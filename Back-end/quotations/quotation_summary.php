<?php
session_start();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>

    <title>Quotation Summary</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="quotation_summary.css?v=<?php echo time(); ?>">
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../includes/menu.php"); ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>



                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Main Content ================== -->
            <div class="quotation-summary">
                <?php
                try {
                    require("../../includes/db_connection.php");

                    if ($_SERVER["REQUEST_METHOD"] === "POST") {
                        $client_name = null;
                        $company_ICE = null;

                        $sell_type = $_SESSION['quotation']['sell_type'];
                        $client_name = $_SESSION['quotation']['client_name'];
                        $date = $_SESSION['quotation']['creation_date'];
                        if ($sell_type === 'Company') {
                            $company_ICE = $_SESSION['quotation']['company_ICE'];
                        }

                        $products_count = intval($_POST['products']);

                        if (!isset($_SESSION['quotation']['products'])) {
                            $_SESSION['quotation']['products'] = [];
                        }
                        if (!isset($_SESSION['quotation']['prices'])) {
                            $_SESSION['quotation']['prices'] = [];
                        }
                        if (!isset($_SESSION['quotation']['quantities'])) {
                            $_SESSION['quotation']['quantities'] = [];
                        }

                        $selected_products = [];
                        $prices = [];
                        $quantities = [];

                        for ($i = 1; $i <= $products_count; $i++) {
                            if (isset($_POST["reference$i"])) {
                                $reference = intval($_POST["reference$i"]);
                                $price = floatval($_POST["price$i"]);
                                $quantity = intval($_POST["quantity$i"]);

                                // Add to arrays
                                $selected_products[] = $reference;
                                $prices[$reference] = $price;
                                $quantities[$reference] = $quantity;

                                // Update session
                                $_SESSION['quotation']['products'][$reference] = $reference;
                                $_SESSION['quotation']['prices'][$reference] = $price;
                                $_SESSION['quotation']['quantities'][$reference] = $quantity;
                            }
                        }

                        if (count($_SESSION['quotation']['products']) > 0) {
                            $productsArray = array_values($_SESSION['quotation']['products']);
                            $placeholders = implode(',', $productsArray);

                            // Prepare the query
                            if (isset($_SESSION["quotation"]["state"]) && $_SESSION['quotation']['state'] == 'modifying_order') {
                                $query = "SELECT ID, REFERENCE, PRODUCT_NAME, PRICE, QUANTITY FROM PRODUCT WHERE ID IN ($placeholders)";
                            } else {
                                $query = "SELECT ID, PRODUCT_NAME, PRICE, QUANTITY FROM PRODUCT WHERE ID IN ($placeholders)";
                            }
                            $stmt = $bd->prepare($query);

                            // Execute the query with the product values
                            $stmt->execute();

                            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            echo "
                                <h2>Quotation Details</h2>
                                <p><strong>Date:</strong> {$date}</p>
                            ";

                            if (!empty($client_name)) {
                                echo "<p><strong>Client Name:</strong> {$client_name}</p>";
                            }

                            if ($sell_type === 'Company') {
                                echo "
                                    <p><strong>Company ICE:</strong> {$company_ICE}</p>
                                ";
                            }

                            echo "
                                <table border='2px'>
                                    <thead>
                                        <tr align='center'>
                                            <th class='mint'>Qu.</th>
                                            <th>Product</th>
                                            <th class='mint'>U.P TTC</th>
                                            <th class='mint'>Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            ";

                            $TVA = 0.2;
                            $grandTotal = 0.00;
                            $grandTVA = 0.00;

                            foreach ($products as $product) {
                                $ref = $product['ID'];

                                $unitPriceTTC = floatval($_SESSION['quotation']['prices'][$ref]);
                                $quantity = intval($_SESSION['quotation']['quantities'][$ref]);

                                $priceBeforeTax = $unitPriceTTC / (1 + $TVA);
                                $TVAamount = $unitPriceTTC - $priceBeforeTax;
                                $totalPriceTTC = $unitPriceTTC * $quantity;
                                $totalTVA = $TVAamount * $quantity;

                                $grandTotal += round($totalPriceTTC, 2);
                                $grandTVA += round($totalTVA, 2);

                                echo "
                                    <tr align='center'>
                                        <td>{$quantity}</td>
                                        <td>{$product['PRODUCT_NAME']}</td>
                                        <td>{$unitPriceTTC}</td>
                                        <td>{$totalPriceTTC}</td>
                                    </tr>
                                ";
                            }

                            $grandHT = round($grandTotal - $grandTVA, 2);

                            echo "
                                </tbody>
                                </table>
                                <div class='summary-totals'>
                                    <p><strong>Total HT :</strong> {$grandHT} MAD</p>
                                    <p><strong>Total TVA :</strong> {$grandTVA} MAD</p>
                                    <p><strong>Montant Total :</strong> {$grandTotal} MAD</p>
                                </div>
                                <div class='action-buttons'>
                                    <form method='POST' action='validate_quotation.php'>
                                        <button type='submit' class='btn validate-btn'>Validate quotation</button>
                                    </form>

                                    <form method='GET' action='choose_category.php'>
                                        <button type='submit' class='btn rechoose-btn'>Re-choose products</button>
                                    </form>
                                </div>
                            ";
                        } else {
                            unset($_SESSION["quotation"]);
                            header("Location: create_quotation.php");
                        }
                    } else {
                        echo "<p class='error'>Invalid request method.</p>";
                        echo "<p class='error'>".$_GET["error"]."</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>