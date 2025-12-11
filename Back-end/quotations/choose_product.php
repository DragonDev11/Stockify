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
    <link rel="stylesheet" href="../css/style_choose_products.css">

    <title>Product Selection - Stockify</title>
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

        /* =============== Products Container ============== */
        .products-container {
            margin: 20px;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .products-header h1 {
            color: #0ce48d;
            margin: 0;
            font-size: 1.8rem;
        }

        /* =============== Table Styles ============== */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
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

        /* =============== Form Elements ============== */
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

        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .select-checkbox {
            width: 20px;
            height: 20px;
        }

        /* =============== Form Actions ============== */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0ce48d;
            color: white;
        }

        .btn-primary:hover {
            background: #0abf7a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(12, 228, 141, 0.2);
        }

        /* =============== Disabled State ============== */
        .disabled-row {
            opacity: 0.6;
            background-color: #f9f9f9 !important;
        }

        .disabled-row:hover {
            background-color: #f9f9f9 !important;
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
            .products-container {
                margin: 10px;
                padding: 15px;
            }

            .sorting-form {
                flex-direction: column;
                align-items: flex-start;
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
                        <input type="text" id="product-search-input" placeholder="Search products by name or reference...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="product-search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Content ================== -->
            <div class="products-container">
                <div class="products-header">
                    <h1>Select Products</h1>
                </div>

                <p id="error-message"></p>

                <?php
                try {
                    require("../../includes/db_connection.php");

                    $requete = null;
                    $sell_type = null;
                    $client_name = null;
                    $category = null;
                    $ICE = null;

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
                    } elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['quotation']) && isset($_POST['category'])) {
                        $sell_type = $_SESSION['quotation']['sell_type'];
                        $client_name = $_SESSION['quotation']['client_name'];
                        $category = $_POST['category'];
                        $ICE = $_SESSION['quotation']['company_ICE'] ?? null;

                        $query = "SELECT * FROM PRODUCT WHERE CATEGORY_NAME LIKE :category";
                        $requete = $bd->prepare($query);
                        $requete->bindValue(':category', $category);
                        $requete->execute();
                    }

                    if (!isset($category)) {
                        echo "<div class='error-message'>No category selected. Please go back and select a category.</div>";
                    } else {
                        echo "<h2 style='margin-bottom: 20px; color: #555;'>Category: {$category}</h2>";

                        echo "
                            <form method='GET' action='choose_product.php' class='sorting-form'>
                                <input type='hidden' name='order_category' value='{$category}'>
                                <label>Order By:</label>
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
                                <button type='submit'>
                                    <ion-icon name='filter-outline'></ion-icon>
                                    Apply
                                </button>
                            </form>
                            ";

                        $products = $requete->fetchAll(PDO::FETCH_ASSOC);
                        $i = 0;

                        echo "
                            <form method='POST' action='quotation_summary.php'>
                                <input type='hidden' name='client_name' value='{$client_name}'>
                                <input type='hidden' name='company_ICE' value='{$ICE}'>
                                <table class='products-table' id='products-table'>
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Product Name</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Select Qty</th>
                                            <th>Image</th>
                                            <th>Select</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            ";

                        if ($products) {
                            foreach ($products as $product) {
                                $i++;
                                $min = ($product['QUANTITY'] > 0) ? 1 : 0;
                                $max = $product['QUANTITY'];
                                $value = $min;
                                $disabled = ($product['QUANTITY'] <= 0) ? '' : '';
                                $disabledClass = ($product['QUANTITY'] <= 0) ? 'disabled-row' : '';

                                $idField = (isset($_SESSION['quotation']['state']) && $_SESSION['quotation']['state'] == 'modifying_order') ?
                                    $product['REFERENCE'] : $product['ID'];

                                echo "
                                    <tr class='product-row' data-product-name='" . htmlspecialchars(strtolower($product['PRODUCT_NAME'])) . "' data-product-ref='" . htmlspecialchars(strtolower($product['REFERENCE'])) . "'>
                                        <td class='product-ref'>{$product['REFERENCE']}</td>
                                        <td class='product-name'>{$product['PRODUCT_NAME']}</td>
                                        <td>{$product['PRICE']} MAD</td>
                                        <td>{$product['QUANTITY']}</td>
                                        <td>
                                            <input type='number' class='quantity-input' name='quantity{$i}' min='{$min}' value='{$value}'>
                                        </td>
                                        <td>
                                    ";

                                if ($product["IMAGE"]) {
                                    $base64Image = base64_encode($product["IMAGE"]);
                                    echo "<img class='product-image' src='data:image/jpeg;base64,{$base64Image}' alt='{$product['PRODUCT_NAME']}'>";
                                } else {
                                    echo "<div class='no-image'><ion-icon name='image-outline'></ion-icon></div>";
                                }

                                echo "
                                        </td>
                                        <td>
                                            <input type='checkbox' class='select-checkbox' id='product{$i}' name='reference{$i}' value='{$idField}' {$disabled}>
                                        </td>
                                        <input type='hidden' name='price{$i}' value='{$product['PRICE']}'>
                                    </tr>
                                    ";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center; padding: 40px; color: #666;'>No products available in this category</td></tr>";
                        }

                        echo "
                                    </tbody>
                                </table>
                                <input type='hidden' name='sell_type' value='{$sell_type}'>
                                <input type='hidden' name='products' value='{$i}'>
                                <div class='form-actions'>
                                    <button type='submit' class='btn btn-primary'>
                                        <ion-icon name='arrow-forward-outline'></ion-icon>
                                        Continue
                                    </button>
                                </div>
                            </form>
                            ";
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
            document.getElementById('product-search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#products-table tbody tr.product-row');
                
                rows.forEach(row => {
                    const productName = row.getAttribute('data-product-name');
                    const productRef = row.getAttribute('data-product-ref');
                    
                    if (productName.includes(searchTerm) || productRef.includes(searchTerm)) {
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