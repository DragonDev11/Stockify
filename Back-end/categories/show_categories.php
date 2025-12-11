<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | Stockify</title>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/style_choose_products.css">
    <style>
        .categories-container {
            padding: 30px;
            margin: 20px;
        }

        .categories-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .add-category-btn {
            background: #0ce48d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-category-btn:hover {
            background: #09c77d;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .category-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .category-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .category-content {
            padding: 20px;
        }

        .category-name {
            color: #0ce48d;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .product-count {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .category-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .category-actions button {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .show-btn {
            background: #0ce48d;
            color: white;
        }

        .modify-btn {
            background: #3498db;
            color: white;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        .category-actions button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .no-categories {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1rem;
        }

        /* Search bar styles */
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
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php
        include "../../includes/menu.php";
        require "../../includes/db_connection.php";
        ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" id="search-input" placeholder="Search categories by name...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name .
                        " " .
                        $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Categories Content ================== -->
            <div class="categories-container">
                <div class="categories-header">
                    <h2>Product Categories</h2>
                    <form method="POST" action="check_user_privileges.php">
                        <input type="hidden" name="action" value="add_categories">
                        <button type="submit" class="add-category-btn">
                            <ion-icon name="add-outline"></ion-icon>
                            Add Category
                        </button>
                    </form>
                </div>

                <div class="categories-grid">
                    <?php
                    $request = $bd->prepare(
                        "SELECT CATEGORYNAME, NUMBER_OF_PRODUCTS, IMAGE FROM CATEGORY",
                    );
                    $request->execute();

                    $result = $request->fetchAll(PDO::FETCH_ASSOC);

                    if (count($result) > 0) {
                        foreach ($result as $index => $category) {

                            $cat_name = $category["CATEGORYNAME"];
                            $num_products = $category["NUMBER_OF_PRODUCTS"];
                            $imageData = $category["IMAGE"];
                            ?>
                            <div class="category-card">
                                <?php if ($imageData): ?>
                                    <img class="category-image" src="data:image/jpeg;base64,<?= base64_encode(
                                        $imageData,
                                    ) ?>" alt="<?= htmlspecialchars(
    $cat_name,
) ?>">
                                <?php else: ?>
                                    <div class="category-image" style="background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                        <ion-icon name="image-outline" style="font-size: 3rem; color: #ccc;"></ion-icon>
                                    </div>
                                <?php endif; ?>

                                <div class="category-content">
                                    <h3 class="category-name"><?= htmlspecialchars(
                                        $cat_name,
                                    ) ?></h3>
                                    <p class="product-count"><?= $num_products ?> Products</p>

                                    <div class="category-actions">
                                        <form method="POST">
                                            <input type="hidden" name="category" value="<?= htmlspecialchars(
                                                $cat_name,
                                            ) ?>">
                                            <button type="submit" formaction="show_category.php" class="show-btn">Show</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="category" value="<?= htmlspecialchars(
                                                $cat_name,
                                            ) ?>">
                                            <input type="hidden" name="action" value="modify_categories">
                                            <button type="submit" formaction="check_user_privileges.php" class="modify-btn">Modify</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="category" value="<?= htmlspecialchars(
                                                $cat_name,
                                            ) ?>">
                                            <input type="hidden" name="action" value="delete_categories">
                                            <button type="submit" formaction="check_user_privileges.php" class="delete-btn">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<div class="no-categories">No categories have been added yet</div>';
                    }
                    ?>
                </div>
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
            document.getElementById('search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = document.querySelectorAll('.category-card');

                cards.forEach(card => {
                    const categoryName = card.querySelector('.category-name').textContent.toLowerCase();

                    if (categoryName.includes(searchTerm)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
