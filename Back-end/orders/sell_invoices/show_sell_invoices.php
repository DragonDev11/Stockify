<?php
session_start();
require("../../../includes/db_connection.php");
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

    <title>Invoices List</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .invoices-container {
            margin: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .invoices-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .invoices-table th {
            background: #0ce48d;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .invoices-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .invoices-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .invoices-table tr:hover {
            background: #f0f0f0;
        }
        
        .show-btn {
            padding: 8px 16px;
            background: #0ce48d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .show-btn:hover {
            background: #0abf7a;
        }
        
        .no-invoices {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
        }
        
        .page-title {
            color: #0ce48d;
            text-align: center;
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

            <!-- ======================= Invoices Content ================== -->
            <div class="invoices-container">
                <h1 class="page-title">Invoices List</h1>
                
                <?php
                try {
                    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["orderID"])) {
                        $orderID = $_POST["orderID"];
                        
                        $query = "SELECT * FROM SELL_INVOICE_HEADER WHERE ID_BON_LIVRAISON IN (
                            SELECT ID FROM BON_LIVRAISON_HEADER
                            WHERE ID_COMMANDE = :id
                        );";
                        $request = $bd->prepare($query);
                        $request->bindValue(":id", $orderID);
                        $request->execute();
                        $result = $request->fetchAll(PDO::FETCH_ASSOC);
                        //var_dump($result);
                ?>
                        <table class="invoices-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <?php if ($result): ?>
                                        <?php if ($result[0]["COMPANY_ICE"] != ""): ?>
                                            <th>ICE</th>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <th>ICE</th>
                                    <?php endif; ?>
                                    <th>Total Price TTC</th>
                                    <th>Total Price HT</th>
                                    <th>Total TVA</th>
                                    <th>Payement</th>
                                    <th>Payement Reference</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && count($result) > 0): ?>
                                    <?php foreach ($result as $invoice): ?>
                                        <tr>
                                            <form method='POST'>
                                                <input type='hidden' name='id' value='<?php echo htmlspecialchars($invoice['ID_INVOICE']); ?>'>
                                                <td><?php echo htmlspecialchars($invoice['INVOICE_NUMBER']); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['CLIENT_NAME']); ?></td>
                                                <?php if ($invoice["COMPANY_ICE"] != ""): ?>
                                                    <td><?php echo htmlspecialchars($invoice['COMPANY_ICE']); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo htmlspecialchars($invoice['TOTAL_PRICE_TTC']); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['TOTAL_PRICE_HT']); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['TVA']); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['PAYEMENT']); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['PAYEMENT_REFERENCE']); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['DATE']); ?></td>
                                                <td><button type='submit' formaction='show_sell_invoice.php' class="show-btn">SHOW</button></td>
                                            </form>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="no-invoices">No invoices found for this order</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                <?php
                    } else {
                        echo '<div class="no-invoices">Invalid request. Please select an order first.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="no-invoices">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>