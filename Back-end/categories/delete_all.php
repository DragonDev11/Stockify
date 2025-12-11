<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST"){
        if (isset($_POST['category'])){
            $category = $_POST['category'];
        }elseif (isset($_POST['order_category'])){
            $category = $_POST['order_category'];
        }

        $bd = new PDO('mysql:host=localhost;dbname=stockify_database;charset=utf8', 'root', '');
        $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try{
            $query = "DELETE FROM PRODUCT WHERE CATEGORY_NAME = :category; UPDATE CATEGORY SET NUMBER_OF_PRODUCTS = 0 WHERE CATEGORY_NAME = :category;";
            $request = $bd->prepare($query);
            $request->bindValue(":category", $category);
            try{
                $request->execute();
            }catch(PDOException $e){
                echo $e->getMessage();
            }

            header("Location: ../categories/show_category.php?category=$category");
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }
?>