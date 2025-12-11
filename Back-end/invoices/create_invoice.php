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

    <script src="/Stockify/main.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setCurrentDate();
        });

        function confirmAction() {
            if (!confirm('The ICE provided is different from the ICE in the database. Do you want to auto-correct? (this will use the ICE from the database)')) {
                window.history.back();
            }
        }

        function setCurrentDate() {
            const today = new Date();

            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');

            document.getElementById("creation_date").value = `${year}-${month}-${day}`;
        }
    </script>
    <link rel="stylesheet" href="/Stockify/Back-end/css/style.css">

    <?php
    session_start();

    try {
        require("../../includes/db_connection.php");

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST['id']) && isset($_POST['supplier_name']) && isset($_POST['creation_date'])) {
                $id = $_POST['id'];
                $date = $_POST['creation_date'];
                $supplier_name = $_POST['supplier_name'];
                $ice = $_POST['company_ICE'];
                $imageData = null;

                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image = $_FILES['image']['tmp_name'];
                    $imageData = file_get_contents($image);
                } else {
                    $imageData = null;
                }

                $query = "SELECT count(*),ICE FROM SUPPLIER WHERE SUPPLIERNAME LIKE :supplier_name;";
                $request = $bd->prepare($query);
                $request->bindValue(":supplier_name", $supplier_name);
                $request->execute();
                //$count = $request->fetchColumn(0);
                $all = $request->fetch(PDO::FETCH_ASSOC);

                if ($all["count(*)"] <= 0) {
                    header("Location: create_invoice.php?error=supplier+does+not+exists");
                    exit();
                }

                if ($all["ICE"] != $ice) {
                    header("Location: create_invoice.php?error=ICE+mismatch+|+Correct ICE:+" . urlencode($all["ICE"]));
                    exit();
                }

                $query = "SELECT INVOICE_NUMBER FROM BUY_INVOICE_HEADER WHERE INVOICE_NUMBER LIKE 'INV" . $id . "';";
                $request = $bd->prepare($query);
                $request->execute();
                $result = $request->fetchColumn(0);
                if (!$result) {
                    $_SESSION['invoice'] = [
                        'type' => $type,
                        'id' => $id,
                        'ice' => $ice,
                        'supplier_name' => $supplier_name,
                        'date' => $date,
                        'invoice_image' => $imageData
                    ];
                    header("Location: Buy/fill_invoice.php");
                    exit();
                } else {
                    $query = "SELECT INVOICE_NUMBER FROM BUY_INVOICE_HEADER ORDER BY INVOICE_NUMBER DESC;";
                    $request = $bd->prepare($query);
                    $request->execute();
                    $invoices_numbers = $request->fetchColumn(0);
                    echo $invoices_numbers;
                    header("Location: create_invoice.php?error=Invoice+number+already+exists+|+Last+invoice+" . urlencode($invoices_numbers));
                    exit();
                }
            }
        }
    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
    ?>
    <script defer>
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');

            if (error) {
                let message = error;
                document.getElementById("error-tag").textContent = message;
            }
        });
    </script>
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 60px);
            /* Subtract topbar height */
        }

        #error-tag {
            color: red;
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            background: #ffeeee;
            border-radius: 4px;
        }

        .invoice-form-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }

        .invoice-form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .invoice-form h1 {
            color: #0ce48d;
            margin-bottom: 25px;
            text-align: center;
            font-size: 24px;
        }

        .invoice-form label {
            display: block;
            margin: 15px 0 8px;
            font-weight: 500;
            color: #333;
        }

        .invoice-form input[type="text"],
        .invoice-form input[type="date"],
        .invoice-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .invoice-form input[type="text"]:focus,
        .invoice-form input[type="date"]:focus,
        .invoice-form select:focus {
            border-color: #0ce48d;
            outline: none;
        }

        .invoice-form select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 1em;
        }

        .invoice-form button[type="submit"] {
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

        .invoice-form button[type="submit"]:hover {
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
            <p id="error-tag"></p>
            <!-- ======================= Create Order Content ================== -->
            <div class="main-content">
                <div class="invoice-form-container">
                    <form method="POST" action="create_invoice.php" enctype="multipart/form-data">
                        <h1>Create New Invoice</h1>
                        <label for="id">Invoice ID</label>
                        <input type="text" name="id" required>
                        <label for="supplier_name">Supplier Name</label>
                        <input type="text" name="supplier_name" required>
                        <label for="company_ICE">Company ICE</label>
                        <input type="text" name="company_ICE" minlength="15" maxlength="15" required>
                        <label for="creation_date">Date</label>
                        <input type="date" id="creation_date" name="creation_date" required>
                        <label for="img">Image</label>
                        <input type="file" id="image-upload" accept='image/*' name="image">
                        <button type="submit">Apply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>