<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../main.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/style.css">

    <title>Choose Category - Stockify</title>
    <style>
        /* =============== Base Styles ============== */
        * {
            font-family: "Parkinsans", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue: #0ce48d;
            --white: #fff;
            --gray: #f5f5f5;
            --black1: #222;
            --black2: #999;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: #f5f5f5;
        }

        /* =============== Categories Container ============== */
        .categories-container {
            margin: 20px;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .categories-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .categories-header h1 {
            color: #0ce48d;
            margin: 0;
            font-size: 1.8rem;
        }

        /* =============== Categories Grid ============== */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .category-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .category-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .category-content {
            padding: 15px;
        }

        .category-name {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 5px;
        }

        .category-products {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
        }

        .category-btn {
            width: 100%;
            padding: 10px;
            background: #0ce48d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }

        .category-btn:hover {
            background: #0abf7a;
        }

        .category-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }

        .empty-message {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1rem;
        }

        /* =============== Error Message ============== */
        .error-message {
            color: #e74c3c;
            padding: 12px;
            background: #fde8e8;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #e74c3c;
        }

        /* =============== Search bar styles ============== */
        .search {
            position: relative;
        }
        
        .search-results {
            display: none;
            position: absolute;
            background: white;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .search-results div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .search-results div:hover {
            background: #f5f5f5;
        }
        
        #error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
        }

        /* =============== Responsive Design ============== */
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .categories-container {
                margin: 10px;
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../includes/menu.php"); ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" id="category-search-input" placeholder="Search categories by name...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="category-search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Content ================== -->
            <div class="categories-container">
                <div class="categories-header">
                    <h1>Choose Category</h1>
                </div>

                <p id="error-message"></p>

                <?php if (!isset($_SESSION['quotation'])): ?>
                    <div class="error-message">Session expired or invalid access. Please start the quotation process again.</div>
                <?php else: ?>
                    <div class="categories-grid" id="categories-grid">
                        <?php
                        if (isset($_SESSION['quotation'])) {
                            require("../../includes/db_connection.php");

                            $request = $bd->prepare("SELECT CATEGORYNAME, NUMBER_OF_PRODUCTS, IMAGE FROM CATEGORY;");

                            try {
                                $request->execute();
                            } catch (PDOException $e) {
                                echo "<div class='error-message'>Database error: " . $e->getMessage() . "</div>";
                                exit();
                            }

                            $result = $request->fetchAll(PDO::FETCH_ASSOC);

                            if (count($result) > 0) {
                                if (isset($_SESSION['quotation']['client_name']) && isset($_SESSION['quotation']['sell_type'])) {
                                    foreach ($result as $category) {
                                        $cat_name = $category["CATEGORYNAME"];
                                        $num_products = $category["NUMBER_OF_PRODUCTS"];
                                        $imageData = $category["IMAGE"];
                        ?>
                                        <div class="category-card" data-category-name="<?= htmlspecialchars(strtolower($cat_name)) ?>">
                                            <?php if ($imageData): ?>
                                                <img src="data:image/jpeg;base64,<?= base64_encode($imageData) ?>" alt="<?= $cat_name ?>" class="category-image">
                                            <?php else: ?>
                                                <div class="category-image" style="background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                                    <ion-icon name="image-outline" style="font-size: 3rem; color: #ccc;"></ion-icon>
                                                </div>
                                            <?php endif; ?>
                                            <div class="category-content">
                                                <h3 class="category-name"><?= $cat_name ?></h3>
                                                <p class="category-products"><?= $num_products ?> product<?= $num_products != 1 ? 's' : '' ?></p>
                                                <form method="POST" action="choose_product.php">
                                                    <input type="hidden" name="category" value="<?= $cat_name ?>">
                                                    <button type="submit" class="category-btn" <?= $num_products == 0 ? 'disabled' : '' ?>>
                                                        <?= $num_products > 0 ? 'Select Category' : 'No Products' ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                        <?php
                                    }
                                }
                            } else {
                                echo '<div class="empty-message">No categories have been added yet</div>';
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                toggle.onclick = function() {
                    navigation.classList.toggle("active");
                    main.classList.toggle("active");
                };
            }
            
            // Category search functionality
            document.getElementById('category-search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = document.querySelectorAll('#categories-grid .category-card');
                
                cards.forEach(card => {
                    const categoryName = card.getAttribute('data-category-name');
                    
                    if (categoryName.includes(searchTerm)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
            
            function show_error(message) {
                document.getElementById("error-message").textContent = message;
            }

            // Handle URL Parameters for Errors
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');

            if (error) {
                let message = '';
                switch (error) {
                    case 'permission_denied':
                        message = 'Permission denied. Contact your administrator.';
                        break;
                    default:
                        message = 'An unknown error occurred.';
                }
                show_error(message);
            }
        });
    </script>
</body>

</html>