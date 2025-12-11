<?php
// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$dbname = "stockify_database";

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Collect and sanitize input data
    $clientName = $conn->real_escape_string($_POST['clientName']);
    $invoiceType = $conn->real_escape_string($_POST['invoiceType']);
    $companyName = isset($_POST['companyName']) ? $conn->real_escape_string($_POST['companyName']) : null;
    $companyIce = isset($_POST['companyIce']) ? $conn->real_escape_string($_POST['companyIce']) : null;
    $refProduct = $conn->real_escape_string($_POST['refProduct']);
    $priceTttc = $conn->real_escape_string($_POST['priceTttc']);
    $totalPrice = $conn->real_escape_string($_POST['totalPrice']);
    $quantity = $conn->real_escape_string($_POST['quantity']);

    // Prepare SQL query to insert the data into the database
    $sql = "INSERT INTO sell_invoices (CLIENT_NAME, COMPANY_NAME, COMPANY_ICE, REF_PRODUCT, PRICE_TTTC, TOTAL_PRICE, QUANTITY, INVOICE_TYPE)
            VALUES ('$clientName', '$companyName', '$companyIce', '$refProduct', '$priceTttc', '$totalPrice', '$quantity', '$invoiceType')";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "New invoice created successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the connection
$conn->close();
?>
