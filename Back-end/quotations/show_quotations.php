<?php
session_start();
if (isset($_SESSION["user"]["username"])) {
    if (isset($_SESSION["quotation"])) {
        unset($_SESSION["quotation"]);
    }
}
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

    <script src="../main.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/style_choose_products.css">

    <title>Quotations Management</title>
    <style>
        /* =============== Base Styles ============== */
        * {
            font-family: "Parkinsans", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue: #0ce48d;
            --white: #fff;
            --gray: #f5f5f5;
            --black1: #222;
            --black2: #999;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: #f5f5f5;
        }

        /* =============== Quotations Container ============== */
        .quotations-container {
            margin: 20px;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .quotations-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .quotations-header h1 {
            color: #0ce48d;
            margin: 0;
            font-size: 1.8rem;
        }

        /* =============== Action Buttons ============== */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: #0ce48d;
            color: white;
        }

        .btn-primary:hover {
            background: #0abf7a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(12, 228, 141, 0.2);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #555;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* =============== Filter Form ============== */
        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .filter-form label {
            font-weight: 500;
            color: #444;
        }

        .filter-form select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .filter-form button {
            background: #0ce48d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-form button:hover {
            background: #0abf7a;
        }

        /* =============== Quotations Table ============== */
        .quotations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .quotations-table th,
        .quotations-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .quotations-table th {
            background-color: #0ce48d;
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
        }

        .quotations-table tr:hover {
            background-color: #f5f5f5;
        }

        .quotations-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .quotations-table tr:hover:nth-child(even) {
            background-color: #f0f0f0;
        }

        /* =============== Action Buttons in Table ============== */
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .show-btn {
            background: #3498db;
            color: white;
        }

        .show-btn:hover {
            background: #2980b9;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        .delete-btn:hover {
            background: #c0392b;
        }

        .confirm-btn {
            background: #2ecc71;
            color: white;
        }

        .confirm-btn:hover {
            background: #27ae60;
        }

        .action-btn ion-icon {
            font-size: 1rem;
        }

        /* =============== Error Message ============== */
        .error-message {
            color: #e74c3c;
            padding: 12px;
            background: #fde8e8;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            border-left: 4px solid #e74c3c;
        }
        
        /* =============== Search Styles ============== */
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

        /* =============== Responsive Design ============== */
        @media (max-width: 1200px) {
            .quotations-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        @media (max-width: 768px) {
            .quotations-container {
                margin: 10px;
                padding: 15px;
            }

            .filter-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .quotations-table th,
            .quotations-table td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }

            .action-btn {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .quotations-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
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
                        <input type="text" id="search-input" placeholder="Search by quotation number, client name or ICE...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Content ================== -->
            <div class="quotations-container">
                <div class="quotations-header">
                    <h1>Quotations Management</h1>
                    <div class="action-buttons">
                        <form method="POST" action="check_user_privileges.php" style="display: inline;">
                            <input type="hidden" name="action" value="add_quotations">
                            <button type="submit" class="btn btn-primary">
                                <ion-icon name="add-outline"></ion-icon>
                                Add Quotation
                            </button>
                        </form>
                    </div>
                </div>

                <form method='GET' action='show_quotations.php' class="filter-form">
                    <label>Order By:</label>
                    <select name='order_column'>
                        <option>Number</option>
                        <option>Date</option>
                        <option>Total price</option>
                        <option>State</option>
                    </select>
                    <select name='order_type'>
                        <option>Ascending</option>
                        <option>Descending</option>
                    </select>
                    <button type='submit'>
                        <ion-icon name="filter-outline"></ion-icon>
                        Apply
                    </button>
                </form>

                <p id="error_tag" class="error-message" style="display: none;"></p>

                <?php
                if (isset($_SESSION["user"]["username"])) {
                    if (isset($_SESSION["quotation"])) {
                        unset($_SESSION["quotation"]);
                    }

                    $username = $_SESSION["user"]["username"];
                    try {
                        // Connect to the database
                        require("../../includes/db_connection.php");

                        $requete = null;
                        $query = null;

                        $query = "SELECT USER_ROLE_ID FROM USERS WHERE USERNAME = :username;";
                        $requete = $bd->prepare($query);
                        $requete->bindValue(":username", $username);

                        try {
                            $requete->execute();
                        } catch (PDOException $e) {
                            echo "<p class='error-message'>" . $e->getMessage() . "</p>";
                            exit();
                        }

                        $userID = $requete->fetchColumn(0);

                        // Fetch products from the database
                        if ($_SERVER["REQUEST_METHOD"] === "GET") {
                            echo "
                                <table class='quotations-table' id='quotations-table'>
                                    <thead>
                                        <tr>
                                            <th>Quotation</th>
                                            <th>Client Name</th>
                                            <th>ICE</th>
                                            <th>Total Price TTC</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                ";

                            if (isset($_GET['order_column']) && isset($_GET['order_type'])) {
                                $columnsMap = [
                                    "Number" => "DEVIS_NUMBER",
                                    "Total price" => "TOTAL_PRICE_TTC"
                                ];

                                $typesMap = [
                                    "Ascending" => "ASC",
                                    "Descending" => "DESC"
                                ];

                                $orderColumn = $columnsMap[$_GET['order_column']];
                                $orderType = $typesMap[$_GET['order_type']];

                                $query = "SELECT * FROM DEVIS_HEADER ORDER BY $orderColumn $orderType";
                                $requete = $bd->prepare($query);
                            } else {
                                $query = "SELECT * FROM DEVIS_HEADER;";
                                $requete = $bd->prepare($query);
                            }

                            try {
                                $requete->execute();
                            } catch (PDOException $e) {
                                echo "<p class='error-message'>" . $e->getMessage() . "</p>";
                                exit();
                            }

                            $result = $requete->fetchAll(PDO::FETCH_ASSOC);

                            if ($result) {
                                foreach ($result as $quotation) {
                                    $query = "SELECT * FROM DEVIS_DETAILS WHERE ID_DEVIS = :id";
                                    $request = $bd->prepare($query);
                                    $request->bindValue(":id", $quotation["ID"]);

                                    try {
                                        $request->execute();
                                    } catch (PDOException $e) {
                                        error_log($e->getMessage());
                                        exit();
                                    }

                                    echo "
                                            <tr class='quotation-row'>
                                                <td class='quotation-number'>{$quotation['DEVIS_NUMBER']}</td>
                                                <td class='client-name'>{$quotation['CLIENT_NAME']}</td>
                                                <td class='client-ice'>{$quotation['COMPANY_ICE']}</td>
                                                <td>{$quotation['TOTAL_PRICE_TTC']}</td>
                                                <td class='actions'>
                                                    <form method='POST' style='display: inline;'>
                                                        <input type='hidden' name='quotationID' value='{$quotation['ID']}'>
                                                        <button type='submit' formaction='show_quotation.php' class='action-btn show-btn'>
                                                            <ion-icon name='eye-outline'></ion-icon>
                                                        </button>
                                                    </form>
                                                    <form method='POST' style='display: inline;'>
                                                        <input type='hidden' name='quotationID' value='{$quotation['ID']}'>
                                                        <input type='hidden' name='action' value='delete_quotations'>
                                                        <button type='submit' formaction='check_user_privileges.php' class='action-btn delete-btn'>
                                                            <ion-icon name='trash-outline'></ion-icon>
                                                        </button>
                                                    </form>
                                                    <form method='POST' style='display: inline;'>
                                                        <input type='hidden' name='quotationID' value='{$quotation['ID']}'>
                                                        <input type='hidden' name='action' value='confirm_quotations'>
                                                        <button type='submit' formaction='check_user_privileges.php' class='action-btn confirm-btn'>
                                                            <ion-icon name='checkmark-outline'></ion-icon>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        ";
                                }
                            } else {
                                echo "
                                        <tr>
                                            <td colspan='5'>No quotations found</td>
                                        </tr>
                                    ";
                            }

                            echo "</tbody></table>";
                        }
                    } catch (PDOException $e) {
                        echo "<p class='error-message'>Error: " . $e->getMessage() . "</p>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Search functionality for quotations
            document.getElementById('search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#quotations-table tbody tr.quotation-row');
                
                rows.forEach(row => {
                    const quotationNumber = row.querySelector('.quotation-number').textContent.toLowerCase();
                    const clientName = row.querySelector('.client-name').textContent.toLowerCase();
                    const clientIce = row.querySelector('.client-ice').textContent.toLowerCase();
                    
                    if (quotationNumber.includes(searchTerm) || clientName.includes(searchTerm) || clientIce.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Error message handling
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');

            if (error) {
                const errorElement = document.getElementById("error_tag");
                let message = error.replaceAll("_", " ");
                errorElement.textContent = message;
                errorElement.style.display = "block";

                // Hide the error after 5 seconds
                setTimeout(() => {
                    errorElement.style.display = "none";
                }, 5000);
            }
        });
    </script>
</body>

</html>