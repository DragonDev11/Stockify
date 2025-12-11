<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Manager | Stockify</title>

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
        .storage-container {
            padding: 30px;
            margin: 20px;
        }

        .storage-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .storage-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            text-align: center;
        }

        .storage-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .storage-card h3 {
            color: #0ce48d;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .storage-card button {
            background: #0ce48d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .storage-card button:hover {
            background: #09c77d;
        }

        .storage-card i {
            font-size: 3rem;
            color: #0ce48d;
            margin-bottom: 20px;
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

            <!-- ======================= Storage Content ================== -->
            <div class="storage-container">
                <h2>Storage Management</h2>
                <div class="storage-options">
                    <div class="storage-card">
                        <ion-icon name="pricetags-outline"></ion-icon>
                        <h3>Categories</h3>
                        <form method="POST" action="../../Back-end/categories/check_user_privileges.php">
                            <input type="hidden" name="action" value="view_categories">
                            <button type="submit">Manage Categories</button>
                        </form>
                    </div>

                    <div class="storage-card">
                        <ion-icon name="cube-outline"></ion-icon>
                        <h3>Products</h3>
                        <form method="POST" action="../../Back-end/products/check_user_privileges.php">
                            <input type="hidden" name="action" value="view_products">
                            <button type="submit">Manage Products</button>
                        </form>
                    </div>
                </div>
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
        });
    </script>
</body>

</html>