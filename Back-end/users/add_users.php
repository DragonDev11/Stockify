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

    <script src="../main.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Add User</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 60px); /* Subtract topbar height */
        }

        .user-form-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }

        .user-form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .user-form h1 {
            color: #0ce48d;
            margin-bottom: 25px;
            text-align: center;
            font-size: 24px;
        }

        .user-form label {
            display: block;
            margin: 15px 0 8px;
            font-weight: 500;
            color: #333;
        }

        .user-form input[type="text"],
        .user-form input[type="email"],
        .user-form input[type="password"],
        .user-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .user-form input[type="text"]:focus,
        .user-form input[type="email"]:focus,
        .user-form input[type="password"]:focus,
        .user-form select:focus {
            border-color: #0ce48d;
            outline: none;
        }

        .user-form select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 1em;
        }

        .user-form button[type="submit"] {
            display: block;
            width: 100%;
            padding: 14px;
            margin-top: 25px;
            background: #0ce48d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .user-form button[type="submit"]:hover {
            background: #0abf7a;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 20px;
        }

        .footer-links a {
            color: #0ce48d;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #0abf7a;
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-top: 10px;
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
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include "../../includes/menu.php"; ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>



                <div class="user">
                    <!--we will not use the user image-->
                </div>
            </div>

            <!-- ======================= Add User Content ================== -->
            <div class="main-content">
                <div class="user-form-container">
                    <form method="POST" action="add_users.php" class="user-form">
                        <h1>Add New User</h1>

                        <?php try {
                            require "../../includes/db_connection.php";
                            $requete = null;

                            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                                $last_name = $_POST["lastname"];
                                $first_name = $_POST["firstname"];
                                $username = $_POST["username"];
                                $email = $_POST["email"];
                                $phone = $_POST["phone"];
                                $password = $_POST["password"];
                                $confirmPassword = $_POST["confirm-password"];
                                $role = $_POST["role"];

                                if ($password !== $confirmPassword) {
                                    echo "<p class='error-message'>Passwords do not match</p>";
                                } else {
                                    $requete = $bd->prepare(
                                        "SELECT USERNAME FROM USERS;",
                                    );
                                    $requete->execute();
                                    $usernames = $requete->fetchAll(
                                        PDO::FETCH_COLUMN,
                                        0,
                                    );

                                    $usernameExists = false;
                                    foreach ($usernames as $existingUsername) {
                                        if ($username == $existingUsername) {
                                            $usernameExists = true;
                                            break;
                                        }
                                    }

                                    if ($usernameExists) {
                                        echo "<p class='error-message'>Username already used</p>";
                                    } else {
                                        $requete = $bd->prepare(
                                            "SELECT ROLE_ID FROM ROLES WHERE NAME = :name;",
                                        );
                                        $requete->bindValue(":name", $role);
                                        $requete->execute();

                                        $role_id = $requete->fetch(
                                            PDO::FETCH_COLUMN,
                                            0,
                                        );

                                        // Generate unique ID
                                        $uniqueId =
                                            "U" . bin2hex(random_bytes(7.5));

                                        $password = password_hash(
                                            $password,
                                            PASSWORD_BCRYPT,
                                        );

                                        // Prepare the SQL query to insert data into the USERS table
                                        $requete = $bd->prepare('
                                            INSERT INTO USERS (USER_ID, last_name, first_name, username, user_role_id, telephone, email, user_password)
                                            VALUES (:user_id, :last_name, :first_name, :username, :role_id, :phone, :email, :password);
                                            UPDATE ROLES SET NUMBER_OF_USERS = NUMBER_OF_USERS+1 WHERE ROLE_ID = :role_id;
                                        ');

                                        // Bind the values to the prepared statement
                                        $requete->bindValue(
                                            ":user_id",
                                            $uniqueId,
                                        );
                                        $requete->bindValue(
                                            ":last_name",
                                            $last_name,
                                        );
                                        $requete->bindValue(
                                            ":first_name",
                                            $first_name,
                                        );
                                        $requete->bindValue(
                                            ":username",
                                            $username,
                                        );
                                        $requete->bindValue(
                                            ":role_id",
                                            $role_id,
                                        );
                                        $requete->bindValue(":phone", $phone);
                                        $requete->bindValue(":email", $email);
                                        $requete->bindValue(
                                            ":password",
                                            $password,
                                        );

                                        $requete->execute();

                                        echo "<p style='color: #0ce48d; text-align: center;'>User added successfully!</p>";
                                    }
                                }
                            }

                            $requete = $bd->prepare(
                                "SELECT NAME FROM ROLES WHERE NAME NOT LIKE 'Super Admin';",
                            );
                            $requete->execute();
                            $roles = $requete->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            echo "<p class='error-message'>Error: " .
                                $e->getMessage() .
                                "</p>";
                        } ?>

                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" placeholder="First Name" required>

                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" placeholder="Last Name" required>

                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" required>

                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Email" >

                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" placeholder="Phone Number" >

                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>

                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" required>

                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <?php foreach ($roles as $role) {
                                if ($role["NAME"] === "User") {
                                    echo "<option value='{$role["NAME"]}' selected>{$role["NAME"]}</option>";
                                } else {
                                    echo "<option value='{$role["NAME"]}'>{$role["NAME"]}</option>";
                                }
                            } ?>
                        </select>

                        <button type="submit">Add User</button>
                    </form>

                    <div class="footer-links">
                        <a href="../../Front-end/dashboard/index.php">Dashboard</a>
                        <a href="show_users.php">Back to users list</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Search functionality
        $('#search-input').on('input', function() {
            const searchTerm = $(this).val().trim();

            if (searchTerm.length > 0) {
                $.ajax({
                    url: '../../Back-end/search_users.php',
                    type: 'GET',
                    data: { term: searchTerm },
                    dataType: 'json',
                    success: function(data) {
                        const resultsContainer = $('#search-results');
                        resultsContainer.empty();

                        if (data.length > 0) {
                            data.forEach(user => {
                                resultsContainer.append(
                                    `<div class="search-result-item" data-id="${user.ID}">
                                        ${user.FIRST_NAME} ${user.LAST_NAME} (${user.USERNAME})
                                    </div>`
                                );
                            });
                            resultsContainer.show();
                        } else {
                            resultsContainer.append(
                                '<div class="search-result-item">No users found</div>'
                            );
                            resultsContainer.show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            } else {
                $('#search-results').hide();
            }
        });

        // Handle click on search result
        $(document).on('click', '.search-result-item', function() {
            const userId = $(this).data('id');
            if (userId) {
                window.location.href = `modify_users.php?id=${userId}`;
            }
        });

        // Hide search results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search').length) {
                $('#search-results').hide();
            }
        });
    });
    </script>
</body>
</html>
