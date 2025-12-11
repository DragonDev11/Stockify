<?php
    session_start();
    require("../../includes/db_connection.php");
    if (isset($_SESSION["user"])){
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
                $invoiceID = isset($_POST["invoiceID"]) ? $_POST["invoiceID"] : null;
                $invoiceType = isset($_POST["invoiceType"]) ? $_POST["invoiceType"] : null;
                $redirect_url = isset($_POST["redirection_url"]) ? $_POST["redirection_url"] : null;
            }

            echo $redirect_url;

            if ($role_id === '0'){
                switch ($action){
                    case "view_invoices":
                        header("Location: $redirect_url");
                        exit();
                        break;
                    case "add_invoices":
                        header("Location: $redirect_url");
                        exit();
                        break;
                    case "delete_invoices":
                        header("Location: delete_invoice.php?invoiceID=".urlencode($invoiceID)."&invoiceType=".urlencode($invoiceType));
                        exit();
                        break;
                    default:
                        header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown");
                        exit();
                        break;
                }
            }else{
                $allowed_actions = [
                    "view_invoices",
                    "add_invoices",
                    "modify_invoices",
                    "delete_invoices"
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
                            case "view_invoices":
                                header("Location: $redirect_url");
                                exit();
                                break;
                            case "add_invoices":
                                header("Location: $redirect_url");
                                exit();
                                break;
                            case "delete_invoices":
                                header("Location: delete_invoice.php?invoiceID=".urlencode($invoiceID)."&invoiceType=".urlencode($invoiceType));
                                exit();
                                break;
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