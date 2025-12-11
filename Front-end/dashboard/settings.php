<?php
session_start();
//include("../../includes/user_infos.php");
require "../../includes/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["tva"])) {
        $query = "UPDATE GENERAL_VARIABLES SET TVA_AMOUNT = :tva;";
        $request = $bd->prepare($query);
        $request->bindValue(":tva", $_POST["tva"]);

        try {
            $request->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }
}

$GENERAL_VARIABLES = $bd
    ->query("SELECT * FROM GENERAL_VARIABLES WHERE ID=0;")
    ->fetch(PDO::FETCH_ASSOC);
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

    <script src="../../Back-end/main.js"></script>

    <style>
        /* Settings Page Styling */
        .content-wrapper {
            display: flex;
            flex-direction: column;
            gap: 30px;
            padding: 40px;
        }

        .settings-card {
            background: gray;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.05);
            max-width: 500px;
            margin: auto;
        }

        .settings-card form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .settings-card label {
            font-size: 1rem;
            color: white;
        }

        .settings-card input[type="number"] {
            padding: 8px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 100%;
        }

        .settings-card button {
            background-color: var(--blue);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .settings-card button:hover {
            background-color: #0ac97c;
        }

        .danger button.export {
            background-color: #007bff;
        }

        .danger button.import {
            background-color: #ffc107;
        }

        .danger button.empty {
            background-color: #dc3545;
        }

        .danger button:hover {
            opacity: 0.85;
        }

        .popup {
            display: none;
            /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 999;
        }

        .popup-content {
            background: white;
            padding: 20px;
            width: 300px;
            margin: 100px auto;
            border-radius: 8px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 8px;
            right: 12px;
            font-size: 20px;
            cursor: pointer;
        }
    </style>

    <title>Settings</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
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



                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name .
                        " " .
                        $last_name; ?></p>
                </div>
            </div>

            <div class="content-wrapper">
                <div class="TVA settings-card">
                    <form method="POST" action="settings.php">
                        <label>TVA amount: </label>
                        <input type="number" min="0" step="0.01" name="tva" value="<?php echo $GENERAL_VARIABLES[
                            "TVA_AMOUNT"
                        ]; ?>">
                        <label>%</label>
                        <button type="submit">Save</button>
                    </form>
                </div>

                <div class="danger settings-card">
                    <form method="POST">
                        <button type="button" class="export" onclick="showPopup('export')">Export database</button>
                        <button type="button" class="empty" onclick="showPopup('empty')">Empty database</button>
                        <button type="button" class="import" onclick="showPopup('import')">Import database</button>
                    </form>
                </div>
            </div>
            <div class="popup" id="passwordPopup">
                <div class="popup-content">
                    <span class="close-btn" onclick="closePopup()">&times;</span>
                    <form method="POST" action="/Stockify/Back-end/settings/check_password.php" enctype="multipart/form-data">
                        <label for="password">Enter Password:</label>
                        <input type="password" name="password" id="password" required>
                        <input type="hidden" name="action" id="PopupAction" value="">
                        <input type="file" name="zip_file" id="zip_file_input" accept="application/zip">
                        <button type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');

    if (error) {
        let message = error.replaceAll("_", " ");
        alert(message);
    }

    function showPopup(action) {
        document.getElementById("passwordPopup").style.display = "block";
        document.getElementById("PopupAction").value = action;

        if (action == "import") {
            document.getElementById("zip_file_input").style.display = "block";
            document.getElementById("zip_file_input").required = true;
        } else {
            document.getElementById("zip_file_input").style.display = "none";
            document.getElementById("zip_file_input").required = false;
        }
    }

    function closePopup() {
        document.getElementById("passwordPopup").style.display = "none";
        document.getElementById("zip_file_input").style.display = "none";
        document.getElementById("zip_file_input").required = false;
    }
</script>

</html>
