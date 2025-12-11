<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require "../../includes/db_connection.php";

    if (isset($_SESSION["invoice"])) {
        $type = $_SESSION["invoice"]["type"];
        echo $type;
        $bd->beginTransaction();

        process_buy_invoice($bd);

        try {
            $bd->commit();
            unset($_SESSION["invoice"]);
            header("Location: show_buy_invoices.php?type={$type}");
        } catch (PDOException $e) {
            $bd->rollback();
            echo $e->getMessage();
            exit();
        }
    }
}

function process_buy_invoice($bd)
{
    echo "<pre>";
    echo "Session: ";
    //print_r($_SESSION);
    echo "Post: ";
    //  print_r($_POST);

    $id = $_SESSION["invoice"]["id"];
    $supplier_name = $_SESSION["invoice"]["supplier_name"];
    $date = $_SESSION["invoice"]["date"];
    $ice = $_SESSION["invoice"]["ice"];
    $invoice_image = $_SESSION["invoice"]["invoice_image"];
    $products_references = [];
    $products_names = [];
    $products_quantities = [];
    $products_prices = [];
    $products_categories = [];
    $products_images = [];
    $products_totals = [];

    $num_products = $_POST["number_of_products"];

    $TVA = isset($GENERAL_VARIABLES["TVA_AMOUNT"])
        ? $GENERAL_VARIABLES["TVA_AMOUNT"] / 100.0
        : 0.2; // Default to 20% if not set

    for ($i = 0; $i < $num_products; $i++) {
        $products_references[$i] = "P" . bin2hex(random_bytes(7.5));
        $products_names[$i] = $_POST["name_" . $i];
        $products_quantities[$i] = $_POST["quantity_" . $i];
        $products_categories[$i] = $_POST["category_" . $i];
        $products_prices[$i] =
            $_POST["price_ttc_" . $i] + $_POST["price_ttc_" . $i] * $TVA;
        if (
            isset($_FILES["image_" . $i]) &&
            $_FILES["image_" . $i]["error"] === UPLOAD_ERR_OK
        ) {
            $image = $_FILES["image_" . $i]["tmp_name"];
            $imageData = file_get_contents($image);
            $products_images[$i] = $imageData;
        } else {
            $products_images[$i] = null;
        }
        $products_totals[$i] = $_POST["total_ttc_" . $i];
    }

    print_r($products_names);
    print_r($products_references);
    print_r($products_quantities);
    print_r($products_prices);
    //echo "</pre>";

    $totalHT = $_POST["total_ht"];
    $totalTVA = $_POST["total_tva"];
    $totalTTC = $_POST["total_ttc"];

    $query = "INSERT INTO BUY_INVOICE_HEADER(INVOICE_NUMBER,SUPPLIER_NAME,TOTAL_PRICE_TTC,TOTAL_PRICE_HT,TOTAL_PRICE_TVA,DATE,IMAGE) VALUES(
            :id,
            :supplier_n,
            :total_ttc,
            :total_ht,
            :total_tva,
            :date,
            :img
        );";

    $request = $bd->prepare($query);
    $request->bindValue(":id", "FAC" . $id);
    $request->bindValue(":supplier_n", $supplier_name);
    $request->bindValue(":total_ttc", $totalTTC);
    $request->bindValue(":total_ht", $totalHT);
    $request->bindValue(":total_tva", $totalTVA);
    $request->bindValue(":date", $date);
    $request->bindParam(":img", $invoice_image, PDO::PARAM_LOB);

    try {
        $request->execute();
    } catch (PDOException $e) {
        $bd->rollback();
        echo $e->getMessage();
        exit();
    }

    $lastID = $bd->lastInsertId();

    echo "<p>added invoice header</p>";

    for ($i = 0; $i < $num_products; $i++) {
        $query =
            "SELECT REFERENCE,PRODUCT_NAME FROM PRODUCT WHERE PRODUCT_NAME = :p_name";
        $requete = $bd->prepare($query);
        $requete->bindValue(":p_name", $products_names[$i]);
        try {
            $requete->execute();
        } catch (PDOException $e) {
            $bd->rollback();
            echo $e->getMessage();
            exit();
        }
        $result = $requete->fetchAll(PDO::FETCH_DEFAULT);

        if (count($result) > 0) {
            $query = "UPDATE PRODUCT SET QUANTITY=QUANTITY+:p_quantity";
            $request = $bd->prepare($query);
            $request->bindValue(":p_quantity", $products_quantities[$i]);
            echo "<p>added quantity to the stock</p>";
        } else {
            $query = "INSERT INTO PRODUCT(REFERENCE,PRODUCT_NAME,PRICE,QUANTITY,CATEGORY_NAME,IMAGE) VALUES(
                    :ref,
                    :p_name,
                    :unit_price_ttc,
                    :quantity,
                    :cat,
                    :img
                );";

            $request = $bd->prepare($query);
            $request->bindValue(":ref", $products_references[$i]);
            $request->bindValue(":p_name", $products_names[$i]);
            $request->bindValue(":unit_price_ttc", $products_prices[$i]);
            $request->bindValue(":quantity", $products_quantities[$i]);
            $request->bindValue(":cat", $products_categories[$i]);
            if ($products_images[$i] != null) {
                $request->bindValue(
                    ":img",
                    $products_images[$i],
                    PDO::PARAM_LOB,
                );
            } else {
                $request->bindValue(":img", null);
            }

            try {
                echo "<p>added product</p>";
                $request->execute();
            } catch (PDOException $e) {
                $bd->rollback();
                echo $e->getMessage();
                exit();
            }

            $query =
                "UPDATE CATEGORY SET NUMBER_OF_PRODUCTS = NUMBER_OF_PRODUCTS + 1 WHERE CATEGORYNAME = :p_category";
            $request = $bd->prepare($query);
            $request->bindValue(":p_category", $products_categories[$i]);
            echo "<p>updated category</p>";
        }

        try {
            $request->execute();
        } catch (PDOException $e) {
            $bd->rollback();
            echo $e->getMessage();
            exit();
        }
    }

    for ($i = 0; $i < $num_products; $i++) {
        echo "<p>adding buy invoice detail {$i}</p>";
        $query = "INSERT INTO BUY_INVOICE_DETAILS(ID_INVOICE, PRODUCT_ID, PRODUCT_NAME, QUANTITY, UNIT_PRICE_TTC, TOTAL_PRICE)
                    VALUES(:id, :p_id, :p_name, :quantity, :unit_price_ttc, :total_ttc);";

        $request = $bd->prepare($query);

        $request->bindValue(":id", $lastID);
        $query = "SELECT REFERENCE FROM PRODUCT WHERE PRODUCT_NAME = :p_name";
        $requete = $bd->prepare($query);
        $requete->bindValue(":p_name", $products_names[$i]);
        try {
            $requete->execute();
        } catch (PDOException $e) {
            $bd->rollback();
            echo $e->getMessage();
            exit();
        }
        $result = $requete->fetch(PDO::FETCH_COLUMN, 0);
        if ($result) {
            $request->bindValue(":p_id", $result, PDO::PARAM_STR);
        } else {
            $request->bindValue(
                ":p_id",
                $products_references[$i],
                PDO::PARAM_STR,
            );
        }

        $request->bindValue(":p_name", $products_names[$i], PDO::PARAM_STR);
        $request->bindValue(
            ":quantity",
            $products_quantities[$i],
            PDO::PARAM_INT,
        );
        $request->bindValue(
            ":unit_price_ttc",
            $_POST["price_ttc_" . $i],
            PDO::PARAM_STR,
        );
        $request->bindValue(":total_ttc", $products_totals[$i], PDO::PARAM_STR);

        try {
            echo "<p>executing</p>";
            $request->execute();
            echo "<p>Inserted invoice details" . $i . "</p>";
        } catch (PDOException $e) {
            $bd->rollback();
            echo "Error: " . $e->getMessage();
            exit();
        }
    }
    echo "done";
}
?>
