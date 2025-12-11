<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Product | Stockify</title>

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
        .modify-product-container {
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
        
        .modify-form {
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
        
        .form-group input[readonly] {
            background-color: #f5f5f5;
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
        
        .current-image-info {
            margin: 10px 0;
            font-size: 0.9rem;
            color: #666;
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
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../includes/menu.php"); ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <?php include("../../includes/topbar.php"); ?>

            <!-- ======================= Modify Product Content ================== -->
            <div class="modify-product-container">
                <h1 class="page-title">Modify Product</h1>
                
                <?php
                    try {
                        require("../../includes/db_connection.php");
                        $requete = null;

                        if ($_SERVER['REQUEST_METHOD'] === "GET" && isset($_GET['reference'])){
                            $ref = $_GET["reference"];

                            $requete = null;
                            $query = null;

                            $query = "SELECT * FROM PRODUCT WHERE ID = :ref";
                            $requete = $bd->prepare($query);
                            $requete->bindValue(":ref", $ref);
                            $requete->execute();
                            $result = $requete->fetch(PDO::FETCH_ASSOC);

                            $imageData = $result['IMAGE'];
                            $hasImage = $imageData ? true : false;
                ?>
                            <form method='POST' action='apply_modifications.php' enctype='multipart/form-data' class="modify-form">
                                <div class="form-group">
                                    <label>Reference</label>
                                    <input readonly type='text' value='<?= htmlspecialchars($result['REFERENCE']) ?>' name='reference'>
                                </div>
                                
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <input type='text' value='<?= htmlspecialchars($result['PRODUCT_NAME']) ?>' name='name' required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Price (MAD)</label>
                                    <input type='text' value='<?= htmlspecialchars($result['PRICE']) ?>' name='price' required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type='text' value='<?= htmlspecialchars($result['QUANTITY']) ?>' name='quantity' required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name='category'>
                                        <?php
                                            $query = "SELECT CATEGORYNAME FROM CATEGORY;";
                                            $requete = $bd->prepare($query);
                                            $requete->execute();
                                            $categories = $requete->fetchAll(PDO::FETCH_DEFAULT);

                                            if (count($categories) > 0){
                                                foreach ($categories as $category){
                                                    $selected = ($category['CATEGORYNAME'] === $result['CATEGORY_NAME']) ? "selected" : "";
                                                    echo "<option $selected>" . htmlspecialchars($category['CATEGORYNAME']) . "</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Product Image</label>
                                    <div class="file-upload">
                                        <input type='file' name='image' accept='image/*' id="image-upload">
                                        <div class="file-upload-preview" id="image-preview">
                                            <?php if ($hasImage): ?>
                                                <img src="data:image/jpeg;base64,<?= base64_encode($imageData) ?>" alt="Current Product Image">
                                            <?php else: ?>
                                                <ion-icon name="image-outline" style="font-size: 3rem; color: #ccc;"></ion-icon>
                                            <?php endif; ?>
                                        </div>
                                        <div class="current-image-info">
                                            <?= $hasImage ? "An image is already set" : "No image has been set" ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type='submit' class="submit-btn">
                                    <ion-icon name="save-outline"></ion-icon>
                                    Save Changes
                                </button>
                            </form>
                <?php
                        }
                    } catch (PDOException $e) {
                        echo "<div class='error-message'>Error: " . $e->getMessage() . "</div>";
                    }
                ?>
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