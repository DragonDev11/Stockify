<?php
session_start();
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

    <script src="../../../main.js"></script>

    <title>Create Delivery Check</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .delivery-check {
            padding: 20px;
            margin: 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
        }

        .delivery-check h2 {
            color: #0ce48d;
            margin-bottom: 20px;
            text-align: center;
        }

        .delivery-form {
            max-width: 800px;
            margin: 0 auto;
        }

        .client-info {
            margin: 15px 0;
        }

        .client-info p {
            margin: 5px 0;
            color: var(--black1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            color: var(--black1);
        }

        .mint {
            color: #0ce48d;
        }

        input[type="number"],
        input[type="date"],
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .totals {
            margin: 20px 0;
            font-size: 1.1em;
        }

        .totals p {
            margin: 5px 0;
        }

        .payment-section {
            margin: 20px 0;
        }

        select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            background-color: #0ce48d;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
        }

        .success-message {
            color: green;
            text-align: center;
            margin: 10px 0;
        }

        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }

            .payment-section {
                display: flex;
                flex-direction: column;
            }

            select {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <?php include("../../../includes/menu.php") ?>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <?php include("../../../includes/topbar.php") ?>

            <!-- ======================= Main Content ================== -->
            <!-- ======================= Main Content ================== -->
            <div class="delivery-check">
                <header>
                    <p id="error_tag" class="error-message"></p>
                    <p id="message_tag" class="success-message"></p>
                </header>

                <?php
                require("../../../includes/db_connection.php");

                if (isset($_SESSION["order"])) {
                    $orderID = $_SESSION["order"]["orderID"];
                    $references = $_SESSION["order"]["products_references"];
                    $quantities = $_SESSION["order"]["products_quantities"];
                    $delivered_quantities = $_SESSION["order"]["products_delivered_quantities"];
                    $prices = [];
                    $products_names = [];

                    $request = $bd->prepare("SELECT * FROM BON_COMMANDE_HEADER WHERE ID = :id;");
                    $request->execute([":id" => $orderID]);

                    $header = $request->fetch(PDO::FETCH_ASSOC);

                    $client_name = $header["CLIENT_NAME"];
                    $ice = $header["COMPANY_ICE"];

                    $request = $bd->prepare("SELECT count(*) FROM BON_LIVRAISON_HEADER;");
                    $request->execute();

                    $delivery_number = $request->fetchColumn(0) + 1;
                    $delivery_number_string = "BL" . $delivery_number;

                    for ($i = 0; $i < sizeof($references); $i++) {
                        $request = $bd->prepare("SELECT PRICE, PRODUCT_NAME FROM PRODUCT WHERE REFERENCE = :ref;");
                        $request->bindValue(":ref", $references[$i]);
                        $request->execute();
                        $result = $request->fetch(PDO::FETCH_ASSOC);
                        $prices[$i] = $result["PRICE"];
                        $products_names[$i] = $result["PRODUCT_NAME"];
                    }

                    echo "<h2>Delivery Check</h2>";
                    echo "
                <form method='POST' action='validate_delivery_check.php' class='delivery-form'>
                    <input type='hidden' name='validate_delivery' value='1'>
                    <table>
                        <thead>
                            <tr>
                                <th>Number</th>
                                <th>Delivery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{$delivery_number_string}</td>
                                <td><input type='date' name='date' id='date' min='{$header['DATE']}' required></td>
                            </tr>
                        </tbody>
                    </table>
            ";

                    echo "<div class='client-info'>";
                    if (!empty($client_name)) {
                        echo "<p><strong>Client Name:</strong> {$client_name}</p>";
                    }

                    if ($header["TYPE"] == 'Company') {
                        echo "<p><strong>Company ICE:</strong> {$ice}</p>";
                    }
                    echo "</div>";

                    echo "
                <table>
                    <thead>
                        <tr>
                            <th class='mint'>Quantity</th>
                            <th>Product</th>
                            <th class='mint'>Unit Price TTC</th>
                            <th class='mint'>Total TTC</th>
                        </tr>
                    </thead>
                    <tbody>
            ";

                    $TVA = 0.2;
                    $grandTotal = 0;
                    $grandTVA = 0;

                    for ($i = 0; $i < sizeof($references); $i++) {
                        $unitPriceTTC = floatval($prices[$i]);
                        $quantity = intval($quantities[$i] - $delivered_quantities[$i]);
                        $reference = $references[$i];
                        $name = $products_names[$i];

                        $priceBeforeTax = $unitPriceTTC / (1 + $TVA);
                        $TVAamount = $unitPriceTTC - $priceBeforeTax;
                        $totalPriceTTC = $unitPriceTTC * $quantity;
                        $totalTVA = $TVAamount * $quantity;

                        $grandTotal += $totalPriceTTC;
                        $grandTVA += $totalTVA;

                        echo "
                        <tr>
                            <input type='hidden' name='references[]' value='{$reference}'>
                            <input type='hidden' name='names[]' value='{$name}'>
                            <td><input type='number' name='new_quantities[]' min='0' max='{$quantity}' value='{$quantity}' oninput='calculate_totals()'></td>
                            <td>{$name}</td>
                            <td><input type='number' name='prices[]' value='{$unitPriceTTC}' oninput='calculate_totals()' readonly></td>
                            <td id='total_product_ttc'>{$totalPriceTTC}</td>
                        </tr>
                ";
                    }

                    $grandHT = $grandTotal - $grandTVA;

                    echo "
                    </tbody>
                </table>
                <div class='totals'>
                    <p id='total_ht'><strong>Total HT :</strong> {$grandHT} MAD</p>
                    <p id='total_tva'><strong>Total TVA :</strong> {$grandTVA} MAD</p>
                    <p id='total_ttc'><strong>Total TTC :</strong> {$grandTotal} MAD</p>
                </div>
                <div class='payment-section'>
                    <label>Payment Method:</label>
                    <select name='payement' id='payement' onchange='toggleFields()'>
                        <option value='1'>Cash</option>
                        <option value='2'>Check</option>
                        <option value='3'>Bank Draft</option>
                    </select>
                    <div id='reference_section' style='display:none; margin-top:10px;'>
                        <label id='reference_label'>Reference</label>
                        <input type='text' name='payement_reference' id='payement_ref'>
                    </div>
                </div>
                <button type='submit' class='btn'>Validate Delivery Check</button>
            </form>
            ";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        const urlparams = new URLSearchParams(window.location.search);
        const error = urlparams.get("error");
        if (error) {
            console.log(error);
            document.getElementById("error_tag").textContent = error;
        }
        const message = urlparams.get("message");
        if (message) {
            document.getElementById("message_tag").textContent = message;
        }
        calculate_totals();
        toggleFields();

        function calculate_totals() {
            const rows = document.querySelectorAll("table tbody tr");
            let totalHT = 0;
            let totalTVA = 0;
            let totalTTC = 0;
            const taxRate = 0.2; // Example TVA rate (20%)

            rows.forEach(row => {
                const quantityInput = row.querySelector('input[name^="new_quantities[]"]');
                const priceInput = row.querySelector('input[name^="prices[]"]');
                const totalInput = row.querySelector('#total_product_ttc');

                const quantity = parseFloat(quantityInput?.value) || 0;
                const price = parseFloat(priceInput?.value) || 0.00;

                const totalRow = quantity * price;
                if (totalInput) totalInput.textContent = totalRow.toFixed(2);

                totalTTC += totalRow;
            });

            totalHT = totalTTC / (1 + taxRate);
            totalTVA = totalTTC - totalHT;

            document.getElementById("total_ht").innerHTML = "<strong>Total HT :</strong> " + totalHT.toFixed(2) + " MAD";
            document.getElementById("total_tva").innerHTML = "<strong>Total TVA :</strong> " + totalTVA.toFixed(2) + " MAD";
            document.getElementById("total_ttc").innerHTML = "<strong>Total TTC:</strong> " + totalTTC.toFixed(2) + " MAD";
        }

        function toggleFields() {
            const select_input = document.getElementById("payement");
            const ref_section = document.getElementById("reference_section");
            const ref_label = document.getElementById("reference_label");
            const ref_input = document.getElementById("payement_ref");
            var value = select_input.value;

            if (value === "1") {
                ref_section.style.display = "none";
                ref_input.required = false;
            } else {
                ref_section.style.display = "block";
                ref_input.required = true;
                if (value === "2") {
                    ref_label.textContent = "Check Number";
                } else if (value === "3") {
                    ref_label.textContent = "Draft Reference";
                }
            }
        }
    </script>
</body>

</html>