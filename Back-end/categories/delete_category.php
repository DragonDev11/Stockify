<?php
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["category"])){
        require("../../includes/db_connection.php");

        $category = $_GET["category"];

        $request = $bd->prepare("SELECT NUMBER_OF_PRODUCTS FROM CATEGORY WHERE CATEGORYNAME = :c_name;");
        $request->bindValue(":c_name", $category);
        try{
            $request->execute();
        }catch (PDOException $e){
            echo $e->getMessage();
        }
        $result = $request->fetch(PDO::FETCH_DEFAULT);
        if (count($result) > 0){
            if ($result[0] > 0){
                echo "
                    <p>Cannot delete category, make sure the category is empty. Do you want to empty '{$category}' category?</p>
                    <form method='POST' action='delete_all.php'>
                        <input type='hidden' name='category' value={$category}>
                        <button type='submit'>Yes</button>
                    </form>
                    <a href='show_categories.php'>
                        <button>Cancel</button>
                    </a>
                ";
            }else{
                $request = $bd->prepare("DELETE FROM CATEGORY WHERE CATEGORYNAME = :c_name;");
                $request->bindValue(":c_name", $category);
                try{
                    $request->execute();
                }catch (PDOException $e){
                    echo $e->getMessage();
                }
                header("Location: show_categories.php");
                exit();
            }
        }

    }
?>