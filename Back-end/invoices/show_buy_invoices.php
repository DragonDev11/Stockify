<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Invoices List | Stockify</title>

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

        .add-invoice-btn {
            background: #0ce48d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .scan-invoice-btn {
            background-color: rgba(138, 12, 228, 0.3);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-invoice-btn:hover {
            background: #09c77d;
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

        .invoice-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .no-image {
            width: 80px;
            height: 80px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #999;
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

        /* Search bar styles */
        .search {
            position: relative;
        }

        .search-results {
            display: none;
            position: absolute;
            background: white;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .search-results div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .search-results div:hover {
            background: #f5f5f5;
        }

        #error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
        }

        .not-available {
            color: #888;
            font-style: italic;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            align : center;
            gap: 6px;
            margin-top: 6px;
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include "../../includes/menu.php"; ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" id="invoice-search-input" placeholder="Search invoices by number or supplier name...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="invoice-search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name .
                        " " .
                        $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Invoices Content ================== -->
            <div class="invoices-container">
                <h1 class="page-title">Buy Invoices List</h1>

                <p id="error-message"></p>

                <div class="invoices-header">
                    <form method='POST'>
                        <input type='hidden' name='action' value='add_invoices'>
                        <input type='hidden' name='redirection_url' value='create_invoice.php'>
                        <button type='submit' formaction='check_user_privileges.php' class="add-invoice-btn">
                            <ion-icon name="add-outline"></ion-icon>
                            Add Invoice
                        </button>
                    </form>
                    <form method='POST'>
                        <input type='hidden' name='action' value='add_invoices'>
                        <input type='hidden' name='redirection_url' value='http://localhost:3000/'> <!-- ADD THE AI SCANNER PAGE PATH HERE -->
                        <button type='submit' formaction='check_user_privileges.php' class="scan-invoice-btn" disabled>
                            <ion-icon name="sparkles-outline"></ion-icon>
                            Scan Invoice
                        </button>
                        <p class="not-available">
                            <ion-icon name="close-circle-outline"></ion-icon>
                            Not available
                        </p>
                    </form>
                </div>

                <div id="invoices-table-container">
                    <?php try {
                        // Connect to the database
                        require "../../includes/db_connection.php";

                        $requete = null;
                        $query = null;

                        // Fetch invoices from the database
                        echo "<table class='invoices-table' id='invoices-table'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>Invoice Number</th>";
                        echo "<th>Supplier Name</th>";
                        echo "<th>Total Price HT</th>";
                        echo "<th>Total TVA</th>";
                        echo "<th>Total Price TTC</th>";
                        echo "<th>Image</th>";
                        echo "<th colspan='2'>Actions</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";

                        $query = "SELECT * FROM BUY_INVOICE_HEADER;";
                        $requete = $bd->prepare($query);
                        try {
                            $requete->execute();
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                        }
                        $result = $requete->fetchAll(PDO::FETCH_ASSOC);

                        $invoiceType = "Buy";

                        if ($result) {
                            foreach ($result as $invoice) {
                                echo "<tr class='invoice-row'>";
                                echo "<form method='POST'>";
                                echo "<input type='hidden' name='invoiceID' value='{$invoice["ID_INVOICE"]}'>";
                                echo "<input type='hidden' name='invoiceType' value='{$invoiceType}'>";
                                echo "<td class='invoice-number'>{$invoice["INVOICE_NUMBER"]}</td>";
                                echo "<td class='supplier-name'>{$invoice["SUPPLIER_NAME"]}</td>";
                                echo "<td class='price-cell'>{$invoice["TOTAL_PRICE_HT"]} MAD</td>";
                                echo "<td class='price-cell'>{$invoice["TOTAL_PRICE_TVA"]} MAD</td>";
                                echo "<td class='price-cell'>{$invoice["TOTAL_PRICE_TTC"]} MAD</td>";
                                // Product Image
                                $imageData = $invoice["IMAGE"];
                                if ($imageData) {
                                    $base64Image = base64_encode($imageData);
                                    echo "<td><img class='invoice-image' src='data:image/jpeg;base64,{$base64Image}' alt='Invoice Image'></td>";
                                } else {
                                    echo "<td><div class='no-image'><ion-icon name='image-outline'></ion-icon></div></td>";
                                }
                                echo "<td><button type='submit' formaction='show_invoice.php' class='action-btn show-btn'>SHOW</button></td>";
                                echo "</form>";

                                echo "<td>";
                                echo "<form method='POST'>";
                                echo "<input type='hidden' name='invoiceID' value='{$invoice["ID_INVOICE"]}'>";
                                echo "<input type='hidden' name='invoiceType' value='{$invoiceType}'>";
                                echo "<input type='hidden' name='action' value='delete_invoices'>";
                                echo "<button type='submit' formaction='check_user_privileges.php' class='action-btn delete-btn'>DELETE</button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='no-invoices'>No invoices in database</td></tr>";
                        }

                        echo "</tbody>";
                        echo "</table>";
                    } catch (PDOException $e) {
                        echo "<div class='error-message'>Error: " .
                            $e->getMessage() .
                            "</div>";
                    } ?>
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
                    const supplierName = row.querySelector('.supplier-name').textContent.toLowerCase();

                    if (invoiceNumber.includes(searchTerm) || supplierName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            function show_error(message) {
                document.getElementById("error-message").textContent = message;
            }

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
                show_error(message);
            }
        });
    </script>
</body>

</html>
