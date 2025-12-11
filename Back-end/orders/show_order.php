<?php
session_start();
require "../../includes/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["orderID"])) {
    $id = $_GET["orderID"];
    require "../../includes/db_connection.php";

    // Get order details
    $query = "SELECT * FROM BON_COMMANDE_DETAILS WHERE ID_COMMANDE = :id";
    $request = $bd->prepare($query);
    $request->bindValue(":id", $id, PDO::PARAM_INT);
    $request->execute();
    $details = $request->fetchAll(PDO::FETCH_ASSOC);

    // Get order header
    $query = "SELECT * FROM BON_COMMANDE_HEADER WHERE ID = :id";
    $request = $bd->prepare($query);
    $request->bindValue(":id", $id, PDO::PARAM_INT);
    $request->execute();
    $header = $request->fetch(PDO::FETCH_ASSOC);
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

    <script src="../../main.js"></script>

    <title>Order Details</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style_choose_products.css">
    <style>
        .order-details-container {
            margin: 20px;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .order-header {
            margin-bottom: 20px;
        }

        .order-header h1 {
            color: #0ce48d;
            text-align: center;
            margin-bottom: 20px;
        }

        .order-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .order-info p {
            margin: 8px 0;
            font-size: 16px;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .order-table th {
            background: #0ce48d;
            color: white;
            padding: 12px;
            text-align: center;
        }

        .order-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        .order-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .total-table {
            width: 50%;
            margin: 20px auto;
        }

        .total-table th {
            background: #333;
            color: white;
            padding: 12px;
            text-align: center;
        }

        .total-table td {
            padding: 12px;
            text-align: center;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 20px 0;
        }

        .action-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .deliveries-btn {
            background: #0ce48d;
            color: white;
        }

        .deliveries-btn:hover {
            background: #0abf7a;
        }

        .invoices-btn {
            background: #3498db;
            color: white;
        }

        .invoices-btn:hover {
            background: #2980b9;
        }

        .print-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #333;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #555;
        }

        @media print {

            .navigation,
            .topbar,
            .action-buttons,
            .print-btn {
                display: none;
            }

            .main {
                width: 100%;
                left: 0;
            }
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



                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name .
                        " " .
                        $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Order Details Content ================== -->
            <div class="order-details-container">
                <?php if (isset($header) && isset($details)): ?>
                    <div class="order-header">
                        <h1>Order Details</h1>

                        <div class="order-info">
                            <p><strong>Order Number:</strong> <?php echo htmlspecialchars(
                                $header["ID_COMMANDE"],
                            ); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars(
                                $header["DATE"],
                            ); ?></p>
                            <p><strong>Client Name:</strong> <?php echo !empty(
                                $header["CLIENT_NAME"]
                            )
                                ? htmlspecialchars($header["CLIENT_NAME"])
                                : "Unknown"; ?></p>
                            <?php if (!empty($header["COMPANY_ICE"])): ?>
                                <p><strong>Company ICE:</strong> <?php echo htmlspecialchars(
                                    $header["COMPANY_ICE"],
                                ); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($header["ADDRESSE"])): ?>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars(
                                    $header["ADDRESSE"],
                                ); ?></p>
                            <?php endif; ?>
                        </div>

                        <h2>Products</h2>
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Quantity</th>
                                    <th>Product Name</th>
                                    <th>Unit Price TTC</th>
                                    <th>Total TTC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($details && count($details) > 0): ?>
                                    <?php foreach ($details as $detail): ?>
                                        <?php
                                        $query =
                                            "SELECT PRODUCT_NAME FROM PRODUCT WHERE REFERENCE = :ref";
                                        $request = $bd->prepare($query);
                                        $request->bindValue(
                                            ":ref",
                                            $detail["PRODUCT_ID"],
                                            PDO::PARAM_STR,
                                        );
                                        $request->execute();
                                        $name = $request->fetchColumn(0);
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(
                                                $detail["QUANTITY"],
                                            ); ?></td>
                                            <td><?php echo htmlspecialchars(
                                                $name,
                                            ); ?></td>
                                            <td><?php echo htmlspecialchars(
                                                $detail["UNIT_PRICE_TTC"],
                                            ); ?></td>
                                            <td><?php echo htmlspecialchars(
                                                $detail["TOTAL_PRICE_TTC"],
                                            ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">No products have been added</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <table class="total-table">
                            <thead>
                                <tr>
                                    <th>TOTAL TTC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo htmlspecialchars(
                                        $header["TOTAL_PRICE_TTC"],
                                    ); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <form method='post' class="action-buttons">
                            <input type='hidden' name='orderID' value='<?php echo htmlspecialchars(
                                $id,
                            ); ?>'>
                            <button type='submit' formaction='delivery_checks/show_delivery_checks.php' class="deliveries-btn">Show Related Deliveries</button>
                            <button type='submit' formaction='sell_invoices/show_sell_invoices.php' class="invoices-btn">Show Invoices</button>
                        </form>

                        <button onclick="window.print()" class="print-btn">Print Order</button>
                    </div>
                <?php else: ?>
                    <div class="error-message">
                        <p>Order not found or invalid request</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
