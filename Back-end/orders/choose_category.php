<?php
session_start();
require("../../includes/db_connection.php");
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

    <title>Choose Category</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 60px);
            padding: 20px;
        }

        .categories-container {
            width: 100%;
            max-width: 1200px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .categories-container h1 {
            color: #0ce48d;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        .category-card {
            background: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
            padding: 20px;
            border: 1px solid #e0e0e0;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .category-card h2 {
            color: #333;
            margin: 15px 0 10px;
            font-size: 20px;
        }

        .category-card img {
            max-width: 100%;
            height: 150px;
            object-fit: contain;
            border-radius: 4px;
        }

        .category-card p {
            color: #666;
            margin: 10px 0 15px;
            font-size: 14px;
        }

        .category-card button {
            background: #0ce48d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .category-card button:hover {
            background: #0abf7a;
        }

        .no-categories {
            text-align: center;
            color: #666;
            font-size: 18px;
            padding: 40px 0;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            justify-items: center;
        }

        .categories-grid:has(> :only-child) {
            max-width: 250px;
            margin: 0 auto;
        }

        .category-card {
            width: 100%;
            max-width: 250px;
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
        
        #error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
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
                        <input type="text" id="search-input" placeholder="Search categories...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Categories Content ================== -->
            <div class="main-content">
                <div class="categories-container">
                    <h1>Choose Category</h1>

                    <?php if (isset($_SESSION['order'])) { ?>
                        <?php
                        $request = $bd->prepare("SELECT CATEGORYNAME, NUMBER_OF_PRODUCTS, IMAGE FROM CATEGORY");
                        try {
                            $request->execute();
                            $result = $request->fetchAll(PDO::FETCH_ASSOC);

                            if (count($result) > 0 && isset($_SESSION['order']['client_name']) && isset($_SESSION['order']['sell_type'])) {
                                $client_name = $_SESSION['order']['client_name'];
                                $sell_type = $_SESSION['order']['sell_type'];
                                $ICE = ($sell_type === 'Company') ? $_SESSION['order']['company_ICE'] : null;
                        ?>
                                <div class="categories-grid" id="categories-grid">
                                    <?php foreach ($result as $index => $category) {
                                        $cat_name = htmlspecialchars($category["CATEGORYNAME"]);
                                        $num_products = htmlspecialchars($category["NUMBER_OF_PRODUCTS"]);
                                    ?>
                                        <div class="category-card">
                                            <h2 class="category-name"><?php echo $cat_name; ?></h2>
                                            <?php if ($category["IMAGE"]) {
                                                $base64Image = base64_encode($category["IMAGE"]);
                                            ?>
                                                <img src="data:image/jpeg;base64,<?php echo $base64Image; ?>" alt="Category Image">
                                            <?php } ?>
                                            <p><?php echo $num_products; ?> Products</p>
                                            <?php if ($num_products > 0) { ?>
                                                <form method='POST' action='choose_product.php'>
                                                    <input type='hidden' name='category' value='<?php echo $cat_name; ?>'>
                                                    <button type='submit'>Select</button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <p class="no-categories">No categories have been added</p>
                            <?php }
                        } catch (PDOException $e) { ?>
                            <p class="no-categories">Error loading categories: <?php echo htmlspecialchars($e->getMessage()); ?></p>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="no-categories">Please start an order first</p>
                    <?php } ?>
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
                    const cards = document.querySelectorAll('#categories-grid .category-card');
                    
                    cards.forEach(card => {
                        const categoryName = card.querySelector('.category-name').textContent.toLowerCase();
                        
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