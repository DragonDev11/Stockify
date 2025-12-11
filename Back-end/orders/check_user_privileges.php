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
            $orderID = null;
            $new_state = null;

            if ($_SERVER["REQUEST_METHOD"] === "POST"){
                var_dump($_POST); // array(2) { ["orderID"]=> string(1) "1" ["new_state"]=> string(15) "STATE_INITIATED" }
                $action = isset($_POST['action']) ? $_POST['action'] : null;
                $orderID = isset($_POST["orderID"]) ? $_POST["orderID"] : null;
                $new_state = isset($_POST["new_state"]) ? $_POST["new_state"] : null;
                echo "<br>".$_POST['action']." ".$orderID." ".$new_state; // null 1 null
                // wtf?
            }

            if ($role_id === '0'){
                switch ($action){
                    case "view_orders":
                        header("Location: show_orders.php");
                        exit();
                        break;
                    case "add_orders":
                        header("Location: create_order.php");
                        exit();
                        break;
                    case "modify_orders":
                        if ($new_state == null){
                            header("Location: modify_order.php?orderID=".urlencode($orderID));
                        }else{
                            header("Location: apply_modifications.php?orderID=".urlencode($orderID)."&new_state=".urlencode($new_state));
                        }
                        exit();
                        break;
                    case "delete_orders":
                        header("Location: delete_order.php?orderID=".urlencode($orderID));
                        exit();
                        break;
                    default:
                        header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown");
                        exit();
                        break;
                }
            }else{
                $allowed_actions = [
                    "view_orders",
                    "add_orders",
                    "modify_orders",
                    "delete_orders"
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
                            case "view_orders":
                                header("Location: show_orders.php");
                                exit();
                                break;
                            case "add_orders":
                                header("Location: create_order.php");
                                exit();
                                break;
                            case "modify_orders":
                                if ($new_state === null){
                                    header("Location: modify_order.php?orderID=".urlencode($orderID));
                                }else{
                                    header("Location: apply_modifications.php?orderID=".urlencode($orderID)."&new_state=".urlencode($new_state));
                                }
                                exit();
                                break;
                            case "delete_orders":
                                header("Location: delete_order.php?orderID=".urlencode($orderID));
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