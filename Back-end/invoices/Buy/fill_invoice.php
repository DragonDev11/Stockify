<?php
    session_start();
    require("../../../includes/db_connection.php");
    $query = "SELECT CATEGORYNAME FROM CATEGORY;";
    $request = $bd->prepare($query);
    $request->execute();
    $categories = $request->fetchAll(PDO::FETCH_DEFAULT);

    if ($_SERVER["REQUEST_METHOD"] === "POST"){
        if (isset($_SESSION["invoice"])){
            if (!isset($_SESSION["invoice"]["data"])){
                $_SESSION["invoice"]["data"] = [];
            }
            if (!isset($_SESSION["invoice"]["total_ht"])){
                $_SESSION["invoice"]["total_ht"] = $_POST["total_ht"];
            }
            if (!isset($_SESSION["invoice"]["total_ht"])){
                $_SESSION["invoice"]["total_tva"] = $_POST["total_tva"];
            }
            if (!isset($_SESSION["invoice"]["total_ht"])){
                $_SESSION["invoice"]["total_ttc"] = $_POST["total_ttc"];
            }

            // Initialize an array to hold all rows
            $invoiceData = [];

            // Loop through the posted form data to gather all rows
            $rowIndex = 0;
            while (isset($_POST["name_{$rowIndex}"])) {
                // Collect the data for this row
                $invoiceData[] = [
                    'quantity' => $_POST["quantity_{$rowIndex}"],
                    'name' => $_POST["name_{$rowIndex}"],
                    'price_ttc' => $_POST["price_ttc_{$rowIndex}"],
                    'total_ttc' => $_POST["total_ttc_{$rowIndex}"],
                    'category' => isset($_POST["category"]) ? $_POST["category"] : '',  // Category (only once per form)
                    'image' => isset($_FILES["image"]) ? $_FILES["image"]['name'] : '' // Image (only once per form)
                ];

                $rowIndex++;
            }

            // Store the invoice data in the session
            $_SESSION["invoice"]["data"] = $invoiceData;

            // Redirect to another page or display a success message
            header("Location: ../finalize_invoice.php"); // Redirect after form submission
            exit(); // Always call exit after a redirect to avoid further processing
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fill Invoice | Stockify</title>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Parkinsans', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
        }

        .face {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .name h1 {
            color: #0ce48d;
            font-size: 24px;
            font-weight: 600;
        }

        #pagename {
            color: #0ce48d;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }

        form {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .fields {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }

        thead th {
            background-color: #0ce48d;
            color: white;
            font-weight: 500;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: #0ce48d;
            outline: none;
        }

        input[readonly] {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .Vbutton {
            background-color: #0ce48d;
            color: white;
        }

        .Vbutton:hover {
            background-color: #09c77d;
            transform: translateY(-2px);
        }

        .X {
            background-color: #e74c3c;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
        }

        .X:hover {
            background-color: #c0392b;
        }

        .totals-table {
            width: 100%;
            max-width: 600px;
            margin: 30px auto;
        }

        .totals-table th {
            background-color: #0ce48d;
            color: white;
        }

        .totals-table td {
            font-weight: 500;
            font-size: 16px;
        }

        .category_field, .image_field {
            min-width: 150px;
        }
    </style>
</head>
<body>
    <header>
        <div class="face">
            <img class="logo" src="../../../uploads/logo.jpeg" alt="Stockify">
            <a class="name">
                <h1>Stockify</h1>
            </a>
        </div>
    </header>
    
    <h1 id="pagename">Add Products to Invoice</h1>
    
    <form method="POST" action="../finalize_invoice.php" enctype='multipart/form-data'>
        <div class="fields">
            <table>
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Product Name</th>
                        <th>Unit Price TTC</th>
                        <th>Total TTC</th>
                        <th class="category_field">Category</th>
                        <th class="image_field">Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="number" name="quantity_0" oninput="calculate_totals()" min="1" required></td>
                        <td><input type="text" name="name_0" required></td>
                        <td><input type="number" name="price_ttc_0" oninput="calculate_totals()" min="0.00" step="0.01" required></td>
                        <td><input type="text" name="total_ttc_0" readonly></td>
                        <td class="category_field">
                            <select name="category_0">
                                <?php
                                    foreach ($categories as $category) {
                                        echo "<option>{$category['CATEGORYNAME']}</option>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td class="image_field">
                            <input type="file" name="image_0" accept="image/*">
                        </td>
                        <td><button type="button" class="X" onclick="delete_row(this)">X</button></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="buttons">
                <button type="button" class="Vbutton" id="AddButton" onclick="add_row()">
                    <ion-icon name="add-outline"></ion-icon>
                    Add Row
                </button>
            </div>
        </div>

        <table class="totals-table">
            <thead>
                <tr>
                    <th>TOTAL HT</th>
                    <th>TOTAL TVA</th>
                    <th>TOTAL TTC</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <input type="hidden" name="total_ht" value="0.00">
                    <td id="total_ht">0.00</td>
                    <input type="hidden" name="total_tva" value="0.00">
                    <td id="total_tva">0.00</td>
                    <input type="hidden" name="total_ttc" value="0.00">
                    <td id="total_ttc">0.00</td>
                    <input type="hidden" name="number_of_products" id="number_of_products">
                </tr>
            </tbody>
        </table>

        <div class="buttons">
            <button type="submit" class="Vbutton">
                <ion-icon name="checkmark-outline"></ion-icon>
                Finalize Invoice
            </button>
        </div>
    </form>

    <script>
        var rowIndex = 1;
        updateProductCount();

        function updateProductCount() {
            document.getElementById("number_of_products").value = rowIndex;
        }

        function add_row() {
            const tableBody = document.querySelector("table tbody");

            const newRow = document.createElement("tr");

            const columns = [
                `<input type="number" name="quantity_${rowIndex}" oninput="calculate_totals()" min="1" required>`,
                `<input type="text" name="name_${rowIndex}" required>`,
                `<input type="number" name="price_ttc_${rowIndex}" oninput="calculate_totals()" min="0.00" step="0.01" required>`,
                `<input type="text" name="total_ttc_${rowIndex}" readonly>`,
                `<select name="category_${rowIndex}" class="category_field">
                    <?php
                        foreach ($categories as $category) {
                            echo "<option>{$category['CATEGORYNAME']}</option>";
                        }
                    ?>
                </select>`,
                `<input type="file" name="image_${rowIndex}" class="image_field" accept="image/*">`,
                `<button type="button" class="X" onclick="delete_row(this)">X</button>`
            ];

            columns.forEach(columnHtml => {
                const cell = document.createElement("td");
                cell.innerHTML = columnHtml;
                newRow.appendChild(cell);
            });

            tableBody.appendChild(newRow);

            rowIndex++;
            updateProductCount();
        }

        function delete_row(button){
            const row = button.closest("tr");
            if (document.querySelectorAll("table tbody tr").length > 1) {
                row.remove();
                rowIndex--;
                updateProductCount();
                calculate_totals();
            } else {
                alert("You need at least one product row.");
            }
        }

        function calculate_totals() {
            const rows = document.querySelectorAll("table tbody tr");
            let totalHT = 0;
            let totalTVA = 0;
            let totalTTC = 0;
            const taxRate = 0.2; // Example TVA rate (20%)

            rows.forEach(row => {
                const quantityInput = row.querySelector('input[name^="quantity_"]');
                const priceInput = row.querySelector('input[name^="price_ttc_"]');
                const totalInput = row.querySelector('input[name^="total_ttc_"]');

                const quantity = parseFloat(quantityInput?.value) || 0;
                const price = parseFloat(priceInput?.value) || 0;

                const totalRow = quantity * price;
                if (totalInput) totalInput.value = totalRow.toFixed(2);

                totalTTC += totalRow;
            });

            totalHT = totalTTC / (1 + taxRate);
            totalTVA = totalTTC - totalHT;

            document.getElementById("total_ht").textContent = totalHT.toFixed(2);
            document.getElementById("total_tva").textContent = totalTVA.toFixed(2);
            document.getElementById("total_ttc").textContent = totalTTC.toFixed(2);
            document.querySelector("input[name='total_ht']").value = totalHT.toFixed(2);
            document.querySelector("input[name='total_tva']").value = totalTVA.toFixed(2);
            document.querySelector("input[name='total_ttc']").value = totalTTC.toFixed(2);
        }
    </script>
</body>
</html>