<?php session_start(); ?>
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

    <title>Invoice</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .invoice-view {
            padding: 20px;
            margin: 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
        }

        .invoice-view h2 {
            color: #0ce48d;
            margin-bottom: 20px;
            text-align: center;
        }

        .invoice-header {
            margin-bottom: 20px;
        }

        .client-info {
            margin: 15px 0;
        }

        .client-info p {
            margin: 5px 0;
            color: var(--black1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #0ce48d;
            color: var(--black1);
        }

        .totals-table th {
            text-align: center;
        }

        .payment-info {
            margin: 20px 0;
        }

        .payment-info p {
            margin: 5px 0;
            color: var(--black1);
        }

        .print-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            background-color: #0ce48d;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .print-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        @media print {

            .navigation,
            .topbar,
            .print-btn {
                display: none;
            }

            .invoice-view {
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../includes/menu.php") ?>

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
            <div class="invoice-view">
                <?php
                require("../../includes/db_connection.php");

                $request = null;
                $query = null;

                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    if (isset($_POST['invoiceID']) && isset($_POST['invoiceType'])) {
                        $type = $_POST['invoiceType'];
                        $ID = $_POST['invoiceID'];

                        if ($type === 'Sell') {
                            $query = "SELECT * FROM SELL_INVOICE_DETAILS WHERE ID_INVOICE = :id;";
                            $request = $bd->prepare($query);
                            $request->bindValue(":id", $ID);

                            try {
                                $request->execute();
                            } catch (PDOException $e) {
                                echo "<p class='error'>" . $e->getMessage() . "</p>";
                            }

                            $details = $request->fetchAll(PDO::FETCH_ASSOC);

                            $query = "SELECT * FROM SELL_INVOICE_HEADER WHERE ID_INVOICE = :id;";
                            $request = $bd->prepare($query);
                            $request->bindValue(":id", $ID);

                            try {
                                $request->execute();
                            } catch (PDOException $e) {
                                echo "<p class='error'>" . $e->getMessage() . "</p>";
                            }

                            $header = $request->fetch(PDO::FETCH_ASSOC);

                            $invoice_number = $header['INVOICE_NUMBER'];
                            $date = $header['DATE'];
                            $client_name = $header['CLIENT_NAME'];
                            $company_ICE = $header['COMPANY_ICE'];

                            $Total_Price_TTC = $header['TOTAL_PRICE_TTC'];
                            $Total_Price_HT = $header['TOTAL_PRICE_HT'];
                            $Total_TVA = $header['TVA'];

                            $sell_type = $header['INVOICE_TYPE'];

                            echo "
                                <h2>Invoice</h2>
                                <div class='invoice-header'>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th><strong>Number</strong></th>
                                                <th><strong>Date</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{$invoice_number}</td>
                                                <td>{$date}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            ";

                            echo "<div class='client-info'>";
                            if (!empty($client_name)) {
                                echo "<p><strong>Client Name:</strong> {$client_name}</p>";
                            }

                            if ($sell_type === 'Company') {
                                echo "<p><strong>Company ICE:</strong> {$company_ICE}</p>";
                            }
                            echo "</div>";

                            echo "
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Quantity</th>
                                            <th>Product Name</th>
                                            <th>Unit Price TTC</th>
                                            <th>Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            ";

                            foreach ($details as $detail) {
                                echo "
                                        <tr>
                                            <td>{$detail['QUANTITY']}</td>
                                            <td>{$detail['PRODUCT_NAME']}</td>
                                            <td>{$detail['UNIT_PRICE_TTC']} MAD</td>
                                            <td>{$detail['TOTAL_PRICE_TTC']} MAD</td>
                                        </tr>
                                ";
                            }

                            echo "
                                    </tbody>
                                </table>
                            ";

                            echo "
                                <div class='totals'>
                                    <table class='totals-table'>
                                        <thead>
                                            <tr>
                                                <th><strong>TOTAL HT</strong></th>
                                                <th><strong>TVA</strong></th>
                                                <th><strong>TOTAL TTC</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{$Total_Price_HT} MAD</td>
                                                <td>{$Total_TVA} MAD</td>
                                                <td>{$Total_Price_TTC} MAD</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            ";

                            echo "<div class='payment-info'>";
                            if ($header["PAYEMENT"] != "") {
                                echo "<p><strong>Payment Method: </strong>{$header['PAYEMENT']}</p>";
                            }
                            if ($header["PAYEMENT_REFERENCE"] != "") {
                                echo "<p><strong>Reference: </strong>{$header['PAYEMENT_REFERENCE']}</p>";
                            }
                            echo "</div>";
                        } else {
                            $query = "SELECT * FROM BUY_INVOICE_DETAILS WHERE ID_INVOICE LIKE (SELECT ID_INVOICE FROM BUY_INVOICE_HEADER WHERE ID_INVOICE = :id);";
                            $request = $bd->prepare($query);
                            $request->bindValue(":id", $ID);

                            try {
                                $request->execute();
                            } catch (PDOException $e) {
                                echo "<p class='error'>" . $e->getMessage() . "</p>";
                            }

                            $details = $request->fetchAll(PDO::FETCH_ASSOC);

                            $query = "SELECT * FROM BUY_INVOICE_HEADER WHERE ID_INVOICE = :id;";
                            $request = $bd->prepare($query);
                            $request->bindValue(":id", $ID);

                            try {
                                $request->execute();
                            } catch (PDOException $e) {
                                echo "<p class='error'>" . $e->getMessage() . "</p>";
                                exit();
                            }

                            $header = $request->fetch(PDO::FETCH_ASSOC);

                            $invoice_number = $header['INVOICE_NUMBER'];
                            $date = $header["DATE"];
                            $supplier_name = $header['SUPPLIER_NAME'];


                            $Total_Price_TTC = $header['TOTAL_PRICE_TTC'];
                            $Total_Price_HT = $header['TOTAL_PRICE_HT'];
                            $Total_TVA = $header['TOTAL_PRICE_TVA'];

                            $query = "SELECT ICE FROM SUPPLIER WHERE SUPPLIERNAME = :supplier_name;";
                            $request = $bd->prepare($query);
                            $request->bindValue(":supplier_name", $supplier_name);

                            try {
                                $request->execute();
                            } catch (PDOException $e) {
                                echo "<p class='error'>" . $e->getMessage() . "</p>";
                                exit();
                            }

                            $ice = $request->fetchColumn(0);

                            echo "
                                <h2>Invoice</h2>
                                <div class='invoice-header'>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th><strong>Number</strong></th>
                                                <th><strong>Date</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{$invoice_number}</td>
                                                <td>{$date}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            ";

                            echo "<div class='client-info'>";
                            echo "<p><strong>Supplier:</strong> {$supplier_name}</p>";
                            echo "<p><strong>ICE:</strong> {$ice}</p>";
                            echo "</div>";

                            echo "
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Quantity</th>
                                            <th>Product Name</th>
                                            <th>Unit Price TTC</th>
                                            <th>Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            ";

                            foreach ($details as $detail) {
                                echo "
                                        <tr>
                                            <td>{$detail['QUANTITY']}</td>
                                            <td>{$detail['PRODUCT_NAME']}</td>
                                            <td>{$detail['UNIT_PRICE_TTC']} MAD</td>
                                            <td>{$detail['TOTAL_PRICE']} MAD</td>
                                        </tr>
                                ";
                            }

                            echo "
                                    </tbody>
                                </table>
                            ";

                            echo "
                                <div class='totals'>
                                    <table class='totals-table'>
                                        <thead>
                                            <tr>
                                                <th><strong>TOTAL HT</strong></th>
                                                <th><strong>TVA</strong></th>
                                                <th><strong>TOTAL TTC</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{$Total_Price_HT} MAD</td>
                                                <td>{$Total_TVA} MAD</td>
                                                <td>{$Total_Price_TTC} MAD</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            ";
                        }
                    }
                }
                ?>

                <button class="print-btn" onclick="window.print()">Print Invoice</button>
            </div>
        </div>
    </div>
</body>

</html>