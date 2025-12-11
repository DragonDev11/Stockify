<?php
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])){
        $ID = $_GET['id'];

        require("../../includes/db_connection.php");

        try{
            $query = "SELECT NUMBER_OF_USERS FROM ROLES WHERE ROLE_ID = :id;";
            $request = $bd->prepare($query);
            $request->bindValue(":id", $ID);
            try{
                $request->execute();
            }catch(PDOException $e){
                echo $e->getMessage();
            }
            $result = $request->fetchColumn(0);
            if ($result == 0){
                $query = "DELETE FROM ROLES WHERE ROLE_ID = :id";
                $request = $bd->prepare($query);
                $request->bindValue(":id", $ID);
                try{
                    $request->execute();
                }catch(PDOException $e){
                    echo $e->getMessage();
                }
                header("Location: show_roles.php");
            }else{
                header("Location: show_roles.php?error=role_has_users");
            }
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }else{
        echo "something went wrong";
    }
?>