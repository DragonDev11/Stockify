<?php
// Désactiver l'affichage des erreurs en production
error_reporting(0);
ini_set("display_errors", 0);

// Démarrer la session
session_start();

// Connexion à la base de données
try {
    require "../../includes/db_connection.php";
} catch (PDOException $e) {
    die("Database connection failed.");
}

// Vérifier l'existence de la session de commande
if (!isset($_SESSION["order"])) {
    header("Location: create_order.php");
    exit();
}

// Vérifier les clés requises dans la session
$required_keys = ["sell_type", "client_name", "creation_date"];
foreach ($required_keys as $key) {
    if (!isset($_SESSION["order"][$key])) {
        unset($_SESSION["order"]);
        header("Location: create_order.php");
        exit();
    }
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Initialiser les tableaux si inexistants
        $_SESSION["order"]["products"] = $_SESSION["order"]["products"] ?? [];
        $_SESSION["order"]["prices"] = $_SESSION["order"]["prices"] ?? [];
        $_SESSION["order"]["quantities"] =
            $_SESSION["order"]["quantities"] ?? [];

        if (!isset($_POST["products"]) || !is_numeric($_POST["products"])) {
            throw new Exception("Invalid product count.");
        }

        $products_count = intval($_POST["products"]);
        $hasProducts = false;

        for ($i = 1; $i <= $products_count; $i++) {
            if (isset($_POST["reference$i"])) {
                $reference = intval($_POST["reference$i"]);
                $price = floatval($_POST["price$i"]);
                $quantity = intval($_POST["quantity$i"]);

                if ($quantity < 1) {
                    continue;
                }

                $_SESSION["order"]["products"][$reference] = $reference;
                $_SESSION["order"]["prices"][$reference] = $price;
                $_SESSION["order"]["quantities"][$reference] = $quantity;
                $hasProducts = true;
            }
        }

        if (!$hasProducts) {
            unset($_SESSION["order"]);
            header("Location: create_order.php");
            exit();
        }

        $productsArray = array_values($_SESSION["order"]["products"]);

        if (empty($productsArray)) {
            throw new Exception("No valid products selected.");
        }

        $placeholders = implode(",", $productsArray);
        $query = "SELECT ID, PRODUCT_NAME, PRICE FROM PRODUCT WHERE ID IN ($placeholders)";
        $stmt = $bd->prepare($query);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) {
            throw new Exception("No products found.");
        }
    } catch (Exception $e) {
        die("Une erreur est survenue.");
    }
} else {
    header("Location: create_order.php");
    exit();
}

// Récupérer le taux TVA
try {
    $tva_query = "SELECT TVA_AMOUNT FROM GENERAL_VARIABLES WHERE ID = 1";
    $tva_stmt = $bd->query($tva_query);
    $tva_row = $tva_stmt->fetch(PDO::FETCH_ASSOC);
    $TVA = $tva_row["TVA_AMOUNT"] / 100.0;
} catch (Exception $e) {
    $TVA = 0.2;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Summary</title>
    <link rel="stylesheet" href="../css/style.css">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <style>
        .order-summary-container {
            margin: 20px auto;
            padding: 20px;
            max-width: 800px;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .order-table th {
            background: #0ce48d;
            color: white;
            padding: 12px;
            text-align: center;
        }

        .order-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        .total-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .validate-btn {
            background: #0ce48d;
            color: white;
        }

        .add-more-btn {
            background: #333;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include "../../includes/menu.php"; ?>
        <div class="main">

            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>



                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name .
                        " " .
                        $last_name; ?></p>
                </div>
            </div>



            <div class="order-summary-container">
                <h1>Order Summary</h1>

                <div class="order-details">
                    <p><strong>Date:</strong> <?= htmlspecialchars(
                        $_SESSION["order"]["creation_date"],
                    ) ?></p>
                    <p><strong>Client Name:</strong> <?= htmlspecialchars(
                        $_SESSION["order"]["client_name"],
                    ) ?></p>
                    <?php if (
                        $_SESSION["order"]["sell_type"] === "Company" &&
                        !empty($_SESSION["order"]["company_ICE"])
                    ): ?>
                        <p><strong>Company ICE:</strong> <?= htmlspecialchars(
                            $_SESSION["order"]["company_ICE"],
                        ) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION["order"]["client_address"])): ?>
                        <p><strong>Address:</strong> <?= htmlspecialchars(
                            $_SESSION["order"]["client_address"],
                        ) ?></p>
                    <?php endif; ?>
                </div>

                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Quantity</th>
                            <th>Product</th>
                            <th>Unit Price TTC</th>
                            <th>Total TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grandTotal = 0.0;
                        $grandTVA = 0.0;

                        foreach ($products as $product) {

                            $ref = $product["ID"];
                            $unitPriceTTC = floatval(
                                $_SESSION["order"]["prices"][$ref],
                            );
                            $quantity = intval(
                                $_SESSION["order"]["quantities"][$ref],
                            );
                            $priceBeforeTax = $unitPriceTTC / (1 + $TVA);
                            $TVAamount = $unitPriceTTC - $priceBeforeTax;
                            $totalPriceTTC = $unitPriceTTC * $quantity;
                            $totalTVA = $TVAamount * $quantity;
                            $grandTotal += $totalPriceTTC;
                            $grandTVA += $totalTVA;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($quantity) ?></td>
                                <td><?= htmlspecialchars(
                                    $product["PRODUCT_NAME"],
                                ) ?></td>
                                <td><?= htmlspecialchars(
                                    number_format($unitPriceTTC, 2),
                                ) ?> MAD</td>
                                <td><?= htmlspecialchars(
                                    number_format($totalPriceTTC, 2),
                                ) ?> MAD</td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>

                <div class="total-section">
                    <p><strong>Total HT:</strong> <?= htmlspecialchars(
                        number_format($grandTotal - $grandTVA, 2),
                    ) ?> MAD</p>
                    <p><strong>Total TVA:</strong> <?= htmlspecialchars(
                        number_format($grandTVA, 2),
                    ) ?> MAD</p>
                    <p><strong>Total Amount:</strong> <?= htmlspecialchars(
                        number_format($grandTotal, 2),
                    ) ?> MAD</p>
                </div>

                <div class="action-buttons">
                    <form method='POST' action='validate_order.php'>
                        <button type='submit' name="validate_order" class="btn validate-btn">Validate Order</button>
                    </form>
                    <form method='GET' action='choose_category.php'>
                        <button type='submit' class="btn add-more-btn">Add More Products</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
