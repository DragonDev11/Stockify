<?php
session_start();

// Check if invoice data exists
if (!isset($_SESSION['invoice']) || !isset($_SESSION['client'])) {
    header('Location: invoice.php'); // Redirect to the start if session data is missing
    exit();
}

// Retrieve client and invoice data
$clientData = $_SESSION['client'];
$invoiceData = $_SESSION['invoice'];

// Calculate total invoice price
$totalPrice = array_reduce($invoiceData, function ($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice Summary</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .print-button {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        .print-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Invoice Summary</h1>
        
        <!-- Client and Invoice Information -->
        <h2>Client Information</h2>
        <table>
            <tr>
                <th>Client Name</th>
                <td><?php echo htmlspecialchars($clientData['name']); ?></td>
            </tr>
            <tr>
                <th>Invoice Type</th>
                <td><?php echo htmlspecialchars($clientData['type']); ?></td>
            </tr>
            <?php if ($clientData['type'] === 'company'): ?>
                <tr>
                    <th>Company Name</th>
                    <td><?php echo htmlspecialchars($clientData['company_name']); ?></td>
                </tr>
                <tr>
                    <th>ICE</th>
                    <td><?php echo htmlspecialchars($clientData['ice']); ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Product List -->
        <h2>Products</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoiceData as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="total">Total</td>
                    <td>$<?php echo number_format($totalPrice, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Print Button -->
        <a href="#" class="print-button" onclick="window.print()">Print Invoice</a>
    </div>
</body>
</html>
