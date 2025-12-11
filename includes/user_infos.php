<?php
    require("db_connection.php");

    $username = null;
    $last_name = null;
    $first_name = null;
    if (isset($_SESSION["user"]["username"])){
        $username = $_SESSION["user"]["username"];

        if ($username == ""){
            header("Location: /Stockify/Front-end/login/index.php?error=not_logged_in");
        }

        $query = "SELECT FIRST_NAME,LAST_NAME FROM USERS WHERE USERNAME = :username;";
        $request = $bd->prepare($query);

        $request->bindValue(":username", $username);
        try{
            $request->execute();
        }catch (PDOException $e){
            echo "<br><pre>ERROR: ".$e->getMessage()."</pre><br>";
            exit();
        }

        $result = $request->fetch(PDO::FETCH_ASSOC);

        if ($result){
            $last_name = $result["LAST_NAME"];
            $first_name = $result["FIRST_NAME"];
        }
    }else{
        header("Location: /Stockify/Front-end/login/index.php?error=not_logged_in");
    }
?>