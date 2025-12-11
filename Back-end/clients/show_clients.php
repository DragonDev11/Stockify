<?php
session_start();
require("../../includes/db_connection.php");
?>

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

    <title>Clients</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .clients-container {
            margin: 20px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f4f4f4;
        }

        form.filter-form {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        form.filter-form label {
            margin-right: 10px;
        }

        form.filter-form select,
        form.filter-form button {
            padding: 8px 12px;
            margin-right: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        form.filter-form button {
            background: #0ce48d;
            color: white;
            border: none;
            cursor: pointer;
        }

        #error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>

<body>
 <!-- =============== Navigation ================ -->
<div class="container">
    <?php require("../../includes/menu.php"); ?>

    <!-- ========================= Main ==================== -->
    <div class="main">
        <div class="topbar">
            <div class="toggle">
                <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
            </div>

            <div class="search">
                <label>
                    <input type="text" id="client-search-input" placeholder="Search clients by name or ICE...">
                    <ion-icon name="search-outline"></ion-icon>
                </label>
                <div class="search-results" id="client-search-results"></div>
            </div>

            <div class="cardName">
                <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
            </div>
        </div>

        <!-- ======================= Clients Content ================== -->
        <div class="clients-container">
            <h1>Clients List</h1>

            <form method="GET" action="show_clients.php" class="filter-form">
                <label>Order By</label>
                <select name="order_column">
                    <option>ID</option>
                    <option>Client name</option>
                    <option>ICE</option>
                </select>
                <select name="order_type">
                    <option>Ascending</option>
                    <option>Descending</option>
                </select>
                <button type="submit">Apply</button>
            </form>

            <p id="error-message"></p>
            <div id="clients-table-container">
                <?php
                try {
                    $requete = null;

                    // Fetch clients from the database
                    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['order_column']) && isset($_GET['order_type'])) {

                        $columnsMap = [
                            "ID" => "ID",
                            "Client name" => "CLIENTNAME",
                            "ICE" => "ICE"
                        ];

                        $typesMap = [
                            "Ascending" => "ASC",
                            "Descending" => "DESC"
                        ];

                        $orderColumn = $columnsMap[$_GET['order_column']];
                        $orderType = $typesMap[$_GET['order_type']];

                        $query = "SELECT * FROM CLIENT ORDER BY $orderColumn $orderType";
                        $requete = $bd->prepare($query);
                        $requete->execute();
                    } else {
                        $requete = $bd->query('SELECT * FROM CLIENT');
                        $requete->execute();
                    }

                    $clients = $requete->fetchAll(PDO::FETCH_ASSOC);


                    echo "<table id='clients-table'>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client Name</th>
                                        <th>ICE</th>
                                        <th colspan='2'>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>";

                    if (count($clients) > 0) {
                        foreach ($clients as $client) {
                            echo "
                                    <tr class='client-row'>
                                        <td>{$client['ID']}</td>
                                        <td class='client-name'>{$client['CLIENTNAME']}</td>
                                        <td class='client-ice'>{$client['ICE']}</td>
                                        <td>
                                            <form method='POST' action='check_user_privileges.php' id='delete-form-{$client['ID']}'>
                                                <input type='hidden' name='client_id' value='{$client['ID']}'>
                                                <input type='hidden' name='action' value='delete_clients'>
                                                <button type='button' class='delete-btn'
                                                onclick='confirmDelete({$client['ID']})'>Delete</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method='POST' action='check_user_privileges.php'>
                                                <input type='hidden' name='client_id' value='{$client['ID']}'>
                                                <input type='hidden' name='action' value='view_invoices'>
                                                <button type='submit'>Show related invoices</button>
                                            </form>
                                        </td>
                                    </tr>

                                ";
                        }
                    } else {
                        echo "  <tr>
                                        <td colspan='3'>No clients in database</td>
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
</div>

<script defer>
    function confirmDelete(clientId) {
        if (confirm('Are you sure you want to delete this client? This action will remove everything related to this client, it cannot be undone.')) {
            document.getElementById('delete-form-' + clientId).submit();
        }
    }

    function show_error(message) {
        document.getElementById("error-message").textContent = message;
    }

    // Handle URL Parameters for Errors
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');

    if (error) {
        let message = '';
        switch (error) {
            case 'permission_denied':
                message = 'Permission denied. Contact your administrator.';
                break;
            default:
                message = 'An unknown error occurred.';
        }
        show_error(message);
    }

    // Client search functionality
    document.getElementById('client-search-input').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#clients-table tbody tr.client-row');
        
        rows.forEach(row => {
            const name = row.querySelector('.client-name').textContent.toLowerCase();
            const ice = row.querySelector('.client-ice').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || ice.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
</body>

</html>