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

    <title>Roles</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style_choose_products.css">
    <link rel="stylesheet" href="../../Front-end/roles management/show_roles.css?v=<?php echo time(); ?>">
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

            <!-- ======================= Content ================== -->
            <div class="roles-container">
                <h1>Roles List</h1>

                <form method='POST' class="roles-form">
                    <input type='hidden' name='action' value='add_roles'>
                    <div class='Buttons'>
                        <div class='bloc'>
                            <button type='submit' class="btn" formaction="check_user_privileges.php">Add Role</button>
                        </div>
                    </div>
                </form>

                <form method="GET" action="show_roles.php" class="filter-form">
                    <label>Order By</label>
                    <select name="order_column">
                        <option>ID</option>
                        <option>Name</option>
                        <option>Number of users</option>
                    </select>
                    <select name="order_type">
                        <option>Ascending</option>
                        <option>Descending</option>
                    </select>
                    <button type="submit" class="btn">Apply</button>
                </form>

                <?php
                try {
                    // Connect to the database
                    require("../../includes/db_connection.php");

                    $requete = null;

                    // Fetch products from the database
                    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['order_column']) && isset($_GET['order_type'])) {

                        $columnsMap = [
                            "ID" => "ROLE_ID",
                            "Name" => "NAME"
                        ];

                        $typesMap = [
                            "Ascending" => "ASC",
                            "Descending" => "DESC"
                        ];

                        $orderColumn = $columnsMap[$_GET['order_column']];
                        $orderType = $typesMap[$_GET['order_type']];

                        $query = "SELECT * FROM ROLES ORDER BY $orderColumn $orderType";
                        $requete = $bd->prepare($query);
                        $requete->execute();
                    } else {
                        $requete = $bd->query('SELECT * FROM ROLES');
                        $requete->execute();
                    }

                    $roles = $requete->fetchAll(PDO::FETCH_ASSOC);


                    echo "<table class='roles-table'>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Number of users</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>";



                    if (count($roles) > 0) {
                        foreach ($roles as $role) {
                            echo "<tr>
                                        <td>{$role['ROLE_ID']}</td>
                                        <td>{$role['NAME']}</td>
                                        <td>{$role['NUMBER_OF_USERS']}</td>
                                        <td class='actions'>
                                            <form method='POST' class='action-form'>
                                                <input type='hidden' name='id' value='{$role['ROLE_ID']}'>
                                                <input type='hidden' name='action' value='modify_roles'>
                                                <button type='submit' class='btn modify-btn' formaction='check_user_privileges.php'>Modify</button>
                                            </form>
                                            <form method='POST' class='action-form'>
                                                <input type='hidden' name='id' value='{$role['ROLE_ID']}'>
                                                <input type='hidden' name='action' value='delete_roles'>
                                                <button type='submit' class='btn delete-btn' formaction='check_user_privileges.php'>Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                ";
                        }
                    } else {
                        echo "  <tr>
                                        <td colspan='4'>No roles in database</td>
                                    </tr>";
                    }
                    echo "  </tbody>
                            </table>";
                } catch (PDOException $e) {
                    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>