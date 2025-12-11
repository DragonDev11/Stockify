<?php
    require("../../includes/db_connection.php");
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['client_id'])){
        $client_id = $_GET['client_id'];

        try{
            $query = "DELETE FROM CLIENT WHERE ID = :id";
            $request = $bd->prepare($query);
            $request->bindValue(":id", $client_id);
            try{
                $request->execute();
                header("Location: show_clients.php?message=Client+deleted+successfully");
            }catch(PDOException $e){
                echo $e->getMessage();
                exit();
            }
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }else{
        header("Location: show_clients.php?error=Wrong+request+method");
    }
?>