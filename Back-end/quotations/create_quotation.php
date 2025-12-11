<?php
session_start();

try {
    require("../../includes/db_connection.php");

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['client_name']) && isset($_POST['sell_type'])) {
            $client_name = $_POST['client_name'];
            $sell_type = $_POST['sell_type'];
            $date = date("Y-m-d");
            $address = $_POST['address'] ?? '';
            $company_ICE = $_POST['company_ICE'] ?? null;

            $_SESSION['quotation'] = [
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
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
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

    <!-- Styles -->
    <link rel="stylesheet" href="../css/style.css">

    <title>Create Quotation</title>
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

        /* =============== Form Container ============== */
        .form-container {
            margin: 20px;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 800px;
            margin: 20px auto;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .form-header h1 {
            color: #0ce48d;
            margin: 0;
            font-size: 1.8rem;
        }

        /* =============== Form Styles ============== */
        .quotation-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #0ce48d;
            outline: none;
            box-shadow: 0 0 0 3px rgba(12, 228, 141, 0.1);
        }

        .form-actions {
            grid-column: span 2;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
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

        /* =============== Error Message ============== */
        .error-message {
            color: #e74c3c;
            padding: 12px;
            background: #fde8e8;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #e74c3c;
            grid-column: span 2;
        }

        /* =============== Responsive Design ============== */
        @media (max-width: 768px) {
            .quotation-form {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }

            .form-actions {
                grid-column: span 1;
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
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
            <!-- ======================= Content ================== -->
            <div class="form-container">
                <div class="form-header">
                    <h1>Create Quotation</h1>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST" action="create_quotation.php" enctype="multipart/form-data" class="quotation-form">
                    <div class="form-group full-width">
                        <label for="creation_date">Creation Date</label>
                        <input type="date" id="creation_date" name="creation_date" readonly>
                    </div>

                    <div class="form-group">
                        <label for="sell_type">Client Type</label>
                        <select name="sell_type" onchange="toggleFields()">
                            <option value="Personal">Personal</option>
                            <option value="Company">Company</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="client_name" id="clientLabel">Client Name</label>
                        <input type="text" name="client_name" id="clientField" required>
                    </div>

                    <div class="form-group" id="iceGroup" style="display: none;">
                        <label for="company_ICE" id="companyICELabel">Company ICE</label>
                        <input type="text" name="company_ICE" id="companyICEField">
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <input type="text" name="address">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <ion-icon name="arrow-forward-outline"></ion-icon>
                            Continue
                        </button>
                    </div>
                </form>
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
            const sellType = document.querySelector("select[name='sell_type']");
            const iceGroup = document.getElementById("iceGroup");
            const companyICEField = document.getElementById("companyICEField");
            const clientField = document.getElementById("clientField");

            if (sellType.value === 'Company') {
                iceGroup.style.display = 'block';
                companyICEField.required = true;
                clientField.required = false;
                document.getElementById("clientLabel").textContent = "Company Name";
            } else {
                iceGroup.style.display = 'none';
                companyICEField.required = false;
                clientField.required = true;
                document.getElementById("clientLabel").textContent = "Client Name";
            }
        }
    </script>
</body>

</html>