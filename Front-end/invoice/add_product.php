<?php
session_start();



// Check if required data is set in the session
if (!isset($_SESSION['client'])) {
    header('Location: create_invoice_form.php'); // Redirect to start if session data is missing
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve product details
    $productName = $_POST['product_name'];
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];

    // Add the product to the invoice session
    if (!isset($_SESSION['invoice'])) {
        $_SESSION['invoice'] = [];
    }

    $_SESSION['invoice'][] = [
        'name' => $productName,
        'price' => $price,
        'quantity' => $quantity,
    ];
}

// Check if a category was passed
if (!isset($_GET['category'])) {
    header('Location: categories.php'); // Redirect back if no category is chosen
    exit();
}

// Get the selected category from the query parameter
$category = $_GET['category'];

// Connect to the database (replace with your database connection)
$conn = new mysqli('localhost', 'root', '', 'STOCKIFY_DATABASE');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch products in the selected category
$sql = "SELECT REF_P, PRODUCT_NAME, PRICE, QUANTITY, IMAGE FROM PRODUCT WHERE CATEGORY_NAME = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $category);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Products</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function addProduct(ref, name, price, quantity) {
            const addAnother = confirm(`Do you want to add ${name} to the invoice?`);

            if (addAnother) {
                // Save product data in session via an AJAX call
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'process_product.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send(`ref=${ref}&name=${name}&price=${price}&quantity=${quantity}`);

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert('Product added successfully!');
                        window.location.href = 'categories.php';
                    } else {
                        alert('An error occurred.');
                    }
                };
            } else {
                window.location.href = 'invoice_summary.php';
            }
        }
    </script>
</head>
<body>
    <h1>Products in <?php echo htmlspecialchars($category); ?></h1>
    <div class="products-container">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($product['IMAGE']); ?>" alt="Product Image">
                <h2><?php echo htmlspecialchars($product['PRODUCT_NAME']); ?></h2>
                <p>Price: $<?php echo number_format($product['PRICE'], 2); ?></p>
                <p>Quantity Left: <?php echo $product['QUANTITY']; ?></p>
                <button onclick="addProduct('<?php echo $product['REF_P']; ?>', '<?php echo addslashes($product['PRODUCT_NAME']); ?>', '<?php echo $product['PRICE']; ?>', '<?php echo $product['QUANTITY']; ?>')">Add to Invoice</button>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
