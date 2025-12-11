<?php
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])){
        $ID = $_GET['id'];

        $bd = new PDO('mysql:host=localhost;dbname=stockify_database;charset=utf8', 'root', '');
        $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try{
            $query = "DELETE FROM USERS WHERE ID = :id";
            $request = $bd->prepare($query);
            $request->bindValue(":id", $ID);
            try{
                $request->execute();
            }catch(PDOException $e){
                echo $e->getMessage();
            }
            header("Location: show_users.php");
        }catch (PDOException $e){
            echo $e->getMessage();
            exit();
        }
    }else{
        echo "something went wrong";
        exit();
    }
?>