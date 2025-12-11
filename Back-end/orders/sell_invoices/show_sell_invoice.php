<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Check</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>

    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .delivery-content {
            margin: 20px;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
        .mint {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include "../../../includes/menu.php"; ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>



                <div class="cardName">

                </div>
            </div>

            <!-- ======================= Content ================== -->
            <div class="delivery-content">
                <?php
                require "../../../includes/db_connection.php";

                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    $invoiceID = $_POST["id"];

                    $request = $bd->prepare(
                        "SELECT * FROM SELL_INVOICE_HEADER WHERE ID_INVOICE = :id;",
                    );
                    $request->execute([":id" => $invoiceID]);

                    $header = $request->fetch(PDO::FETCH_ASSOC);

                    $client_name = $header["CLIENT_NAME"];
                    $ice = $header["COMPANY_ICE"];

                    $request = $bd->prepare(
                        "SELECT * FROM SELL_INVOICE_DETAILS WHERE ID_INVOICE = :id;",
                    );
                    $request->execute([":id" => $invoiceID]);

                    $details = $request->fetchAll(PDO::FETCH_ASSOC);

                    echo "<h2>Sell invoice</h2>";

                    if (!empty($client_name)) {
                        echo "<p><strong>Client Name:</strong> {$client_name}</p>";
                    }

                    if ($header["INVOICE_TYPE"] == "Company") {
                        echo "
                                <p><strong>Company ICE:</strong> {$ice}</p>
                            ";
                    }
                    echo "
                            <table>
                                <thead>
                                    <tr>
                                        <th>Number</th>
                                        <th>Delivery Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{$header["INVOICE_NUMBER"]}</td>
                                        <td>{$header["DATE"]}</td>
                                    </tr>
                                </tbody>
                            </table>
                        ";

                    echo "
                            <table>
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

                    try {
                        $tva_query =
                            "SELECT TVA_AMOUNT FROM GENERAL_VARIABLES WHERE ID = 1";
                        $tva_stmt = $bd->query($tva_query);
                        $tva_row = $tva_stmt->fetch(PDO::FETCH_ASSOC);
                        $TVA = $tva_row["TVA_AMOUNT"] / 100.0;
                    } catch (Exception $e) {
                        $TVA = 0.2;
                    }
                    $grandTotal = 0;
                    $grandTVA = 0;

                    foreach ($details as $detail) {
                        echo "
                                <tr align='center'>
                                    <td>{$detail["QUANTITY"]}</td>
                                    <td>{$detail["PRODUCT_NAME"]}</td>
                                    <td>{$detail["UNIT_PRICE_TTC"]}</td>
                                    <td>{$detail["TOTAL_PRICE_TTC"]}</td>
                                </tr>
                            ";
                    }

                    echo "
                                </tbody>
                            </table>
                            <p><strong>Total HT :</strong> {$header["TOTAL_PRICE_HT"]} DH</p>
                            <p><strong>Total TVA :</strong> {$header["TVA"]} DH</p>
                            <p><strong>Montant Total : {$header["TOTAL_PRICE_TTC"]} DH</strong></p>
                        ";
                }
                ?>
                <?php if ($header["PAYEMENT"] != ""): ?>
                    <p><strong>Payement: </strong><?php echo $header[
                        "PAYEMENT"
                    ]; ?></p>
                <?php endif; ?>
                <?php if ($header["PAYEMENT_REFERENCE"] != ""): ?>
                    <p><strong>Reference: </strong><?php echo $header[
                        "PAYEMENT_REFERENCE"
                    ]; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Add hovered class to selected list item
            let list = document.querySelectorAll(".navigation li");

            function activeLink() {
                list.forEach((item) => {
                    item.classList.remove("hovered");
                });
                this.classList.add("hovered");
            }

            list.forEach((item) => item.addEventListener("mouseover", activeLink));

            // Menu Toggle
            let toggle = document.querySelector(".toggle");
            let navigation = document.querySelector(".navigation");
            let main = document.querySelector(".main");

            if (toggle && navigation && main) {
                toggle.onclick = function () {
                    navigation.classList.toggle("active");
                    main.classList.toggle("active");
                };
            } else {
                console.error("One or more elements (.toggle, .navigation, .main) are missing!");
            }
        });
    </script>
</body>
</html>
