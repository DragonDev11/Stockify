<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'STOCKIFY_DATABASE');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les catégories
$sql = "SELECT CATEGORYNAME, IMAGE, NUMBER_OF_PRODUCTS FROM CATEGORY";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Categories</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Select a Category</h1>
    <div class="categories">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="category">
                <a href="products.php?category=<?= $row['CATEGORYNAME'] ?>">
                    <img src="data:image/jpeg;base64,<?= base64_encode($row['IMAGE']) ?>" alt="<?= $row['CATEGORYNAME'] ?>">
                    <h3><?= $row['CATEGORYNAME'] ?></h3>
                    <p><?= $row['NUMBER_OF_PRODUCTS'] ?> products</p>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
