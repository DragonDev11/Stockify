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

    <script src="../../main.js"></script>

    <title>Product List</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style_choose_products.css">
    <style>
        .products-container {
            margin: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .filter-form {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .filter-form label {
            font-weight: 500;
            color: #333;
        }

        .filter-form select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .filter-form button {
            padding: 8px 16px;
            background: #0ce48d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .products-table {
            width: auto;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .products-table th {
            background: #0ce48d;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .products-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .products-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .products-table tr:hover {
            background: #f0f0f0;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 4px;
        }

        .product-quantity {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .product-checkbox {
            transform: scale(1.3);
        }

        .next-btn {
            display: block;
            margin: 20px auto 0;
            padding: 12px 24px;
            background: #0ce48d;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .next-btn:hover {
            background: #0abf7a;
        }

        .page-title {
            color: #0ce48d;
            text-align: center;
            margin-bottom: 20px;
        }

        .no-products {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
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
                        <input type="text" id="search-input" placeholder="Search products...">
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
                    $requete = null;
                    $sell_type = null;
                    $client_name = null;
                    $category = null;
                    $ICE = null;
                    $orderCategory = null;

                    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['order_column']) && isset($_GET['order_type']) && isset($_GET['order_category'])) {
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
                        $orderCategory = $_GET['order_category'];

                        $query = "SELECT * FROM PRODUCT WHERE CATEGORY_NAME LIKE :orderCategory ORDER BY $orderColumn $orderType;";
                        $requete = $bd->prepare($query);
                        $requete->bindValue(":orderCategory", $orderCategory);
                        $requete->execute();
                        $category = $orderCategory;
                    } elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['order']) && isset($_POST['category'])) {
                        $sell_type = $_SESSION['order']['sell_type'];
                        $client_name = $_SESSION['order']['client_name'];
                        $category = $_POST['category'];
                        $ICE = $_SESSION['order']['company_ICE'] ?? null;

                        $query = "SELECT * FROM PRODUCT WHERE CATEGORY_NAME LIKE :category";
                        $requete = $bd->prepare($query);
                        $requete->bindValue(':category', $category);
                        $requete->execute();
                    }

                    $products = $requete ? $requete->fetchAll(PDO::FETCH_ASSOC) : [];
                ?>

                    <h1 class="page-title">Category: <?php echo htmlspecialchars($category); ?></h1>

                    <form method='GET' action='choose_product.php' class="filter-form">
                        <input type='hidden' name='order_category' value='<?php echo htmlspecialchars($category); ?>'>
                        <label>Order By</label>
                        <select name='order_column'>
                            <option>Reference</option>
                            <option>Name</option>
                            <option>Price</option>
                            <option>Quantity</option>
                        </select>
                        <select name='order_type'>
                            <option>Ascending</option>
                            <option>Descending</option>
                        </select>
                        <button type='submit'>Apply</button>
                    </form>

                    <form class="table1" method='POST' action='order_summary.php'>
                        <input type='hidden' name='client_name' value='<?php echo htmlspecialchars($client_name ?? ''); ?>'>
                        <input type='hidden' name='company_ICE' value='<?php echo htmlspecialchars($ICE ?? ''); ?>'>
                        <input type='hidden' name='sell_type' value='<?php echo htmlspecialchars($sell_type ?? ''); ?>'>

                        <?php if (count($products) > 0): ?>
                            <table class="products-table" id="products-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Reference</th>
                                        <th>Product Name</th>
                                        <th>Price (MAD)</th>
                                        <th>Available Quantity</th>
                                        <th>Order Quantity</th>
                                        <th>Select</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $i => $product):
                                        $min = $product['QUANTITY'] > 0 ? 1 : 0;
                                        $max = $product['QUANTITY'] > 0 ? $product['QUANTITY'] : 0;
                                        $value = $min;
                                        $isModifying = isset($_SESSION['order']['state']) && $_SESSION['order']['state'] == 'modifying_order';
                                    ?>
                                        <tr class="product-row">
                                            <td>
                                                <?php if ($product["IMAGE"]):
                                                    $base64Image = base64_encode($product["IMAGE"]);
                                                ?>
                                                    <img class="product-image" src="data:image/jpeg;base64,<?php echo $base64Image; ?>" alt="<?php echo htmlspecialchars($product['PRODUCT_NAME']); ?>">
                                                <?php else: ?>
                                                    <div class="no-image">No Image</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="product-ref"><?php echo htmlspecialchars($product['REFERENCE']); ?></td>
                                            <td class="product-name"><?php echo htmlspecialchars($product['PRODUCT_NAME']); ?></td>
                                            <td>
                                                <input type='number' name='price<?php echo $i + 1; ?>' value='<?php echo htmlspecialchars($product['PRICE']); ?>' readonly>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['QUANTITY']); ?></td>
                                            <td>
                                                <input type='number' class="product-quantity" name='quantity<?php echo $i + 1; ?>' min='<?php echo $min; ?>' max='<?php echo $max; ?>' value='<?php echo $value; ?>' <?php echo $max == 0 ? 'disabled' : ''; ?>>
                                            </td>
                                            <td>
                                                <input type='checkbox' class="product-checkbox" name='reference<?php echo $i + 1; ?>' value='<?php echo $isModifying ? htmlspecialchars($product['REFERENCE']) : htmlspecialchars($product['ID']); ?>' <?php echo $max == 0 ? 'disabled' : ''; ?>>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <input type='hidden' name='products' value='<?php echo count($products); ?>'>
                            <button type='submit' class="next-btn">Next</button>
                        <?php else: ?>
                            <div class="no-products">
                                <h2>No products in this category</h2>
                            </div>
                        <?php endif; ?>
                    </form>

                <?php } catch (PDOException $e) { ?>
                    <div class="no-products">
                        <h2>Error: <?php echo htmlspecialchars($e->getMessage()); ?></h2>
                    </div>
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
            
            // Product search functionality
            document.getElementById('search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#products-table tbody tr.product-row');
                
                rows.forEach(row => {
                    const productRef = row.querySelector('.product-ref').textContent.toLowerCase();
                    const productName = row.querySelector('.product-name').textContent.toLowerCase();
                    
                    if (productRef.includes(searchTerm) || productName.includes(searchTerm)) {
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