<?php
    session_start();
    require("../../includes/db_connection.php");
    if (isset($_SESSION["user"]) && ($_SESSION["user"]["username"] !== "")){
        var_dump($_SESSION);
        $username = $_SESSION["user"]["username"];

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
            $reference = null;

            if ($_SERVER["REQUEST_METHOD"] === "POST"){
                $action = isset($_POST["action"]) ? $_POST["action"] : null;
                $reference = isset($_POST["reference"]) ? $_POST["reference"] : null;
            }

            if ($role_id === '0'){
                switch ($action){
                    case "view_products":
                        header("Location: show_products.php");
                        exit();
                        break;
                    case "add_products":
                        header("Location: add_products.php");
                        exit();
                        break;
                    case "delete_products":
                        header("Location: delete_product.php?reference=".urlencode($reference));
                        exit();
                        break;
                    case "modify_products":
                        header("Location: modify_product.php?reference=".urlencode($reference));
                        exit();
                        break;
                    default:
                        header("Location: ../../Front-end/dashboard/dashboard.html?error=unknown");
                        exit();
                        break;
                }
            }else{
                $allowed_actions = [
                    "view_products",
                    "add_products",
                    "modify_products",
                    "delete_products"
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
                            case "view_products":
                                header("Location: show_products.php");
                                exit();
                            case "add_products":
                                header("Location: add_products.php");
                                exit();
                            case "delete_products":
                                header("Location: delete_product.php?reference=" . urlencode($reference));
                                exit();
                            case "modify_products":
                                header("Location: modify_product.php?reference=" . urlencode($reference));
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