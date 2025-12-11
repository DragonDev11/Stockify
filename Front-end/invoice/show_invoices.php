



<iframe src="show_invoices.php1" frameborder="0">


        


</iframe>


























<?php
// Database connection settings
$host = "localhost"; // Or your database host
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "stockify_database"; // Your database name

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch invoices
$sql = "SELECT ID_INVOICE, CLIENT_NAME, COMPANY_NAME, COMPANY_ICE, REF_PRODUCT, PRICE_TTTC, TOTAL_PRICE, QUANTITY, INVOICE_TYPE FROM sell_invoices";
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    // Start table
    echo "<table border='1'>
            <tr>
                <th>ID_INVOICE</th>
                <th>CLIENT_NAME</th>
                <th>COMPANY_NAME</th>
                <th>COMPANY_ICE</th>
                <th>REF_PRODUCT</th>
                <th>PRICE_TTTC</th>
                <th>TOTAL_PRICE</th>
                <th>QUANTITY</th>
                <th>INVOICE_TYPE</th>
            </tr>";

    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['ID_INVOICE'] . "</td>
                <td>" . $row['CLIENT_NAME'] . "</td>
                <td>" . $row['COMPANY_NAME'] . "</td>
                <td>" . $row['COMPANY_ICE'] . "</td>
                <td>" . $row['REF_PRODUCT'] . "</td>
                <td>" . $row['PRICE_TTTC'] . "</td>
                <td>" . $row['TOTAL_PRICE'] . "</td>
                <td>" . $row['QUANTITY'] . "</td>
                <td>" . $row['INVOICE_TYPE'] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No invoices found.";
}

// Close the connection
$conn->close();
?>
