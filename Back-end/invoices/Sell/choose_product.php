<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="../../../Front-end/categories/show_category.css?v=<?php echo time(); ?>" >
</head>
<body>
    <header>
        <div class="face">

            <img class="logo" src="../../../Front-end/images/logo.jpeg" alt="Stockify">
            <a class="name"><h1>Category products</h1></a>

        </div>
    </header>

    
    <?php

        try {
            // Connect to the database
            $bd = new PDO('mysql:host=localhost;dbname=stockify_database;charset=utf8', 'root', '');
            $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $requete = null;
            $type = null;
            $sell_type = null;
            $client_name = null;
            $category = null;
            $ICE = null;


            // Fetch products from the database
            if ($_SERVER["REQUEST_METHOD"] === "GET"){
                if (isset($_GET['order_column']) && isset($_GET['order_type']) && isset($_GET['order_category'])){


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

                    echo "
                        <h1 align='center'>Category: {$orderCategory}</h1>  
                    ";

                    echo "
                        <form method='GET' action='choose_product.php'>
                            <input type='hidden' name='order_category' value='{$orderCategory}'>
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
                    ";
                }
                    
            }elseif ($_SERVER["REQUEST_METHOD"] === "POST"){
                if (isset($_SESSION['invoice']) && isset($_POST['category'])){
                    var_dump($_SESSION);
                    $type = $_SESSION['invoice']['type'];
                    if ($type === 'Sell'){
                        $sell_type = $_SESSION['invoice']['sell_type'];
                        $client_name = $_SESSION['invoice']['client_name'];
                        $category = $_POST['category'];
                        $ICE = null;

                        if (isset($_SESSION['invoice']['company_ICE'])){
                            $ICE = $_SESSION['invoice']['company_ICE'];
                        }

                        echo "  
                            <h1 align='center'>Category: {$category}</h1>
                        ";
                        echo "
                            <form method='GET' action='choose_product.php'>
                                <input type='hidden' name='order_category' value='{$category}'>
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
                        ";
                    }
                    $query = "SELECT * FROM PRODUCT WHERE CATEGORY_NAME LIKE :category";
                    $requete = $bd->prepare($query);
                    $requete->bindValue(':category', $category);
                    $requete->execute();
                }
            }
            $products = $requete->fetchAll(PDO::FETCH_ASSOC);

            echo "
            <form method='POST' action='invoice_summary.php'>
                <input type='hidden' name='client_name' value='{$client_name}'>
                <input type='hidden' name='company_ICE' value='{$ICE}'>
                <div class='products'>
            ";
            $i = 0;
            if ($products) {
                foreach ($products as $product) {
                    $i++;
                    $min = 0;
                    $max = 0;
                    $value = 0;
                    if ($product['QUANTITY'] > 0){
                        $min = 1;
                        $max = $product['QUANTITY'];
                        $value = $min;
                    }else{
                        $min = 0;
                        $max = 0;
                        $value = 0;
                    }
                    echo "
                        <div class='product'".$i."'>";
                    
                    $imageData = $product["IMAGE"];
        
                    if ($imageData){
                        $base64Image = base64_encode($imageData);
                        
                    }
                    
                    echo "
                        <div class='container'>

                            <div class= 'imgc'> <img class= 'img' src='data:image/jpeg;base64,$base64Image' alt='Product Image' ></div>

                            
                            <table border='2px'>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Check</th>
                                    </tr>
                                </thead
                                <tbody>
                                    <tr>
                                        <td>{$product['PRODUCT_NAME']}</td>
                                        <td>{$product['QUANTITY']}</td>
                                        <td><input id='pricei' type='number' name='price".$i."' min='0.00' value='{$product['PRICE']}'> MAD</td>
                                        <td><input type='number' name='quantity".$i."' min='{$min}' max='{$max}' value='{$value}'></td>
                                        <td><input type='checkbox' name='reference".$i."' value='{$product['REF_P']}'></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                ";
                }
            }else{
                echo "
                    <h2>No products in this category</h2>
                ";
            }
            echo "
                </div>
                <input type='hidden' name='type' value='{$type}'>
                <input type='hidden' name='sell_type' value='{$sell_type}'>
                <input type='hidden' name='products' value='{$i}'>
                <button type='submit'>Next</button>
            </form>
            ";
        } catch (PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    ?>
</body>
</html>
