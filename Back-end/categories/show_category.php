<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List | Stockify</title>

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
        .products-container {
            padding: 30px;
            margin: 20px;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            color: #0ce48d;
            margin-bottom: 20px;
        }

        .add-product-btn {
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

        .add-product-btn:hover {
            background: #09c77d;
        }

        .sorting-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sorting-form label {
            font-weight: 500;
            color: #555;
        }

        .sorting-form select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .sorting-form button {
            background: #0ce48d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .products-table th {
            background: #0ce48d;
            color: white;
            padding: 15px;
            text-align: left;
        }

        .products-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .products-table tr:hover {
            background: #f9f9f9;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .no-image {
            width: 80px;
            height: 80px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #999;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .modify-btn {
            background: #3498db;
            color: white;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .no-products {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1rem;
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
                        <input type="text" id="search-input" placeholder="Search products by reference, name or category...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Products Content ================== -->
            <div class="products-container">
                <?php
                try {
                    // Connect to the database
                    require("../../includes/db_connection.php");

                    $requete = null;
                    $categoryName = "";

                    // Fetch products from the database
                    if ($_SERVER["REQUEST_METHOD"] === "GET") {
                        if (isset($_GET['order_column']) && isset($_GET['order_type']) && isset($_GET['order_category'])) {

                            $columnsMap = [
                                "Reference" => "REFERENCE",
                                "Name" => "PRODUCT_NAME",
                                "Price" => "PRICE",
                                "Quantity" => "QUANTITY"
                            ];

                            $typesMap = [
                                "Ascending" => "ASC",
                                "Descending" => "DESC"
                            ];

                            $orderColumn = $columnsMap[$_GET['order_column']];
                            $orderType = $typesMap[$_GET['order_type']];
                            $categoryName = $_GET['order_category'];

                            $query = "SELECT * FROM PRODUCT WHERE CATEGORY_NAME LIKE :orderCategory ORDER BY $orderColumn $orderType;";
                            $requete = $bd->prepare($query);
                            $requete->bindValue(":orderCategory", $categoryName);
                            $requete->execute();
                        }
                    } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
                        if (isset($_POST["category"])) {
                            $categoryName = $_POST['category'];
                            $query = "SELECT * FROM PRODUCT WHERE CATEGORY_NAME LIKE :category";
                            $requete = $bd->prepare($query);
                            $requete->bindValue(':category', $categoryName);
                            $requete->execute();
                        }
                    }

                    if ($categoryName) {
                        echo "<h1 class='page-title'>Category: {$categoryName}</h1>";

                        // Add Product Button
                        echo "<div class='products-header'>";
                        echo "<form method='POST' class='add-product-form'>";
                        echo "<input type='hidden' name='category' value='{$categoryName}'>";
                        echo "<input type='hidden' name='action' value='add_products'>";
                        echo "<button type='submit' formaction='../products/check_user_privileges.php' class='add-product-btn'>";
                        echo "<ion-icon name='add-outline'></ion-icon> Add Product";
                        echo "</button>";
                        echo "</form>";
                        echo "</div>";

                        // Sorting Form
                        echo "<form method='GET' action='show_category.php' class='sorting-form'>";
                        echo "<input type='hidden' name='order_category' value='{$categoryName}'>";
                        echo "<label>Sort By:</label>";
                        echo "<select name='order_column'>";
                        echo "<option value='Reference'>Reference</option>";
                        echo "<option value='Name'>Name</option>";
                        echo "<option value='Price'>Price</option>";
                        echo "<option value='Quantity'>Quantity</option>";
                        echo "</select>";
                        echo "<select name='order_type'>";
                        echo "<option value='Ascending'>Ascending</option>";
                        echo "<option value='Descending'>Descending</option>";
                        echo "</select>";
                        echo "<button type='submit'>Apply</button>";
                        echo "</form>";
                    }

                    if ($requete) {
                        $products = $requete->fetchAll(PDO::FETCH_ASSOC);

                        if (count($products) > 0) {
                            echo "<table class='products-table' id='products-table'>";
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>Reference</th>";
                            echo "<th>Product Name</th>";
                            echo "<th>Price</th>";
                            echo "<th>Quantity</th>";
                            echo "<th>Category</th>";
                            echo "<th>Image</th>";
                            echo "<th colspan='2'>Actions</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";

                            foreach ($products as $product) {
                                echo "<tr class='product-row'>";
                                echo "<td class='product-reference'>{$product['REFERENCE']}</td>";
                                echo "<td class='product-name'>{$product['PRODUCT_NAME']}</td>";
                                echo "<td>{$product['PRICE']} MAD</td>";
                                echo "<td>{$product['QUANTITY']}</td>";
                                echo "<td class='product-category'>{$product['CATEGORY_NAME']}</td>";

                                // Product Image
                                $imageData = $product['IMAGE'];
                                if ($imageData) {
                                    $base64Image = base64_encode($imageData);
                                    echo "<td><img class='product-image' src='data:image/jpeg;base64,{$base64Image}' alt='Product Image'></td>";
                                } else {
                                    echo "<td><div class='no-image'><ion-icon name='image-outline'></ion-icon></div></td>";
                                }

                                // Action Buttons
                                echo "<td>";
                                echo "<form method='POST'>";
                                echo "<input type='hidden' name='reference' value='{$product['ID']}'>";
                                echo "<input type='hidden' name='from_show_category' value='true'>";
                                echo "<input type='hidden' name='action' value='modify_products'>";
                                echo "<button type='submit' formaction='../products/check_user_privileges.php' class='action-btn modify-btn'>Modify</button>";
                                echo "</form>";
                                echo "</td>";

                                echo "<td>";
                                echo "<form method='POST'>";
                                echo "<input type='hidden' name='reference' value='{$product['ID']}'>";
                                echo "<input type='hidden' name='from_show_category' value='true'>";
                                echo "<input type='hidden' name='action' value='delete_products'>";
                                echo "<button type='submit' formaction='../products/check_user_privileges.php' class='action-btn delete-btn'>Delete</button>";
                                echo "</form>";
                                echo "</td>";

                                echo "</tr>";
                            }

                            echo "</tbody>";
                            echo "</table>";
                        } else {
                            echo "<div class='no-products'>No products found in this category</div>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<div class='error-message'>Error: " . $e->getMessage() . "</div>";
                }
                ?>
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
            
            // Product search functionality
            document.getElementById('search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#products-table tbody tr.product-row');
                
                rows.forEach(row => {
                    const productReference = row.querySelector('.product-reference').textContent.toLowerCase();
                    const productName = row.querySelector('.product-name').textContent.toLowerCase();
                    const productCategory = row.querySelector('.product-category').textContent.toLowerCase();
                    
                    if (productReference.includes(searchTerm) || productName.includes(searchTerm) || productCategory.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
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