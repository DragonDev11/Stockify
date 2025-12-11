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

    <script src="../../main.js"></script>

    <title>Orders List</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .orders-container {
            margin: 20px;
            padding: 20px;
        }

        .filter-form {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .filter-form label {
            margin-right: 10px;
        }

        .filter-form select,
        .filter-form button {
            padding: 8px 12px;
            margin-right: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .filter-form button {
            background: #0ce48d;
            color: white;
            border: none;
            cursor: pointer;
        }

        .add-order-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #0ce48d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
            max-width: 200px;
        }

        #error_tag {
            color: red;
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            background: #ffeeee;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #0ce48d;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        select {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        button {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            margin: 2px;
        }

        button[type="submit"] {
            background: #0ce48d;
            color: white;
        }

        button[disabled] {
            background: #cccccc;
            cursor: not-allowed;
        }

        button:not([type="submit"]) {
            background: #333;
            color: white;
        }

        .state-form {
            display: flex;
            align-items: center;
            gap: 5px;
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
                        <input type="text" id="order-search-input" placeholder="Search orders by number, client or ICE...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Orders Content ================== -->
            <div class="orders-container">
                <h1>Orders List</h1>

                <form method="POST" action="check_user_privileges.php">
                    <input type="hidden" name="action" value="add_orders">
                    <button type="submit" class="add-order-btn">Add New Order</button>
                </form>

                <form method='GET' action='show_orders.php' class="filter-form">
                    <label>Order By</label>
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
                    <button type='submit'>Apply</button>
                </form>

                <p id="error_tag"></p>

                <?php if (isset($_SESSION["user"]["username"])): ?>
                    <?php
                    $username = $_SESSION["user"]["username"];
                    try {
                        $query = "SELECT USER_ROLE_ID FROM USERS WHERE USERNAME = :username;";
                        $requete = $bd->prepare($query);
                        $requete->bindValue(":username", $username);
                        $requete->execute();
                        $userID = $requete->fetchColumn(0);

                        if ($_SERVER["REQUEST_METHOD"] === "GET"):
                    ?>
                            <div id="orders-table-container">
                                <table id="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Order Number</th>
                                            <th>Client Name</th>
                                            <th>ICE</th>
                                            <th>Address</th>
                                            <th>Total Price TTC</th>
                                            <th>Date</th>
                                            <th>State</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (isset($_GET['order_column']) && isset($_GET['order_type'])) {
                                            $columnsMap = [
                                                "Number" => "ID_COMMANDE",
                                                "Date" => "DATE",
                                                "Total price" => "TOTAL_PRICE_TTC",
                                                "State" => "STATE"
                                            ];

                                            $typesMap = [
                                                "Ascending" => "ASC",
                                                "Descending" => "DESC"
                                            ];

                                            $orderColumn = $columnsMap[$_GET['order_column']];
                                            $orderType = $typesMap[$_GET['order_type']];

                                            $query = "SELECT * FROM BON_COMMANDE_HEADER ORDER BY $orderColumn $orderType";
                                            $requete = $bd->prepare($query);
                                        } else {
                                            $query = "SELECT * FROM BON_COMMANDE_HEADER;";
                                            $requete = $bd->prepare($query);
                                        }

                                        $requete->execute();
                                        $result = $requete->fetchAll(PDO::FETCH_ASSOC);

                                        $query = "
                                                    SELECT ADMINISTRATOR, STATE_INITIATED, STATE_IN_PROGRESS, STATE_DELIVERING, STATE_HALTED, STATE_DELIVERED, STATE_CANCELED
                                                    FROM ROLES r
                                                    INNER JOIN USERS u ON r.ROLE_ID = u.USER_ROLE_ID
                                                    WHERE USER_ROLE_ID = :userID;
                                                ";

                                        $requete = $bd->prepare($query);
                                        $requete->bindValue(":userID", $userID);
                                        $requete->execute();
                                        $states = $requete->fetch(PDO::FETCH_ASSOC);

                                        $states_column_names = [];
                                        $user_states = [];
                                        $i = 0;

                                        foreach ($states as $state => $value) {
                                            if (($states["ADMINISTRATOR"] == 1) && ($state != "ADMINISTRATOR")) {
                                                $formattedState = strtolower(str_replace("_", " ", $state));
                                                $finalformattedState = str_ireplace("state ", "", $formattedState);
                                                $user_states[$i] = $finalformattedState;
                                                $states_column_names[$i] = $state;
                                                $i++;
                                            } elseif ($state !== "ADMINISTRATOR") {
                                                if ($value == 1) {
                                                    $formattedState = strtolower(str_replace("_", " ", $state));
                                                    $finalformattedState = strtolower(str_ireplace("state ", "", $formattedState));
                                                    $user_states[$i] = $finalformattedState;
                                                    $states_column_names[$i] = $state;
                                                    $i++;
                                                }
                                            }
                                        }

                                        if ($result && count($result) > 0):
                                            foreach ($result as $order):
                                                $query = "SELECT * FROM BON_COMMANDE_DETAILS WHERE ID_COMMANDE = :id";
                                                $request = $bd->prepare($query);
                                                $request->bindValue(":id", $order["ID"]);
                                                $request->execute();
                                                $details = $request->fetchAll(PDO::FETCH_ASSOC);

                                                $products_references = [];
                                                $products_quantities = [];
                                                $products_delivered_quantities = [];

                                                $i = 0;
                                                foreach ($details as $detail) {
                                                    $products_references[$i] = $detail["PRODUCT_ID"];
                                                    $products_quantities[$i] = $detail["QUANTITY"];
                                                    $products_delivered_quantities[$i] = $detail["DELIVERED_QUANTITY"];
                                                    $i++;
                                                }
                                        ?>
                                                <tr class="order-row">
                                                    <td class="order-number"><?php echo htmlspecialchars($order['ID_COMMANDE']); ?></td>
                                                    <td class="client-name"><?php echo htmlspecialchars($order['CLIENT_NAME']); ?></td>
                                                    <td class="client-ice"><?php echo htmlspecialchars($order['COMPANY_ICE']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['ADDRESSE']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['TOTAL_PRICE_TTC']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['DATE']); ?></td>
                                                    <td>
                                                        <?php if (($order['STATE'] === 'delivered' || $order['STATE'] === 'canceled')): ?>
                                                            <form method='POST' class="state-form">
                                                                <input type='hidden' name='orderID' value='<?php echo htmlspecialchars($order['ID']); ?>'>
                                                                <input type='hidden' name='action' value='modify_orders'>
                                                                <select name='new_state' disabled>
                                                                    <option value='<?php echo htmlspecialchars($order['STATE']); ?>' selected><?php echo htmlspecialchars($order['STATE']); ?></option>
                                                                </select>
                                                                <button disabled>Save</button>
                                                            </form>
                                                        <?php elseif (($order['STATE'] === 'delivering')): ?>
                                                            <form method='POST' class="state-form">
                                                                <input type='hidden' name='orderID' value='<?php echo htmlspecialchars($order['ID']); ?>'>
                                                                <input type='hidden' name='redirect' value='delivery_checks/create_delivery_check.php'>
                                                                <input type='hidden' name='action' value='modify_orders'>
                                                                <?php for ($i = 0; $i < count($products_references); $i++): ?>
                                                                    <input type='hidden' name='products_references[]' value='<?php echo htmlspecialchars($products_references[$i]); ?>'>
                                                                    <input type='hidden' name='products_quantities[]' value='<?php echo htmlspecialchars($products_quantities[$i]); ?>'>
                                                                    <input type='hidden' name='products_delivered_quantities[]' value='<?php echo htmlspecialchars($products_delivered_quantities[$i]); ?>'>
                                                                <?php endfor; ?>
                                                                <button type='submit' formaction='create_session.php'>Create delivery check</button>
                                                                <select name='new_state'>
                                                                    <option value='<?php echo htmlspecialchars($order['STATE']); ?>' selected><?php echo htmlspecialchars($order['STATE']); ?></option>
                                                                    <?php for ($j = 0; $j < count($user_states); $j++):
                                                                        $state_name = $states_column_names[$j];
                                                                        $state = $user_states[$j];
                                                                        if ($state !== strtolower($order['STATE'])):
                                                                    ?>
                                                                            <option value='<?php echo htmlspecialchars($state_name); ?>'><?php echo htmlspecialchars($state); ?></option>
                                                                    <?php endif;
                                                                    endfor; ?>
                                                                </select>
                                                                <button type='submit' formaction='check_user_privileges.php'>Save</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method='POST' class="state-form">
                                                                <input type='hidden' name='orderID' value='<?php echo htmlspecialchars($order['ID']); ?>'>
                                                                <input type='hidden' name='action' value='modify_orders'>
                                                                <select name='new_state'>
                                                                    <option value='<?php echo htmlspecialchars($order['STATE']); ?>' selected><?php echo htmlspecialchars($order['STATE']); ?></option>
                                                                    <?php for ($j = 0; $j < count($user_states); $j++):
                                                                        $state_name = $states_column_names[$j];
                                                                        $state = $user_states[$j];
                                                                        if ($state !== strtolower($order['STATE'])):
                                                                    ?>
                                                                            <option value='<?php echo htmlspecialchars($state_name); ?>'><?php echo htmlspecialchars($state); ?></option>
                                                                    <?php endif;
                                                                    endfor; ?>
                                                                </select>
                                                                <button type='submit' formaction='check_user_privileges.php'>Save</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <form method='GET'>
                                                            <input type='hidden' name='orderID' value='<?php echo htmlspecialchars($order['ID']); ?>'>
                                                            <button type='submit' formaction='show_order.php'>Show</button>
                                                            <?php if ($order["STATE"] !== "delivered" && $order['STATE'] !== 'canceled'): ?>
                                                                <button type='submit' formaction='modify_order.php'>Modify</button>
                                                            <?php else: ?>
                                                                <button disabled>Modify</button>
                                                            <?php endif; ?>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php
                                            endforeach;
                                        else:
                                            ?>
                                            <tr>
                                                <td colspan='8'>No orders in database</td>
                                            </tr>
                                        <?php
                                        endif;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                    <?php
                        endif;
                    } catch (PDOException $e) {
                        echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script defer>
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');

        if (error) {
            let message = error.replaceAll("_", " ");
            document.getElementById("error_tag").textContent = message;
        }

        // Order search functionality
        document.getElementById('order-search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#orders-table tbody tr.order-row');
            
            rows.forEach(row => {
                const orderNumber = row.querySelector('.order-number').textContent.toLowerCase();
                const clientName = row.querySelector('.client-name').textContent.toLowerCase();
                const clientIce = row.querySelector('.client-ice').textContent.toLowerCase();
                
                if (orderNumber.includes(searchTerm) || 
                    clientName.includes(searchTerm) || 
                    clientIce.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>