<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices Management | Stockify</title>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script src="../../main.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sub-container {
            padding: 30px;
            margin: 20px;
        }

        .management-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .management-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            text-align: center;
        }

        .management-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .management-card h3 {
            color: #0ce48d;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .management-card button {
            background: #0ce48d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .management-card button:hover {
            background: #09c77d;
        }

        .management-card i {
            font-size: 3rem;
            color: #0ce48d;
            margin-bottom: 20px;
        }

        .management .icon {
            width: 100px;
            height: auto;
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

            <!-- ======================= User Management Content ================== -->
            <div class="sub-container">
                <h2>Invoices Management</h2>
                <div class="management-options">
                    <div class="management-card">
                        <ion-icon class="management icon" name="download-outline"></ion-icon>
                        <h3>Buy invoices</h3>
                        <form method="POST" action="../../Back-end/invoices/check_user_privileges.php">
                            <input type="hidden" name="action" value="view_invoices">
                            <input type="hidden" name="redirection_url" value="show_buy_invoices.php">
                            <button type="submit">
                                <ion-icon name="settings-outline"></ion-icon>
                                Manage
                            </button>
                        </form>
                    </div>

                    <div class="management-card">
                        <ion-icon class="management icon" name="trending-up-outline"></ion-icon>
                        <h3>Sell invoices</h3>
                        <form method="POST" action="../../Back-end/invoices/check_user_privileges.php">
                            <input type="hidden" name="action" value="view_invoices">
                            <input type="hidden" name="redirection_url" value="show_sell_invoices.php">
                            <button type="submit">
                                <ion-icon name="settings-outline"></ion-icon>
                                Manage
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

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
    });
</script>

</html>
<div class="sell-invoices">
    <h3>Sell invoices</h3>
    <form method="POST" action="../../Back-end/invoices/check_user_privileges.php">
        <input type="hidden" name="action" value="view_invoices">
        <input type="hidden" name="redirection_url" value="show_sell_invoices.php">
        <button type="submit">Manage</button>
    </form>
</div>
<div class="buy-invoices">
    <h3>Buy invoices</h3>
    <form method="POST" action="../../Back-end/invoices/check_user_privileges.php">
        <input type="hidden" name="action" value="view_invoices">
        <input type="hidden" name="redirection_url" value="show_buy_invoices.php">
        <button type="submit">Manage</button>
    </form>
</div>