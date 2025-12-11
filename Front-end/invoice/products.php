<?php
$category = $_GET['category'];
$conn = new mysqli('localhost', 'root', '', 'STOCKIFY_DATABASE');
$sql = "SELECT * FROM PRODUCT WHERE CATEGORY_NAME='$category'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Products in <?= $category ?></h1>
    <div class="products">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product">
                <img src="data:image/jpeg;base64,<?= base64_encode($row['IMAGE']) ?>" alt="<?= $row['PRODUCT_NAME'] ?>">
                <h3><?= $row['PRODUCT_NAME'] ?></h3>
                <p>Quantity: <?= $row['QUANTITY'] ?></p>
                <form action="add_product.php" method="POST">
                    <input type="hidden" name="product_ref" value="<?= $row['REF_P'] ?>">
                    <input type="number" name="price" value="<?= $row['PRICE'] ?>" step="0.01">
                    <button type="submit">Add</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
