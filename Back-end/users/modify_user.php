<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Modify User</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .user-management {
            padding: 20px;
            margin: 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
        }

        .user-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .user-form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            color: var(--black1);
        }

        .user-form input[type="text"],
        .user-form input[type="email"],
        .user-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .user-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            background-color: #0ce48d;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            width: 100%;
        }

        .user-form button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .error {
            color: red;
            text-align: center;
            margin: 10px 0;
        }

        /* Search bar styles */


        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }

        .search-result-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .search-result-item:hover {
            background-color: #f5f5f5;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .user-form {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../includes/menu.php"); ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>


                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Main Content ================== -->
            <div class="user-management">
                <h1>Modify User</h1>
                <?php
                try {
                    require("../../includes/db_connection.php");
                    $requete = null;

                    if ($_SERVER['REQUEST_METHOD'] === "GET" && isset($_GET['id'])) {
                        $ID = $_GET["id"];

                        $requete = null;
                        $query = null;

                        $query = "SELECT * FROM USERS WHERE ID = :ID";
                        $requete = $bd->prepare($query);
                        $requete->bindValue(":ID", $ID);
                        $requete->execute();
                        $result = $requete->fetch(PDO::FETCH_ASSOC);

                        $requete = $bd->prepare("SELECT NAME FROM ROLES WHERE NAME NOT LIKE 'Super Admin';");
                        $requete->execute();
                        $roles = $requete->fetchAll(PDO::FETCH_ASSOC);

                        $requete = $bd->prepare("SELECT NAME FROM ROLES WHERE ROLE_ID = :id;");
                        $requete->bindValue(":id", $result['USER_ROLE_ID']);
                        $requete->execute();
                        $user_role = $requete->fetch(PDO::FETCH_ASSOC);

                        echo "
                            <form method='POST' action='apply_modifications.php' class='user-form'>
                                <input type='hidden' name='id' value='{$ID}'>
                                <label>Last Name</label>
                                <input type='text' value='{$result['LAST_NAME']}' name='last_name' required>
                                <label>First Name</label>
                                <input type='text' value='{$result['FIRST_NAME']}' name='first_name' required>
                                <label>Username</label>
                                <input type='text' value='{$result['USERNAME']}' name='username' required>
                                <label>Phone</label>
                                <input type='text' value='{$result['TELEPHONE']}' name='telephone' required>
                                <label>Email</label>
                                <input type='email' value='{$result['EMAIL']}' name='email' required>
                        ";
                        if ($result["USER_ID"] != "U0") {
                            echo "<label>Role</label><select name='role' required>";
                            foreach ($roles as $role) {
                                if ($role['NAME'] === $user_role["NAME"]) {
                                    echo "<option selected>{$role['NAME']}</option>";
                                } else {
                                    echo "<option>{$role['NAME']}</option>";
                                }
                            }
                            echo "</select>";
                        }else{
                            echo "<input type='hidden' name='role' value='{$user_role["NAME"]}'>";
                            echo "
                                <h2>Password zone</h2>
                                <label>New password</label>
                                <input type='text' name='new_password'>
                            ";
                        }
                        echo "
                                <button type='submit'>Save Changes</button>
                            </form>
                        ";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
                    exit();
                }
                ?>
            </div>
        </div>
    </div>


</body>

</html>