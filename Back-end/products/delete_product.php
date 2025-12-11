<?php
    require("../../includes/db_connection.php");
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['reference'])){
        $refrence = $_GET['reference'];

        $request = $bd->prepare("SELECT QUANTITY,CATEGORY_NAME FROM PRODUCT WHERE ID = :ref");
        $request->bindValue(":ref", $refrence);
        $request->execute();
        $result = $request->fetch(PDO::FETCH_ASSOC);

        $quantity = $result["QUANTITY"];
        $cat = $result["CATEGORY_NAME"];

        $query = "UPDATE CATEGORY SET NUMBER_OF_PRODUCTS = (NUMBER_OF_PRODUCTS - 1) WHERE CATEGORYNAME = :p_category";
        $request = $bd->prepare($query);
        $request->bindValue(":p_category", $cat);
        try{
            $request->execute();
            $query = "DELETE FROM PRODUCT WHERE ID = :ref";
            $request = $bd->prepare($query);
            $request->bindValue(":ref", $refrence);
            try{
                $request->execute();
            }catch(PDOException $e){
                echo $e->getMessage();
            }

            if (isset($_GET['from_show_category'])){
                if ($_GET['from_show_category'] != ''){
                    header("Location: ../categories/show_category.php?category={$cat}");
                    exit;
                }
            }else{
                header("Location: show_products.php");
            }
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }else{
        echo "cannot get the reference";
    }
?>