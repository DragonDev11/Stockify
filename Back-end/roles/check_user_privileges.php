<pre>
<?php
    session_start();

    if (isset($_SESSION["user"]["username"])){
        require("../../includes/db_connection.php");
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
            if ($role_id === '0'){
                if ($_SERVER["REQUEST_METHOD"] === "POST"){
                    $action = isset($_POST["action"]) ? $_POST["action"] : null;
                    $ID = isset($_POST["id"]) ? $_POST["id"] : null;
                    switch ($action){
                        case "view_roles":
                            header("Location: show_roles.php");
                            exit();
                        case "add_roles":
                            header("Location: add_roles.php?id=".urlencode($ID));
                            exit();
                        case "delete_roles":
                            header("Location: delete_role.php?id=".urlencode($ID));
                            exit();
                        case "modify_roles":
                            header("Location: modify_role.php?id=".urlencode($ID));
                            exit();
                        default:
                            header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown");
                            exit();
                    }
                }
            }else{
                if ($_SERVER["REQUEST_METHOD"] === "POST"){
                    $action = isset($_POST["action"]) ? $_POST["action"] : null;
                    $ID = isset($_POST["id"]) ? $_POST["id"] : null;

                    $allowed_actions = [
                        "view_roles",
                        "add_roles",
                        "modify_roles",
                        "delete_roles"
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

                        var_dump($action_perm);

                        if ($action_perm){

                            switch ($action){
                                case "view_roles":
                                    header("Location: show_roles.php");
                                    exit();
                                case "delete_roles":
                                    header("Location: delete_role.php?id=".urlencode($ID));
                                    exit();
                                case "add_roles":
                                    header("Location: add_roles.php");
                                    exit();
                                case "modify_roles":
                                    header("Location: modify_role.php?id=".urlencode($ID));
                                    exit();
                                default:
                                    header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown");
                                    exit();
                            }
                        }else{
                            header("Location: show_roles.php?error=permission_denied");
                        }
                    }else{
                        header("Location: /Stockify/Front-end/dashboard/index.php?error=unknown_action");
                        exit();
                    }
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