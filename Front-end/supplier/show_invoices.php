<?php
session_start();
if (!isset($_SESSION["user"]["username"])){
    header("Location: /Stockify/Front-end/login/index.php?error=not_logged_in");
    exit();
}
require("../../includes/functions.php");
// Database connection
$host = 'localhost';
$dbname = 'stockify_database';
$username = 'root';
$password = '';
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$username = $_SESSION["user"]["username"];

// Fetch invoices for the selected supplier
if (isset($_GET['supplier'])) {
    $supplierName = $_GET['supplier'];
    $stmt = $conn->prepare("SELECT * FROM BUY_INVOICE_HEADER WHERE SUPPLIER_NAME = :supplier_name ORDER BY DATE DESC");
    $stmt->bindParam(':supplier_name', $supplierName);
    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "<p class='error'>No supplier selected.</p>";
    exit;
}

// Handle invoice deletion
if (isset($_POST['delete_invoice'])) {
    $invoiceId = $_POST['invoice_id'];
    $deleteStmt = $conn->prepare("DELETE FROM BUY_INVOICE_HEADER WHERE ID_INVOICE = :invoice_id");
    $deleteStmt->bindParam(':invoice_id', $invoiceId);
    $deleteStmt->execute();
    header("Location: show_invoices.php?supplier=" . urlencode($supplierName));
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Invoices for <?php echo htmlspecialchars($supplierName); ?></title>
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

        .delete-btn,
        .details-btn {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .delete-btn {
            background: red;
            color: white;
        }

        .details-btn {
            background: green;
            color: white;
        }
    </style>
</head>

<body>
    <h2>Invoices for <?php echo htmlspecialchars($supplierName); ?></h2>
    <a href="supplier.php" class="back-btn">Back to Suppliers</a>

    <?php if (count($invoices) > 0): ?>
        <table>
            <tr>
                <th>Invoice Number</th>
                <th>Supplier</th>
                <th>Total Price HT</th>
                <th>Total Price TVA</th>
                <th>Total Price TTC</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><?php echo htmlspecialchars($invoice['INVOICE_NUMBER']); ?></td>
                    <td><?php echo htmlspecialchars($supplierName); ?></td>
                    <td><?php echo htmlspecialchars($invoice['TOTAL_PRICE_HT']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['TOTAL_PRICE_TVA']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['TOTAL_PRICE_TTC']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['DATE']); ?></td>
                    <td>
                        <a href="show_invoice_details.php?id_invoice=<?php echo $invoice['ID_INVOICE']; ?>"
                            class="details-btn">Show Details</a>
                        <?php if (check_role("ADD_SUPPLIERS", $username) || check_role("ADMINISTRATOR", $username)): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="invoice_id" value="<?php echo $invoice['ID_INVOICE']; ?>">
                                <button type="submit" name="delete_invoice" class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this invoice?');">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No invoices found for this supplier.</p>
    <?php endif; ?>
</body>

</html>