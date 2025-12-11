<?php
    session_start();

    if (isset($_SESSION["user"])){
        $username = $_SESSION["user"]["username"];
        require("../../includes/db_connection.php");

        $query = "SELECT USER_ROLE_ID FROM USERS WHERE USERNAME LIKE :username;";
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
            $action = null;
            $category = null;

            if ($_SERVER["REQUEST_METHOD"] === "POST"){
                $action = isset($_POST["action"]) ? $_POST["action"] : null;
                $category = isset($_POST["category"]) ? $_POST["category"] : null;
            }

            if ($role_id === '0'){
                switch ($action){
                    case "view_categories":
                        header("Location: show_categories.php");
                        exit();
                        break;
                    case "add_categories":
                        header("Location: add_category.php");
                        exit();
                        break;
                    case "delete_categories":
                        header("Location: delete_category.php?category=".urlencode($category));
                        exit();
                        break;
                    case "modify_categories":
                        header("Location: modify_category.php?category=".urlencode($category));
                        exit();
                        break;
                    default:
                        header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown");
                        exit();
                        break;
                }
            }else{
                $allowed_actions = [
                    "view_categories",
                    "add_categories",
                    "modify_categories",
                    "delete_categories"
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
                            case "view_categories":
                                header("Location: show_categories.php");
                                exit();
                            case "add_categories":
                                header("Location: add_category.php");
                                exit();
                            case "delete_categories":
                                header("Location: delete_category.php?category=".urlencode($category));
                                exit();
                            case "modify_categories":
                                header("Location: modify_category.php?category=".urlencode($category));
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
    }else{
        header("Location: /Stockify/Front-end/login/index.php?error=not_logged_in");
        exit();
    }
?>