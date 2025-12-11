<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Invoices List | Stockify</title>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .invoices-container {
            padding: 30px;
            margin: 20px;
        }

        .page-title {
            color: #0ce48d;
            text-align: center;
            margin-bottom: 30px;
        }

        .invoices-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .invoices-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .invoices-table th {
            background: #0ce48d;
            color: white;
            padding: 15px;
            text-align: left;
        }

        .invoices-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .invoices-table tr:hover {
            background: #f9f9f9;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .show-btn {
            background: #3498db;
            color: white;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .no-invoices {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1rem;
        }

        .price-cell {
            font-weight: 500;
        }

        .date-cell {
            white-space: nowrap;
        }
        
        #error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
        }
    </style>
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

                <div class="search">
                    <label>
                        <input type="text" id="invoice-search-input" placeholder="Search by invoice number, client name, total, or date...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="invoice-search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Invoices Content ================== -->
            <div class="invoices-container">
                <h1 class="page-title">Sell Invoices List</h1>

                <p id="error-message"></p>
                <div id="invoices-table-container">
                <?php
                try {
                    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["client_id"])) {
                        $id = $_GET["client_id"];
                        // Connect to the database
                        require("../../includes/db_connection.php");

                        $requete = null;
                        $query = null;

                        // Fetch invoices from the database
                        echo "<table class='invoices-table' id='invoices-table'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>Invoice Number</th>";
                        echo "<th>Client Name</th>";
                        echo "<th>ICE</th>";
                        echo "<th>Total TTC</th>";
                        echo "<th>Total HT</th>";
                        echo "<th>TVA</th>";
                        echo "<th>Type</th>";
                        echo "<th>Date</th>";
                        echo "<th colspan='2'>Actions</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";

                        $query = "
                            SELECT *
                            FROM SELL_INVOICE_HEADER
                            WHERE ID_BON_LIVRAISON IN (
                                SELECT ID
                                FROM BON_LIVRAISON_HEADER
                                WHERE ID_COMMANDE IN (
                                    SELECT ID
                                    FROM BON_COMMANDE_HEADER
                                    WHERE CLIENT_ID = :client_id
                                )
                            );
                        ";
                        $requete = $bd->prepare($query);
                        $requete->bindValue(":client_id", $id);
                        try {
                            $requete->execute();
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                            exit();
                        }
                        $result = $requete->fetchAll(PDO::FETCH_ASSOC);

                        $invoiceType = "Sell";

                        if ($result) {
                            foreach ($result as $invoice) {
                                echo "<tr class='invoice-row'>";
                                echo "<td class='invoice-number'>{$invoice['INVOICE_NUMBER']}</td>";
                                echo "<td class='client-name'>{$invoice['CLIENT_NAME']}</td>";
                                echo "<td class='client-ice'>{$invoice['COMPANY_ICE']}</td>";
                                echo "<td class='price-cell total-ttc'>{$invoice['TOTAL_PRICE_TTC']} MAD</td>";
                                echo "<td class='price-cell total-ht'>{$invoice['TOTAL_PRICE_HT']} MAD</td>";
                                echo "<td class='price-cell tva'>{$invoice['TVA']} MAD</td>";
                                echo "<td class='invoice-type'>{$invoice['INVOICE_TYPE']}</td>";
                                echo "<td class='date-cell invoice-date'>{$invoice['DATE']}</td>";
                                
                                // Show button
                                echo "<td>";
                                echo "<form method='POST'>";
                                echo "<input type='hidden' name='invoiceID' value='{$invoice['ID_INVOICE']}'>";
                                echo "<input type='hidden' name='invoiceType' value='{$invoiceType}'>";
                                echo "<button type='submit' formaction='/Stockify/Back-end/invoices/show_invoice.php' class='action-btn show-btn'>SHOW</button>";
                                echo "</form>";
                                echo "</td>";
                                
                                // Delete button
                                echo "<td>";
                                echo "<form method='POST'>";
                                echo "<input type='hidden' name='invoiceID' value='{$invoice['ID_INVOICE']}'>";
                                echo "<input type='hidden' name='invoiceType' value='{$invoiceType}'>";
                                echo "<input type='hidden' name='action' value='delete_invoices'>";
                                echo "<button type='submit' formaction='/Stockify/Back-end/invoices/check_user_privileges.php' class='action-btn delete-btn'>DELETE</button>";
                                echo "</form>";
                                echo "</td>";
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10' class='no-invoices'>No invoices in database</td></tr>";
                        }

                        echo "</tbody>";
                        echo "</table>";
                    } else {
                        //header("Location: show_clients.php?error=Wrong+request+method");
                        exit();
                    }
                } catch (PDOException $e) {
                    echo "<div class='error-message'>Error: " . $e->getMessage() . "</div>";
                }
                ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                toggle.onclick = function() {
                    navigation.classList.toggle("active");
                    main.classList.toggle("active");
                };
            }

            // Invoice search functionality
            document.getElementById('invoice-search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#invoices-table tbody tr.invoice-row');
                
                rows.forEach(row => {
                    const invoiceNumber = row.querySelector('.invoice-number').textContent.toLowerCase();
                    const clientName = row.querySelector('.client-name').textContent.toLowerCase();
                    const totalTtc = row.querySelector('.total-ttc').textContent.toLowerCase();
                    const invoiceDate = row.querySelector('.invoice-date').textContent.toLowerCase();
                    
                    if (invoiceNumber.includes(searchTerm) || 
                        clientName.includes(searchTerm) || 
                        totalTtc.includes(searchTerm) || 
                        invoiceDate.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Handle URL Parameters for Errors
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');

            if (error) {
                let message = '';
                switch (error) {
                    case 'permission_denied':
                        message = 'Permission denied. Contact your administrator.';
                        break;
                    default:
                        message = 'An unknown error occurred.';
                }
                document.getElementById("error-message").textContent = message;
            }
        });
    </script>
</body>
</html>