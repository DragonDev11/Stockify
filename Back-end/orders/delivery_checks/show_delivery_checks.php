<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deliveries</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>

    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .deliveries-content {
            margin: 20px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #0ce48d;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        button {
            background: #0ce48d;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #09c77d;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
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

            <!-- ======================= Content ================== -->
            <div class="deliveries-content">
                <h1 align="center">Deliveries List</h1>

                <?php
                try {
                    require("../../../includes/db_connection.php");
                    if ($_SERVER["REQUEST_METHOD"] === "POST") {
                        $orderID = $_POST["orderID"];

                        $query = "SELECT * FROM BON_LIVRAISON_HEADER WHERE ID_COMMANDE = :id;";
                        $request = $bd->prepare($query);
                        $request->bindValue(":id", $orderID);

                        try {
                            $request->execute();
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                            exit();
                        }

                        $result = $request->fetchAll(PDO::FETCH_ASSOC);
                        echo "
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Client Name</th>
                        ";
                        if ($result) {
                            if ($result[0]['COMPANY_ICE'] != '') {
                                echo "<th>ICE</th>";
                            }
                        } else {
                            echo "<th>ICE</th>";
                        }
                        echo "
                                            <th>Total Price TTC</th>
                                            <th>Total Price HT</th>
                                            <th>Total TVA</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            ";

                        if ($result) {
                            foreach ($result as $delivery) {
                                echo "
                                        <tr>
                                            <form method='POST'>
                                                <input type='hidden' name='id' value='{$delivery['ID']}'>
                                                <td>{$delivery['ID_BON']}</td>
                                                <td>{$delivery['CLIENT_NAME']}</td>
                                ";
                                if ($delivery["COMPANY_ICE"] != '') {
                                    echo "<td>{$delivery['COMPANY_ICE']}</td>";
                                }
                                echo "
                                                <td>{$delivery['TOTAL_PRICE_TTC']}</td>
                                                <td>{$delivery['TOTAL_PRICE_HT']}</td>
                                                <td>{$delivery['TVA']}</td>
                                                <td>{$delivery['DATE']}</td>
                                                <td><button type='submit' formaction='show_delivery_check.php'>SHOW</button></td>
                                            </form>
                                        </tr>
                                    ";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "
                                            <tr>
                                                <td colspan='9' style='text-align: center;'>No deliveries found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                ";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                toggle.onclick = function() {
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