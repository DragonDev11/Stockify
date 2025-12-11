<?php
    require("../../includes/db_connection.php");
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["quotationID"])){
        $ID = $_GET["quotationID"];

        $query = "SELECT ID FROM DEVIS_HEADER WHERE ID = :id";
        $request = $bd->prepare($query);
        $request->bindParam(":id", $ID, PDO::PARAM_INT);

        try{
            $request->execute();
        }catch (PDOException $e){
            echo $e->getMessage();
            exit();
        }

        $result = $request->fetch(PDO::FETCH_DEFAULT);

        if ($result){
            $query = "DELETE FROM DEVIS_HEADER WHERE ID = :id;";
            $request = $bd->prepare($query);
            $request->bindParam(":id", $ID, PDO::PARAM_INT);

            try{
                $request->execute();
                header("Location: show_quotations.php");
            }catch (PDOException $e){
                echo $e->getMessage();
                exit();
            }

        }
    }else{
        header("Location: show_quotations.php?error=wrong_request_method_or_parameters");
    }
?>