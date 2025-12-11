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
    <link rel="stylesheet" href="../css/style_choose_products.css">

    <title>Users Management</title>
    <style>
        /* =============== Base Styles ============== */
        * {
            font-family: "Parkinsans", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue: #0ce48d;
            --white: #fff;
            --gray: #f5f5f5;
            --black1: #222;
            --black2: #999;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: #f5f5f5;
        }

        /* =============== Users Container ============== */
        .users-container {
            margin: 20px;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .users-header h1 {
            color: #0ce48d;
            margin: 0;
            font-size: 1.8rem;
        }

        /* =============== Action Buttons ============== */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: #0ce48d;
            color: white;
        }

        .btn-primary:hover {
            background: #0abf7a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(12, 228, 141, 0.2);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #555;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* =============== Filter Form ============== */
        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .filter-form label {
            font-weight: 500;
            color: #444;
        }

        .filter-form select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .filter-form button {
            background: #0ce48d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-form button:hover {
            background: #0abf7a;
        }

        /* =============== Users Table ============== */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background-color: #0ce48d;
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
        }

        .users-table tr:hover {
            background-color: #f5f5f5;
        }

        .users-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .users-table tr:hover:nth-child(even) {
            background-color: #f0f0f0;
        }

        /* =============== Action Buttons in Table ============== */
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .modify-btn {
            background: #3498db;
            color: white;
        }

        .modify-btn:hover {
            background: #2980b9;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        .delete-btn:hover {
            background: #c0392b;
        }

        .disabled-btn {
            background: #95a5a6;
            color: white;
            cursor: not-allowed;
        }

        .action-btn ion-icon {
            font-size: 1rem;
        }

        /* =============== Error Message ============== */
        .error-message {
            color: #e74c3c;
            padding: 12px;
            background: #fde8e8;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            border-left: 4px solid #e74c3c;
        }

        /* =============== Search Bar Styles ============== */
        /* Search results are not needed with client-side filtering */
        .search-results {
            display: none;
        }

        /* =============== Responsive Design ============== */
        @media (max-width: 1200px) {
            .users-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        @media (max-width: 768px) {
            .users-container {
                margin: 10px;
                padding: 15px;
            }

            .filter-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .users-table th,
            .users-table td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }

            .action-btn {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .users-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include("../../includes/menu.php"); ?>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" id="search-input" placeholder="Search users by name or username...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <div class="users-container">
                <div class="users-header">
                    <h1>Users Management</h1>
                    <div class="action-buttons">
                        <a href="add_users.php" class="btn btn-primary">
                            <ion-icon name="person-add-outline"></ion-icon>
                            Add User
                        </a>
                    </div>
                </div>

                <form method="GET" action="show_users.php" class="filter-form">
                    <label>Order By:</label>
                    <select name="order_column">
                        <option value="ID">ID</option>
                        <option value="LAST_NAME">Last name</option>
                        <option value="FIRST_NAME">First name</option>
                        <option value="USERNAME">Username</option>
                        <option value="TELEPHONE">Phone</option>
                        <option value="EMAIL">Email</option>
                        <option value="CREATED_AT">Creation date</option>
                        <option value="UPDATED_AT">Update date</option>
                    </select>
                    <select name="order_type">
                        <option value="ASC">Ascending</option>
                        <option value="DESC">Descending</option>
                    </select>
                    <button type="submit">
                        <ion-icon name="filter-outline"></ion-icon>
                        Apply
                    </button>
                </form>

                <p id="error-message" class="error-message" style="display: none;"></p>

                <?php
                try {
                    // Connect to the database
                    require("../../includes/db_connection.php");

                    $requete = null;

                    // Fetch products from the database
                    $query = "SELECT u.*, r.NAME FROM USERS u LEFT OUTER JOIN ROLES r ON u.USER_ROLE_ID = r.ROLE_ID";

                    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['order_column']) && isset($_GET['order_type'])) {
                        $orderColumn = $_GET['order_column'];
                        $orderType = $_GET['order_type'];

                        // Validate column names to prevent SQL injection (important!)
                        $allowedColumns = ["USER_ID", "LAST_NAME", "FIRST_NAME", "USERNAME", "TELEPHONE", "EMAIL", "CREATED_AT", "UPDATED_AT"];
                        $allowedTypes = ["ASC", "DESC"];

                        if (in_array($orderColumn, $allowedColumns) && in_array($orderType, $allowedTypes)) {
                            $query .= " ORDER BY $orderColumn $orderType";
                        } else {
                            // Default order if invalid parameters are provided
                            $query .= " ORDER BY USER_ID ASC";
                        }
                    } else {
                        $query .= " ORDER BY USER_ID ASC"; // Default order
                    }

                    $requete = $bd->prepare($query);
                    $requete->execute();
                    $users = $requete->fetchAll(PDO::FETCH_ASSOC);


                    echo "<table class='users-table' id='users-table'>
                                <thead>
                                    <tr>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>";

                    if (count($users) > 0) {
                        foreach ($users as $user) {
                            echo "<tr class='user-row'>
                                        <td class='user-last-name'>{$user['LAST_NAME']}</td>
                                        <td class='user-first-name'>{$user['FIRST_NAME']}</td>
                                        <td class='user-username'>{$user['USERNAME']}</td>
                                        <td>{$user['NAME']}</td>
                                        <td>{$user['TELEPHONE']}</td>
                                        <td>{$user['EMAIL']}</td>
                                        <td>{$user['CREATED_AT']}</td>
                                        <td>{$user['UPDATED_AT']}</td>
                                        <td class='actions'>";

                            if ($user["USER_ROLE_ID"] != 0) {
                                echo "
                                        <form method='POST' style='display: inline;'>
                                            <input type='hidden' name='id' value='{$user['ID']}'>
                                            <input type='hidden' name='action' value='modify_users'>
                                            <button type='submit' formaction='check_user_privileges.php' class='action-btn modify-btn'>
                                                <ion-icon name='create-outline'></ion-icon>
                                            </button>
                                        </form>
                                        <form method='POST' style='display: inline;'>
                                            <input type='hidden' name='id' value='{$user['ID']}'>
                                            <input type='hidden' name='action' value='delete_users'>
                                            <button type='submit' formaction='check_user_privileges.php' class='action-btn delete-btn'>
                                                <ion-icon name='trash-outline'></ion-icon>
                                            </button>
                                        </form>
                                    ";
                            } else {
                                if ($user["ID"])
                                    echo "
                                            <form method='POST' style='display: inline;'>
                                                <input type='hidden' name='id' value='{$user['ID']}'>
                                                <input type='hidden' name='action' value='modify_users'>
                                                <button type='submit' formaction='check_user_privileges.php' class='action-btn modify-btn'>
                                                    <ion-icon name='create-outline'></ion-icon>
                                                </button>
                                            </form>
                                        ";

                                echo "
                                        <button class='action-btn disabled-btn' onclick=\"show_error('You can\\'t delete the Super Admin')\">
                                            <ion-icon name='trash-outline'></ion-icon>
                                        </button>
                                    ";
                            }

                            echo "</td></tr>";
                        }
                    } else {
                        echo "  <tr>
                                        <td colspan='9'>No users found</td>
                                    </tr>";
                    }
                    echo "  </tbody>
                            </table>";
                } catch (PDOException $e) {
                    echo "<p class='error-message'>Error: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Navigation hover effect
            let list = document.querySelectorAll(".navigation li");

            function activeLink() {
                list.forEach((item) => item.classList.remove("hovered"));
                this.classList.add("hovered");
            }
            list.forEach((item) => item.addEventListener("mouseover", activeLink));

            // Sidebar toggle
            let toggle = document.querySelector(".toggle");
            let navigation = document.querySelector(".navigation");
            let main = document.querySelector(".main");
            if (toggle && navigation && main) {
                toggle.onclick = function() {
                    navigation.classList.toggle("active");
                    main.classList.toggle("active");
                };
            }

            // Function to display error messages
            function show_error(message) {
                const errorElement = document.getElementById("error-message");
                errorElement.textContent = message;
                errorElement.style.display = "block";

                // Hide the error after 5 seconds
                setTimeout(() => {
                    errorElement.style.display = "none";
                }, 5000);
            }

            // User table search functionality (similar to first code)
            document.getElementById('search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const userRows = document.querySelectorAll('#users-table tbody tr.user-row');

                userRows.forEach(row => {
                    const lastName = row.querySelector('.user-last-name').textContent.toLowerCase();
                    const firstName = row.querySelector('.user-first-name').textContent.toLowerCase();
                    const username = row.querySelector('.user-username').textContent.toLowerCase();

                    // Check if the search term is found in any of the relevant columns
                    if (lastName.includes(searchTerm) || firstName.includes(searchTerm) || username.includes(searchTerm)) {
                        row.style.display = ''; // Show the row
                    } else {
                        row.style.display = 'none'; // Hide the row
                    }
                });
            });
        });
    </script>
</body>

</html>