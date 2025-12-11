<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="./invoice_summary.css?v=<?php echo time(); ?>" >
</head>
<body>
    <header>
        <div class="face">

            <img class="logo" src="../../../Front-end/images/logo.jpeg" alt="Stockify">
            <a class="name"><h1>Category products</h1></a>

        </div>
    </header>

    


<?php
try {

    $bd = new PDO('mysql:host=localhost;dbname=stockify_database;charset=utf8', 'root', '');
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $type = $_SESSION['invoice']['type'];
        $client_name = null;
        $company_name = null;
        $company_ICE = null;

        if ($type === "Sell") {
            $sell_type = $_SESSION['invoice']['sell_type'];
            $client_name = $_SESSION['invoice']['client_name'];
            $date = $_SESSION['invoice']['creation_date'];
            if ($sell_type === 'Company') {
                $company_ICE = $_SESSION['invoice']['company_ICE'];
            }

            $products_count = intval($_POST['products']);

            if (!isset($_SESSION['invoice']['products'])) {
                $_SESSION['invoice']['products'] = [];
            }
            if (!isset($_SESSION['invoice']['prices'])) {
                $_SESSION['invoice']['prices'] = [];
            }
            if (!isset($_SESSION['invoice']['quantities'])) {
                $_SESSION['invoice']['quantities'] = [];
            }

            $selected_products = [];
            $prices = [];
            $quantities = [];

            for ($i = 1; $i <= $products_count; $i++) {
                if (isset($_POST["reference$i"])) {
                    $reference = intval($_POST["reference$i"]);
                    $price = floatval($_POST["price$i"]);
                    $quantity = intval($_POST["quantity$i"]);

                    // Add to arrays
                    $selected_products[] = $reference;
                    $prices[$reference] = $price;
                    $quantities[$reference] = $quantity;

                    // Update session
                    $_SESSION['invoice']['products'][$reference] = $reference;
                    $_SESSION['invoice']['prices'][$reference] = $price;
                    $_SESSION['invoice']['quantities'][$reference] = $quantity;
                }
            }

            if (count($_SESSION['invoice']['products']) > 0) {
                
                $productsArray = array_values($_SESSION['invoice']['products']);
                $placeholders = implode(',', $productsArray);
                
                // Prepare the query
                $query = "SELECT REF_P, PRODUCT_NAME, PRICE, QUANTITY FROM PRODUCT WHERE REF_P IN ($placeholders)";
                $stmt = $bd->prepare($query);
                
                // Execute the query with the product values
                $stmt->execute();

                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "
                    <h2>Invoice Details</h2>
                    <p><strong>Date:</strong> {$date}</p>
                ";

                if (!empty($client_name)) {
                    echo "<p><strong>Client Name:</strong> {$client_name}</p>";
                }

                if ($sell_type === 'Company') {
                    echo "
                        <p><strong>Company ICE:</strong> {$company_ICE}</p>
                    ";
                }

                echo "
                    <table border='2px'>
                        <thead>
                            <tr align='center'>
                                <th class='mint'>Qu.</th>
                                <th>Product</th>
                                <th class='mint'>U.P TTC</th>
                                <th class='mint'>Total TTC</th>
                            </tr>
                        </thead>
                        <tbody>
                ";

                $TVA = 0.2;
                $grandTotal = 0;
                $grandTVA = 0;

                foreach ($products as $product) {
                    $ref = $product['REF_P'];
                    $unitPriceTTC = floatval($_SESSION['invoice']['prices'][$ref]);
                    $quantity = intval($_SESSION['invoice']['quantities'][$ref]);

                    $priceBeforeTax = $unitPriceTTC / (1 + $TVA);
                    $TVAamount = $unitPriceTTC - $priceBeforeTax;
                    $totalPriceTTC = $unitPriceTTC * $quantity;
                    $totalTVA = $TVAamount * $quantity;

                    $grandTotal += $totalPriceTTC;
                    $grandTVA += $totalTVA;

                    echo "
                        <tr align='center'>
                            <td>{$quantity}</td>
                            <td>{$product['PRODUCT_NAME']}</td>
                            <td>{$unitPriceTTC}</td>
                            <td>{$totalPriceTTC}</td>
                        </tr>
                    ";
                }

                $grandHT = $grandTotal - $grandTVA;

                echo "
                    </tbody>
                    </table>
                    <p><strong>Total HT :</strong> {$grandHT} MAD</p>
                    <p><strong>Total TVA :</strong> {$grandTVA} MAD</p>
                    <p><strong>Montant Total :</strong> {$grandTotal} MAD</p>
                    <form method='GET' action='generate_invoice.php'>
                       <button type='submit'>Print Invoice</button>
                    </form>


                    <form method='GET' action='choose_category.php'>
                        <button type='submit'>Add more products</button>
                    </form>
                ";
            } else {
                session_destroy();
                header("Location: ../create_invoice.php");
            }
        }
    } else {
        echo "<p>Invalid request method.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
