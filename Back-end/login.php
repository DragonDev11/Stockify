<?php
    session_start();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Set up PDO connection
        require("../includes/db_connection.php");
            
        $requete = $bd->prepare('SELECT USER_ID,USER_PASSWORD FROM USERS WHERE USERNAME = :username;');
        $requete->bindValue(':username', $username);
        $requete->execute();

        $userInfo = $requete->fetch(PDO::FETCH_ASSOC);
        
        if ($userInfo){
            
            if (!str_starts_with($userInfo["USER_PASSWORD"], "$2y$10$")){
                $password_verify = ($password == $userInfo["USER_PASSWORD"]);
            }else{
                $password_verify = password_verify($password, $userInfo["USER_PASSWORD"]);
            }
            echo $password_verify." ".$username;
            var_dump($userInfo);
            if ($password_verify) {
                $_SESSION["user"] = ["username" => $username];
                header("Location: ../Front-end/dashboard/index.php");
                exit();
            }else{
                header("Location: ../Front-end/login/index.php?error=wrong_password");
                exit();
            }
        }else{
            header("Location: ../Front-end/login/index.php?error=username_not_found");
            exit();
        }
    }
?>