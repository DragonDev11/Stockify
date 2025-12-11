<?php
session_start();
require("../../includes/db_connection.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['client_name']) && isset($_POST['sell_type']) && isset($_POST['creation_date'])) {
        $client_name = $_POST['client_name'];
        $sell_type = $_POST['sell_type'];
        $date = $_POST['creation_date'];
        $address = $_POST['address'] ?? '';
        $company_ICE = $_POST['company_ICE'] ?? null;

        $_SESSION['order'] = [
            'sell_type' => $sell_type,
            'client_name' => $client_name,
            'company_ICE' => $company_ICE,
            'address' => $address,
            'creation_date' => $date
        ];
        header("Location: choose_category.php");
        exit();
    }
}
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

    <title>Create Order</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 60px);
            /* Subtract topbar height */
        }

        .order-form-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }

        .order-form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .order-form h1 {
            color: #0ce48d;
            margin-bottom: 25px;
            text-align: center;
            font-size: 24px;
        }

        .order-form label {
            display: block;
            margin: 15px 0 8px;
            font-weight: 500;
            color: #333;
        }

        .order-form input[type="text"],
        .order-form input[type="date"],
        .order-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .order-form input[type="text"]:focus,
        .order-form input[type="date"]:focus,
        .order-form select:focus {
            border-color: #0ce48d;
            outline: none;
        }

        .order-form select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 1em;
        }

        .order-form button[type="submit"] {
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

        .order-form button[type="submit"]:hover {
            background: #0abf7a;
        }

        .hidden {
            display: none;
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

            <!-- ======================= Create Order Content ================== -->
            <div class="main-content">
                <div class="order-form-container">
                    <form method="POST" action="create_order.php" enctype="multipart/form-data" class="order-form">
                        <h1>Create New Order</h1>

                        <label for="client_name">Client Name</label>
                        <input type="text" name="client_name" id="clientField" required>

                        <label>Type</label>
                        <select name="sell_type" onchange="toggleFields()">
                            <option value="Personal">Personal</option>
                            <option value="Company">Company</option>
                        </select>

                        <label for="company_ICE" id="companyICELabel"></label>
                        <input type="text" name="company_ICE" id="companyICEField" class="hidden">

                        <label for='address'>Address</label>
                        <input type='text' name='address'>

                        <label for="creation_date">Date</label>
                        <input type="date" id="creation_date" name="creation_date" required>

                        <button type="submit">Create Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setCurrentDate();
            toggleFields();
        });

        function setCurrentDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            document.getElementById("creation_date").value = `${year}-${month}-${day}`;
        }

        function toggleFields() {
            const sellTypeSelect = document.querySelector("select[name='sell_type']");
            const companyICELabel = document.getElementById("companyICELabel");
            const companyICEField = document.getElementById("companyICEField");
            const clientField = document.getElementById("clientField");

            if (sellTypeSelect.value == 'Company') {
                companyICELabel.textContent = "Company ICE";
                companyICEField.classList.remove("hidden");
                companyICEField.required = true;
                clientField.required = true;
            } else {
                companyICELabel.textContent = "";
                companyICEField.classList.add("hidden");
                companyICEField.required = false;
                clientField.required = true;
            }
        }
    </script>
</body>

</html>