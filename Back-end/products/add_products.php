<?php
session_start();
ob_start(); // Start output buffering at the very beginning

ini_set("max_allowed_packet", "50M");

try {
    require "../../includes/db_connection.php";
    $requete = null;
    $message = "";
    $error = "";

    // Process form submission
    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["name"]) &&
        isset($_POST["price"]) &&
        isset($_POST["quantity"]) &&
        isset($_POST["category"])
    ) {
        $TVA = isset($GENERAL_VARIABLES["TVA_AMOUNT"])
            ? $GENERAL_VARIABLES["TVA_AMOUNT"] / 100.0
            : 0.2; // Default to 20% if not set
        $ref = "P" . bin2hex(random_bytes(7.5));
        $name = $_POST["name"];
        $price = $_POST["price"] + $_POST["price"] * $TVA;
        $quantity = $_POST["quantity"];
        $cat = $_POST["category"];

        // Handle image upload
        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {
            $image = $_FILES["image"]["tmp_name"];
            $imageData = file_get_contents($image);
        } else {
            $imageData = null;
        }

        // Check if product exists
        $query =
            "SELECT PRODUCT_NAME FROM PRODUCT WHERE PRODUCT_NAME = :p_name";
        $requete = $bd->prepare($query);
        $requete->bindValue(":p_name", $name);
        $requete->execute();
        $result = $requete->fetchAll(PDO::FETCH_DEFAULT);

        if (count($result) > 0) {
            // Update existing product quantity
            $query =
                "UPDATE PRODUCT SET QUANTITY=QUANTITY+:p_quantity WHERE PRODUCT_NAME = :p_name";
            $requete = $bd->prepare($query);
            $requete->bindValue(":p_quantity", $quantity);
            $requete->bindValue(":p_name", $name);
            $message = "Product quantity updated successfully!";
        } else {
            // Insert new product
            $query = "INSERT INTO PRODUCT (REFERENCE,PRODUCT_NAME,PRICE,QUANTITY,CATEGORY_NAME,IMAGE) VALUES(
                    :p_reference,
                    :p_name,
                    :p_price,
                    :p_quantity,
                    :p_cat,
                    :p_image
                );";
            $requete = $bd->prepare($query);
            $requete->bindValue(":p_reference", $ref);
            $requete->bindValue(":p_name", $name);
            $requete->bindValue(":p_quantity", $quantity);
            $requete->bindValue(":p_price", $price);
            $requete->bindValue(":p_cat", $cat);
            if ($imageData != null) {
                $requete->bindParam(":p_image", $imageData, PDO::PARAM_LOB);
            } else {
                $requete->bindValue(":p_image", null, PDO::PARAM_NULL);
            }
            $message = "Product added successfully!";
        }

        $requete->execute();

        // Update category product count if it's a new product
        if (count($result) === 0) {
            $query =
                "UPDATE CATEGORY SET NUMBER_OF_PRODUCTS = NUMBER_OF_PRODUCTS + 1 WHERE CATEGORYNAME = :p_category";
            $requete = $bd->prepare($query);
            $requete->bindValue(":p_category", $cat);
            $requete->execute();
        }

        // Clear output buffer and redirect
        ob_end_clean();
        header("Location: show_products.php?success=" . urlencode($message));
        exit();
    }

    // Get categories for dropdown
    $requete = $bd->prepare("SELECT CATEGORYNAME FROM CATEGORY");
    $requete->execute();
    $categories = $requete->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

// Flush the output buffer before HTML
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Stockify</title>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .add-product-container {
            padding: 30px;
            margin: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .page-title {
            color: #0ce48d;
            margin-bottom: 30px;
        }

        .add-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-group input::placeholder {
            color: #999;
        }

        .file-upload {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .file-upload-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 15px 0;
        }

        .file-upload-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .submit-btn {
            background: #0ce48d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .submit-btn:hover {
            background: #09c77d;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 5px;
            font-size: 0.9rem;
        }

        .success-message {
            color: #2ecc71;
            margin-top: 5px;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .action-btn {
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .view-products-btn {
            background: #3498db;
            color: white;
        }

        .dashboard-btn {
            background: #9b59b6;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include "../../includes/menu.php"; ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <?php include "../../includes/topbar.php"; ?>

            <!-- ======================= Add Product Content ================== -->
            <div class="add-product-container">
                <h1 class="page-title">Add New Product</h1>

                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars(
                        $error,
                    ) ?></div>
                <?php endif; ?>

                <?php if (isset($_GET["success"])): ?>
                    <div class="success-message"><?= htmlspecialchars(
                        $_GET["success"],
                    ) ?></div>
                <?php endif; ?>

                <form method='POST' action='add_products.php' enctype='multipart/form-data' class="add-form">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type='text' name='name' required value="<?= isset(
                            $_POST["name"],
                        )
                            ? htmlspecialchars($_POST["name"])
                            : "" ?>">
                    </div>

                    <div class="form-group">
                        <label>Price (MAD)</label>
                        <input type='text' name='price' placeholder='0.00' required value="<?= isset(
                            $_POST["price"],
                        )
                            ? htmlspecialchars($_POST["price"])
                            : "" ?>">
                    </div>

                    <div class="form-group">
                        <label>Quantity</label>
                        <input type='text' name='quantity' placeholder='0' required value="<?= isset(
                            $_POST["quantity"],
                        )
                            ? htmlspecialchars($_POST["quantity"])
                            : "" ?>">
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name='category' required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars(
                                    $category["CATEGORYNAME"],
                                ) ?>"
                                    <?= isset($_POST["category"]) &&
                                    $_POST["category"] ===
                                        $category["CATEGORYNAME"]
                                        ? "selected"
                                        : "" ?>>
                                    <?= htmlspecialchars(
                                        $category["CATEGORYNAME"],
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Product Image</label>
                        <div class="file-upload">
                            <input type='file' name='image' accept='image/*' id="image-upload">
                            <div class="file-upload-preview" id="image-preview">
                                <ion-icon name="image-outline" style="font-size: 3rem; color: #ccc;"></ion-icon>
                            </div>
                        </div>
                    </div>

                    <button type='submit' class="submit-btn">
                        <ion-icon name="add-outline"></ion-icon>
                        Add Product
                    </button>
                </form>


            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Navigation hover effect
            let list = document.querySelectorAll(".navigation li");
            function activeLink() {
                list.forEach((item) => item.classList.remove("hovered"));
                this.classList.add("hovered");
            }
            list.forEach((item) => item.addEventListener("mouseover", activeLink));

            // Sidebar toggle
            let toggle = document.querySelector(".toggle");
            let navigation = document.querySelector(".navigation");
            let main = document.querySelector(".main");
            if (toggle && navigation && main) {
                toggle.onclick = function () {
                    navigation.classList.toggle("active");
                    main.classList.toggle("active");
                };
            }

            // Image preview functionality
            const imageUpload = document.getElementById('image-upload');
            const imagePreview = document.getElementById('image-preview');

            if (imageUpload && imagePreview) {
                imageUpload.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            imagePreview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>
