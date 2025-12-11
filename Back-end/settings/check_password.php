<?php
    session_start();

    if (isset($_SESSION["user"]["username"])){
        $username = $_SESSION["user"]["username"];
        if ($_SERVER["REQUEST_METHOD"] == "POST"){
            require("../../includes/db_connection.php");
            $password = $_POST["password"];
            $query = "SELECT USER_ID,USER_PASSWORD FROM USERS WHERE USERNAME = :username;";
            $request = $bd->prepare($query);
            $request->bindValue(":username", $username);

            try{
                $request->execute();
            }catch (PDOException $e){
                echo $e->getMessage();
                exit();
            }

            $result = $request->fetch(PDO::FETCH_ASSOC);
            if ($result){
                if (password_get_info($result["USER_PASSWORD"]) == 0){
                    $password_verify = ($password == $result["USER_PASSWORD"]);
                }else{
                    $password_verify = password_verify($password, $result["USER_PASSWORD"]);
                }
                if ($password_verify){
                    $action = $_POST["action"];
                    switch ($action){
                        case "import":
                            if (isset($_FILES["zip_file"]) && $_FILES["zip_file"]["error"] == 0){
                                $tmpFileName = $_FILES["zip_file"]["tmp_name"];
                                $originalName = $_FILES["zip_file"]["name"];
                                $uploadDir = __DIR__ . "/../../uploads/"; // Adjust based on your script location
                                if (!is_dir($uploadDir)) {
                                    mkdir($uploadDir, 0777, true); // Create it if it doesn't exist
                                }

                                $destination = $uploadDir . $originalName;

                                if (!move_uploaded_file($tmpFileName, $destination)) {
                                    header("Location: /Stockify/Front-end/dashboard/settings.php?error=Could+not+upload+the+zip+file+:(");
                                    exit();
                                }

                            }
                            header("Location: import.php?zip_name=".urlencode($originalName));
                            break;
                        case "export":
                            header("Location: export.php");
                            break;
                        case "empty":
                            header("Location: empty.php");
                            break;
                        default:
                            header("Location: /Stockify/Front-end/dashboard/settings.php?error=unknown+action");
                            break;
                    }
                    exit();
                }else{
                    header("Location: /Stockify/Front-end/login/index.php?error=wrong+password+,+please+login+again");
                    exit();
                }
            }
        }
    }else{
        header("Location: /Stockify/Front-end/login/index.php?error=not_logged_in");
        exit();
    }
?>