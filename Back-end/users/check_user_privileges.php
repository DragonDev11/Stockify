<?php
    session_start();

    if (isset($_SESSION["user"]["username"])){
        if ($_SESSION["user"]["username"] !== ""){
            require("../../includes/db_connection.php");
            $username = $_SESSION["user"]["username"];

            $action = null;
            $reference = null;

            if ($_SERVER["REQUEST_METHOD"] === "POST"){
                $action = isset($_POST["action"]) ? $_POST["action"] : null;
                $userID = isset($_POST["id"]) ? $_POST["id"] : null;
            }

            $query = "SELECT USER_ROLE_ID FROM USERS WHERE USERNAME = :username;";
            $request = $bd->prepare($query);

            $request->bindValue(":username", $username);

            try{
                $request->execute();
            }catch (PDOException $e){
                echo $e->getMessage();
            }

            $result = $request->fetch(PDO::FETCH_ASSOC);

            if ($result){
                $role_id = $result["USER_ROLE_ID"];
                if ($role_id === '0'){
                    switch ($action){
                        case "view_users":
                            header("Location: show_users.php");
                            exit();
                        case "delete_users":
                            header("Location: delete_user.php?id=".urlencode($userID));
                            exit();
                        case "modify_users":
                            header("Location: modify_user.php?id=".urlencode($userID));
                            exit();
                        default:
                            header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown");
                            exit();
                    }
                }else{
                    $allowed_actions = [
                        "view_users",
                        "add_users",
                        "modify_users",
                        "delete_users"
                    ];
                    
                    if (in_array($action, $allowed_actions)) {
                        $query = "SELECT $action FROM ROLES WHERE ROLE_ID = :role_id;";
                        $request = $bd->prepare($query);
                        $request->bindValue(":role_id", $role_id);
                    
                        try {
                            $request->execute();
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                        }
                    
                        $action_perm = $request->fetchColumn(0);
                    
                        if ($action_perm) {
                            switch ($action) {
                                case "view_users":
                                    header("Location: show_users.php");
                                    exit();
                                case "add_users":
                                    header("Location: add_users.php");
                                    exit();
                                case "delete_users":
                                    header("Location: delete_user.php?id=" . urlencode($userID));
                                    exit();
                                case "modify_users":
                                    header("Location: modify_user.php?id=" . urlencode($userID));
                                    exit();
                            }
                        } else {
                            header("Location: /Stockify/Front-end/dashboard/index.php?error=permission_denied");
                            exit();
                        }
                    } else {
                        header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown_action");
                        exit();
                    }
                }
            }else{
                header("Location: /Stockify/Front-end/login/index.php?error=username_not_found");
                exit();
            }
            echo "haaa :D";
        }else{
            header("Location: /Stockify/Front-end/login/index.php?error=not_logged_in");
            exit();
        }
    }else{
        header("Location: /Stockify/Front-end/login/index.php?error=not_logged_in");
        exit();
    }
?>