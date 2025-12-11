<?php
session_start();
require("../../includes/db_connection.php");
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

    <title>Modify Order</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style_choose_products.css">
    <style>
        .modify-order-container {
            margin: 20px;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .order-form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .order-form h1 {
            color: #0ce48d;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .order-form label {
            display: block;
            margin: 15px 0 8px;
            font-weight: 500;
            color: #333;
        }
        
        .order-form input[type="text"],
        .order-form input[type="date"],
        .order-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .order-form select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 1em;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .products-table th {
            background: #0ce48d;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .products-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .products-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-buttons button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .save-btn {
            background: #0ce48d;
            color: white;
        }
        
        .save-btn:hover {
            background: #0abf7a;
        }
        
        .add-products-btn {
            background: #333;
            color: white;
        }
        
        .add-products-btn:hover {
            background: #555;
        }
        
        .remove-btn {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .remove-btn:hover {
            background: #c0392b;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../includes/menu.php"); ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <?php include("../../includes/topbar.php"); ?>

            <!-- ======================= Modify Order Content ================== -->
            <div class="modify-order-container">
                <?php
                try {
                    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["orderID"])) {
                        $id = $_GET["orderID"];

                        if (!isset($_SESSION['order'])) {
                            $_SESSION['order'] = [];
                        }
                        $_SESSION['order']['id'] = $id;

                        // Get order header
                        $query = "SELECT * FROM BON_COMMANDE_HEADER WHERE ID = :id";
                        $request = $bd->prepare($query);
                        $request->bindValue(":id", $id);
                        $request->execute();
                        $result = $request->fetch(PDO::FETCH_ASSOC);

                        if (!$result) {
                            throw new Exception("Order not found");
                        }

                        // Get order details
                        $query = "SELECT * FROM BON_COMMANDE_DETAILS WHERE ID_COMMANDE = :id";
                        $request = $bd->prepare($query);
                        $request->bindValue(":id", $id);
                        $request->execute();
                        $products = $request->fetchAll(PDO::FETCH_ASSOC);

                        if (empty($products)) {
                            throw new Exception("No products found in this order");
                        }
                ?>
                        <div class="order-form">
                            <h1>Modify Order</h1>
                            <form method='POST' action='create_session.php'>
                                <input type='hidden' name='state' value='modifying_order'>
                                <input type='hidden' name='redirect' id='redirect' value=''>
                                <input type='hidden' name='orderID' value='<?php echo htmlspecialchars($id); ?>'>
                                
                                <label for='client_name'>Client Name</label>
                                <input type='text' name='client_name' value='<?php echo htmlspecialchars($result["CLIENT_NAME"]); ?>' required>
                                
                                <label>Type</label>
                                <select name='sell_type' onchange='toggleFields()'>
                                    <option value='Personal' <?php echo ($result['TYPE'] === 'Personal') ? 'selected' : ''; ?>>Personal</option>
                                    <option value='Company' <?php echo ($result['TYPE'] === 'Company') ? 'selected' : ''; ?>>Company</option>
                                </select>
                                
                                <label for='company_ICE' id="companyICELabel">Company ICE</label>
                                <input type='text' name='company_ICE' id="companyICEField" value='<?php echo htmlspecialchars($result['COMPANY_ICE']); ?>' <?php echo ($result['TYPE'] === 'Company') ? 'required' : ''; ?> <?php echo ($result['TYPE'] !== 'Company') ? 'class="hidden"' : ''; ?>>
                                
                                <label for='address'>Address</label>
                                <input type='text' name='address' value='<?php echo htmlspecialchars($result['ADDRESSE']); ?>'>
                                
                                <label for='creation_date'>Date</label>
                                <input type='date' id="creation_date" name='creation_date' value='<?php echo htmlspecialchars($result['DATE']); ?>' required>   
                                
                                <div class="action-buttons">
                                    <button type='submit' class="save-btn" onclick='redirect_input("apply_modifications.php")'>Save Changes</button>
                                </div>
                            </form>
                        </div>
                <?php
                    } else {
                        throw new Exception("Invalid request");
                    }
                } catch (Exception $e) {
                    echo '<div class="error-message">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            toggleFields();
            setCurrentDate();
        });

        function setCurrentDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            
            const dateInput = document.getElementById("creation_date");
            if (dateInput && !dateInput.value) {
                dateInput.value = `${year}-${month}-${day}`;
            }
        }

        function toggleFields() {
            const sellType = document.querySelector("select[name='sell_type']");
            const companyICELabel = document.getElementById("companyICELabel");
            const companyICEField = document.getElementById("companyICEField");
            const clientField = document.querySelector("input[name='client_name']");

            if (sellType.value == 'Company') {
                companyICELabel.textContent = "Company ICE";
                companyICEField.classList.remove("hidden");
                companyICEField.required = true;
                clientField.required = false;
            } else {
                companyICELabel.textContent = "";
                companyICEField.classList.add("hidden");
                companyICEField.required = false;
                clientField.required = true;
            }
        }

        function redirect_input(destination) {
            document.getElementById('redirect').value = destination;
        }
    </script>
</body>
</html>