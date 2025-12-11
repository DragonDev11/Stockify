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

    <title>Quotation</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .quotation-container {
            margin: 20px;
            padding: 20px;
        }

        .quotation-container table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .quotation-container th,
        .quotation-container td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .quotation-container th {
            background-color: #0ce48d;
            color: white;
        }

        .quotation-container tr:hover {
            background-color: #f5f5f5;
        }

        .print-btn {
            background: #0ce48d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        .print-btn:hover {
            background: #0abf7a;
        }

        .client-info {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
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

            <div class="quotation-container">
                <?php
                require("../../includes/db_connection.php");
                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    if (isset($_POST["quotationID"])) {
                        $id = $_POST["quotationID"];

                        $query = "SELECT * FROM DEVIS_DETAILS WHERE ID_DEVIS = :id";
                        $request = $bd->prepare($query);

                        $request->bindValue(":id", $id, PDO::PARAM_INT);

                        try {
                            $request->execute();
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                        }

                        $details = $request->fetchAll(PDO::FETCH_ASSOC);

                        $query = "SELECT * FROM DEVIS_HEADER WHERE ID = :id";
                        $request = $bd->prepare($query);

                        $request->bindValue(":id", $id, PDO::PARAM_INT);

                        try {
                            $request->execute();
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                        }

                        $header = $request->fetch(PDO::FETCH_ASSOC);

                        $quotation_number = $header["DEVIS_NUMBER"];
                        $client = $header["CLIENT_NAME"];
                        $ice = $header["COMPANY_ICE"];
                        $Total_Price_TTC = $header["TOTAL_PRICE_TTC"];

                        echo "
                                <h2>Quotation</h2>
                                <table>
                                    <thead>
                                        <tr>
                                            <th><strong>Number</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{$quotation_number}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            ";

                        echo '<div class="client-info">';
                        if (!empty($client)) {
                            echo "<p><strong>Client Name: </strong>{$client}</p>";
                        } else {
                            echo "<p><strong>Client Name: </strong>Unknown</p>";
                        }

                        if (!empty($ice)) {
                            echo "
                                    <p><strong>Company ICE:</strong> {$ice}</p>
                                ";
                        }
                        echo '</div>';

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

                        if ($details) {
                            foreach ($details as $detail) {
                                $query = "SELECT PRODUCT_NAME FROM PRODUCT WHERE REFERENCE = :ref";
                                $request = $bd->prepare($query);

                                $request->bindValue(":ref", $detail["PRODUCT_ID"], PDO::PARAM_STR);

                                try {
                                    $request->execute();
                                } catch (PDOException $e) {
                                    echo $e->getMessage();
                                }

                                $name = $request->fetchColumn(0);
                                echo "
                                            <tr>
                                                <td>{$detail['QUANTITY']}</td>
                                                <td>{$name}</td>
                                                <td>{$detail['UNIT_PRICE_TTC']}</td>
                                                <td>{$detail['TOTAL_PRICE_TTC']}</td>
                                            </tr>
                                    ";
                            }
                        } else {
                            echo "
                                        <tr><td colspan='4'>No products have been added</td></tr>
                                ";
                        }

                        echo "
                                    </tbody>
                                </table>
                            ";

                        echo "
                                <table>
                                    <thead>
                                        <tr>
                                            <th><strong>TOTAL TTC</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{$Total_Price_TTC}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            ";
                    }
                }
                ?>
                <button class="print-btn" onclick="window.print()">Print</button>
            </div>
        </div>
    </div>
</body>

</html>