<?php
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../../../Front-end/categories/show_categories.css?v=<?php echo time(); ?>">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

        <title>Stockify</title>
    </head>

    <body>

        <header>
            <div class="face">
                <img class="logo" src="../../../Front-end/images/logo.jpeg" alt="Stockify">
                <a class="name"><h1>Choose category</h1></a>
            </div>
        </header>
        

        <div class="categories">

            <h1>Categories</h1>

            <div class="cat">
                <?php
                    if (isset($_SESSION['invoice'])){
                        var_dump($_SESSION);
                        $bd = new PDO('mysql:host=localhost;dbname=stockify_database;charset=utf8', 'root', '');
                        $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        $request = $bd->prepare("SELECT CATEGORYNAME, NUMBER_OF_PRODUCTS, IMAGE FROM CATEGORY;");

                        try{
                            $request->execute();
                        }catch (PDOException $e){
                            $e->getMessage();
                        }

                        $result = $request->fetchAll(PDO::FETCH_ASSOC);


                        $type = $_SESSION['invoice']['type'];
                        if (count($result)>0){
                            if ($type === 'Buy'){
                                if (isset($_SESSION['invoice']['id']) && isset($_SESSION['invoice']['supplier_name']) && isset($_SESSION['invoice']['company_name'])){
                                    $id = $_SESSION['invoice']['id'];
                                    $supplier_name = $_SESSION['invoice']['supplier_name'];
                                    $company_name = $_SESSION['invoice']['company_name'];
                                    // to complete later
                                }
                            }elseif ($type === 'Sell'){
                                if (isset($_SESSION['invoice']['client_name']) && isset($_SESSION['invoice']['sell_type'])){
                                    $client_name = $_SESSION['invoice']['client_name'];
                                    $sell_type = $_SESSION['invoice']['sell_type'];
                                    $ICE = null;
                                    if ($sell_type === 'Company'){
                                        $ICE = $_SESSION['invoice']['company_ICE'];
                                    }

                                    for ($i=0; $i<count($result); $i++){
                                        $cat_name = $result[$i]["CATEGORYNAME"];
                                        $num_products = $result[$i]["NUMBER_OF_PRODUCTS"];
                                        echo "
                                            <div class='cat".($i+1)."'>
                                                <a class='acat".($i+1)."'>
                                                    <h2>$cat_name</h2>
                                        ";
        
                                        $imageData = $result[$i]["IMAGE"];
        
                                        if ($imageData){
                                            $base64Image = base64_encode($imageData);
                                            echo "<img src='data:image/jpeg;base64,$base64Image' alt='Category Image' width='100px'>";
                                        }
        
                                        echo "
                                                    <p>{$num_products} Products</p>
                                                    <form method='POST' action='choose_product.php'>
                                                        <input type='hidden' name='category' value='{$cat_name}'>
                                        ";
                                        if ($num_products > 0){
                                            echo "<button type='submit'>Select</button>";
                                        }
                                        echo "
                                                    </form>
                                                </a>
                                            </div>
                                        ";
                                    }
                                }
                            }
                        }else{
                            echo "<p>No categories has been added</p>";
                        }
                    }else{
                        echo "<p>كيف وصلت إلى هنا؟؟</p>";
                    }
                ?>
            </div>
        </div>
    </body>
    
</html>