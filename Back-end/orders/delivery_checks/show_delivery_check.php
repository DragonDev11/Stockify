<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Check | Stockify</title>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .delivery-container {
            padding: 20px;
            margin: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .delivery-container h2 {
            color: #0ce48d;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        th {
            background-color: #0ce48d;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .mint {
            background-color: #0ce48d;
        }
        
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .summary p {
            font-size: 1.1rem;
            margin: 10px 0;
        }
        
        .total-amount {
            font-size: 1.3rem;
            color: #0ce48d;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../../includes/menu.php"); ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>



                <div class="cardName">
                    
                </div>
            </div>

            <!-- ======================= Delivery Content ================== -->
            <div class="delivery-container">
                <?php
                    require("../../../includes/db_connection.php");

                    if ($_SERVER["REQUEST_METHOD"] === "POST"){
                        $bonID = $_POST["id"];

                        $request = $bd->prepare("SELECT * FROM BON_LIVRAISON_HEADER WHERE ID = :id;");
                        $request->execute([":id" => $bonID]);
                        
                        $header = $request->fetch(PDO::FETCH_ASSOC);

                        $client_name = $header["CLIENT_NAME"];
                        $ice = $header["COMPANY_ICE"];

                        $request = $bd->prepare("SELECT * FROM BON_LIVRAISON_DETAILS WHERE ID_BON = :id;");
                        $request->execute([":id" => $bonID]);
                        
                        $details = $request->fetchAll(PDO::FETCH_ASSOC);

                        echo "<h2>Delivery Check #{$header["ID_BON"]}</h2>";

                        if (!empty($client_name)) {
                            echo "<p><strong>Client Name:</strong> {$client_name}</p>";
                        }

                        if ($header["INVOICE_TYPE"] == 'Company') {
                            echo "<p><strong>Company ICE:</strong> {$ice}</p>";
                        }
                        echo "
                            <table>
                                <thead>
                                    <tr>
                                        <th>Delivery Number</th>
                                        <th>Delivery Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{$header["ID_BON"]}</td>
                                        <td>{$header["DATE"]}</td>
                                    </tr>
                                </tbody>
                            </table>
                        ";



                        echo "
                            <table>
                                <thead>
                                    <tr>
                                        <th class='mint'>Quantity</th>
                                        <th>Product</th>
                                        <th class='mint'>Unit Price (TTC)</th>
                                        <th class='mint'>Total (TTC)</th>
                                    </tr>
                                </thead>
                                <tbody>
                        ";

                        foreach ($details as $detail){
                            echo "
                                <tr>
                                    <td>{$detail["QUANTITY"]}</td>
                                    <td>{$detail["PRODUCT_NAME"]}</td>
                                    <td>{$detail["UNIT_PRICE_TTC"]} MAD</td>
                                    <td>{$detail["TOTAL_PRICE_TTC"]} MAD</td>
                                </tr>
                            ";
                        }

                        echo "
                                </tbody>
                            </table>
                            <div class='summary'>
                                <p><strong>Total HT:</strong> {$header["TOTAL_PRICE_HT"]} MAD</p>
                                <p><strong>Total TVA:</strong> {$header["TVA"]} MAD</p>
                                <p class='total-amount'>Total Amount: {$header["TOTAL_PRICE_TTC"]} MAD</p>
                            </div>
                        ";
                    }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Navigation hover effect
            let list = document.querySelectorAll(".navigation li");
            function activeLink() {
                list.forEach((item) => item.classList.remove("hovered"));
                this.classList.add("hovered");
            }
            list.forEach((item) => item.addEventListener("mouseover", activeLink));

            // Sidebar toggle
            let toggle = document.querySelector(".toggle");
            let navigation = document.querySelector(".navigation");
            let main = document.querySelector(".main");
            if (toggle && navigation && main) {
                toggle.onclick = function () {
                    navigation.classList.toggle("active");
                    main.classList.toggle("active");
                };
            }
        });
    </script>
</body>
</html>