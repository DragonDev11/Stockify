<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category | Stockify</title>

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
        .add-category-container {
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
        
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
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
            margin-top: 20px;
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
        
        .view-categories-btn {
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
        
        .btn {
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
            text-decoration: none;
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

            <!-- ======================= Add Category Content ================== -->
            <div class="add-category-container">
                <h1 class="page-title">Add New Category</h1>
                <button type="button" class="btn" onclick="window.location.href='show_categories.php'">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back
                </button>
                <?php
                    try {
                        // Connect to the database
                        require("../../includes/db_connection.php");
                        $requete = null;

                        ini_set('mysqli.max_allowed_packet', '50M');

                        if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['name'])){
                            $name = $_POST["name"];

                            if (isset($_FILES['image'])){
                                $image = $_FILES['image']['tmp_name'];
                            } else {
                                $image = null;
                            }
                            $imageData = ($image != null) ? file_get_contents($image) : null;

                            $requete = null;
                            $query = null;

                            // Check if category exists
                            $query = "SELECT CATEGORYNAME FROM CATEGORY WHERE CATEGORYNAME = :c_name";
                            $requete = $bd->prepare($query);
                            $requete->bindValue(":c_name", $name);
                            $requete->execute();
                            $result = $requete->fetchAll(PDO::FETCH_DEFAULT);

                            if (count($result) > 0){
                                echo "<div class='error-message'>This category already exists</div>";
                            } else {
                                // Insert new category
                                $query = "INSERT INTO CATEGORY (CATEGORYNAME, IMAGE) VALUES(:c_name, :c_image);";
                                $requete = $bd->prepare($query);
                                $requete->bindValue(":c_name", $name);
                                if ($imageData != null){
                                    $requete->bindParam(":c_image", $imageData, PDO::PARAM_LOB);
                                } else {
                                    $requete->bindValue(":c_image", NULL);
                                }
                                $requete->execute();

                                // Update product count
                                $query = "SELECT count(*) as product_count FROM PRODUCT WHERE CATEGORY_NAME = :c_name";
                                $requete = $bd->prepare($query);
                                $requete->bindValue(":c_name", $name);
                                $requete->execute();
                                $result = $requete->fetch(PDO::FETCH_ASSOC);
                                $count = $result['product_count'];
                                
                                if ($count > 0){
                                    $query = "UPDATE CATEGORY SET NUMBER_OF_PRODUCTS = :c_num_p WHERE CATEGORYNAME = :c_name;";
                                    $requete = $bd->prepare($query);
                                    $requete->bindValue(":c_num_p", $count);
                                    $requete->bindValue(":c_name", $name);

                                    try {
                                        $requete->execute();
                                        echo "<div class='success-message'>Category added successfully!</div>";
                                    } catch (PDOException $e){
                                        echo "<div class='error-message'>Error updating product count: " . $e->getMessage() . "</div>";
                                    }
                                } else {
                                    echo "<div class='success-message'>Category added successfully!</div>";
                                }
                            }
                        }
                    } catch (PDOException $e) {
                        echo "<div class='error-message'>Error: " . $e->getMessage() . "</div>";
                    }
                ?>
                
                <form method='POST' action='add_category.php' enctype='multipart/form-data' class="add-form">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category Image</label>
                        <div class="file-upload">
                            <input type="file" name="image" accept="image/*" id="image-upload">
                            <div class="file-upload-preview" id="image-preview">
                                <ion-icon name="image-outline" style="font-size: 3rem; color: #ccc;"></ion-icon>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <ion-icon name="add-outline"></ion-icon>
                        Add Category
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