<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    require("../../includes/db_connection.php");
    $TVA = isset($GENERAL_VARIABLES["TVA_AMOUNT"]) ? $GENERAL_VARIABLES["TVA_AMOUNT"] / 100.00 : 0.20; // Default to 20% if not set

    $ref = trim($_POST['reference']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']) + (floatval($_POST['price']) * $TVA);
    $quantity = intval($_POST['quantity']);
    $category = trim($_POST['category']);

    try {
        echo "<pre>";
        $bd->beginTransaction();
        echo "POST: ";
        print_r($_POST);
        // Update image if provided
        if (!empty($_FILES['image']['tmp_name']) && exif_imagetype($_FILES['image']['tmp_name'])) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $request = $bd->prepare("UPDATE PRODUCT SET IMAGE = :p_image WHERE REFERENCE = :p_ref;");
            $request->execute([
                ':p_image' => $imageData,
                ':p_ref' => $ref
            ]);
        }

        // Get current category
        $request = $bd->prepare("SELECT CATEGORY_NAME FROM PRODUCT WHERE REFERENCE = :p_ref;");
        $request->execute([':p_ref' => $ref]);
        $result = $request->fetch(PDO::FETCH_ASSOC);
        echo "CATEGORY: ";
        print_r($result);
        $oldCategory = $result['CATEGORY_NAME'];

        // Update product details
        $request = $bd->prepare("
            UPDATE PRODUCT 
            SET PRODUCT_NAME = :p_name, PRICE = :p_price, QUANTITY = :p_quantity, CATEGORY_NAME = :p_category 
            WHERE REFERENCE = :p_ref;
        ");
        $request->execute([
            ':p_name' => $name,
            ':p_price' => $price,
            ':p_quantity' => $quantity,
            ':p_category' => $category,
            ':p_ref' => $ref
        ]);

        if ($category != $oldCategory){
            $request = $bd->prepare("
                UPDATE CATEGORY 
                SET NUMBER_OF_PRODUCTS = NUMBER_OF_PRODUCTS - 1 
                WHERE CATEGORYNAME = :cat;
            ");
            $request->execute([':cat' => $oldCategory]);
            $request = $bd->prepare("
                UPDATE CATEGORY 
                SET NUMBER_OF_PRODUCTS = NUMBER_OF_PRODUCTS + 1 
                WHERE CATEGORYNAME = :cat;
            ");
            $request->execute([':cat' => $category]);
        }

        echo "All should be updated successfully";

        $bd->commit();
        header("Location: show_products.php");
        exit();
    } catch (PDOException $e) {
        $bd->rollBack();
        error_log($e->getMessage());
        echo "An error occurred. Please try again.";
    }
}
?>
