<?php

// Database connection
$host = 'localhost';
$dbname = 'stockify_database';
$username = 'root';
$password = '';
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch invoice details based on the invoice ID
if (isset($_GET['id_invoice'])) {
    $invoiceId = $_GET['id_invoice'];
    $stmt = $conn->prepare("SELECT * FROM BUY_INVOICE_DETAILS WHERE ID_INVOICE = :id_invoice");
    $stmt->bindParam(':id_invoice', $invoiceId);
    $stmt->execute();
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "<p class='error'>No invoice selected.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Invoice Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f4f4f4;
        }

        .back-btn {
            background: blue;
            color: white;
            padding: 10px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <h2>Invoice Details</h2>
    <a href="javascript:history.back()" class="back-btn">Back</a>

    <?php if (count($details) > 0): ?>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price TTC</th>
                <th>Total Price</th>
            </tr>
            <?php foreach ($details as $detail): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detail['PRODUCT_NAME']); ?></td>
                    <td><?php echo htmlspecialchars($detail['QUANTITY']); ?></td>
                    <td><?php echo htmlspecialchars($detail['UNIT_PRICE_TTC']); ?></td>
                    <td><?php echo htmlspecialchars($detail['TOTAL_PRICE']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No details found for this invoice.</p>
    <?php endif; ?>
</body>

</html>