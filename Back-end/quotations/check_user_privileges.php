<?php
    session_start();
    require("../../includes/db_connection.php");
    if (isset($_SESSION["user"]) && ($_SESSION["user"]["username"] !== "")){
        //var_dump($_SESSION);
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
            $quotationID = null;
            $new_state = null;

            if ($_SERVER["REQUEST_METHOD"] === "POST"){
                var_dump($_POST); // array(1) { ["quotationID"]=> string(1) "1"}
                $action = isset($_POST['action']) ? $_POST['action'] : null;
                $quotationID = isset($_POST["quotationID"]) ? $_POST["quotationID"] : null;
                echo "<br>".$_POST['action']." ".$quotationID; // null 1
            }

            if ($role_id === '0'){ // Super admin
                switch ($action){
                    case "view_quotations":
                        header("Location: show_quotations.php");
                        exit();
                        break;
                    case "add_quotations":
                        header("Location: create_quotation.php");
                        exit();
                        break;
                    case "delete_quotations":
                        header("Location: delete_quotation.php?quotationID=".urlencode($quotationID));
                        exit();
                        break;
                    case "confirm_quotations":
                        header("Location: confirm_quotation.php?quotationID=".urlencode($quotationID));
                        exit();
                        break;
                    default:
                        header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown_action");
                        exit();
                        break;
                }
            }else{
                $allowed_actions = [
                    "view_quotations",
                    "add_quotations",
                    "delete_quotations",
                    "confirm_quotations"
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

                    echo "<br>";
                    echo $action_perm;
                    echo "<br>";
                    echo $query;
                
                    if ($action_perm) {
                        switch ($action) {
                            case "view_quotations":
                                header("Location: show_quotations.php");
                                exit();
                                break;
                            case "add_quotations":
                                header("Location: create_quotation.php");
                                exit();
                                break;
                            case "delete_quotations":
                                header("Location: delete_quotation.php?quotationID=".urlencode($quotationID));
                                exit();
                                break;
                            case "confirm_quotations":
                                header("Location: confirm_quotation.php?quotationID=".urlencode($quotationID));
                                exit();
                                break;
                            default:
                                header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown_action");
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