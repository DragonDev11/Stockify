<?php
session_start();

if (isset($_SESSION["invoice"])) {
    unset($_SESSION["invoice"]);
}

if (isset($_SESSION["order"])) {
    unset($_SESSION["order"]);
}

if (isset($_SESSION["quotation"])) {
    unset($_SESSION["quotation"]);
}

include("../../includes/user_infos.php");
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
    <script src="../../main.js"></script>

    <!-- ================ chart ================= -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>


<style>
.search {
    position: relative;
    width: 400px;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    max-height: 400px;
    overflow-y: auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
}

.search-section {
    padding: 8px 12px;
    background: #f5f5f5;
    font-weight: bold;
    border-bottom: 1px solid #eee;
}

.search-item {
    padding: 10px 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.2s;
}

.search-item:hover {
    background: #f0f0f0;
}

.search-item .icon {
    margin-right: 10px;
    color: #666;
}

.no-results {
    padding: 15px;
    text-align: center;
    color: #666;
}
</style>

<body>
    <div class="container">
        <?php include("../../includes/menu.php"); ?>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon id="toggle-icon" name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" id="globalSearch" placeholder="Search here..." autocomplete="off">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-suggestions" id="searchSuggestions">
                        <!-- Suggestions will be populated here -->
                    </div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <?php if (check_role("ADMINISTRATOR", $username)): ?>
                <div class="cardBox">
                    <div class="card">
                        <div>
                            <div class="numbers">
                                <?php
                                $query = "SELECT count(*) FROM BON_COMMANDE_HEADER WHERE STATE NOT LIKE 'delivered';";
                                $request = $bd->prepare($query);
                                try {
                                    $request->execute();
                                    echo $request->fetchColumn() ?: "0";
                                } catch (PDOException $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                            <div class="cardName">Orders</div>
                        </div>

                        <div class="iconBx">
                            <ion-icon name="bag-check-outline"></ion-icon>
                        </div>
                    </div>

                    <div class="card">
                        <div>
                            <div class="numbers">
                                <?php
                                $query = "SELECT count(*) FROM BON_COMMANDE_HEADER WHERE STATE LIKE 'delivered';";
                                $request = $bd->prepare($query);
                                try {
                                    $request->execute();
                                    echo $request->fetchColumn() ?: "0";
                                } catch (PDOException $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                            <div class="cardName">Sales</div>
                        </div>

                        <div class="iconBx">
                            <ion-icon name="cart-outline"></ion-icon>
                        </div>
                    </div>

                    <div class="card">
                        <div>
                            <div class="numbers">
                                <?php
                                $query = "SELECT SUM(TOTAL_PRICE_TTC) FROM BON_COMMANDE_HEADER WHERE STATE LIKE 'delivered';";
                                $request = $bd->prepare($query);
                                try {
                                    $request->execute();
                                    $result = $request->fetchColumn(0);
                                    if ($result) {
                                        echo "{$result} MAD";
                                    } else {
                                        echo "0.00 MAD";
                                    }
                                } catch (PDOException $e) {
                                    echo "0.00 MAD";
                                }
                                ?>
                            </div>
                            <div class="cardName">Sales</div>
                        </div>

                        <div class="iconBx">
                            <ion-icon name="cash-outline"></ion-icon>
                        </div>
                    </div>

                    <div class="card">
                        <div>
                            <div class="numbers">
                                <?php
                                $query = "SELECT SUM(EARNINGS) FROM SELL_INVOICE_HEADER;";
                                $request = $bd->prepare($query);
                                try {
                                    $request->execute();
                                    $earnings = $request->fetchColumn(0);
                                    if (!$earnings){
                                        $earnings = 0.00;
                                    }
                                    echo "{$earnings} MAD";
                                } catch (PDOException $e) {
                                    echo "0.00 MAD";
                                }
                                ?>
                            </div>
                            <div class="cardName">Earnings</div>
                        </div>

                        <div class="iconBx">
                            <ion-icon name="cash-outline"></ion-icon>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ================ Order Details List ================= -->
            <div class="details">
                <?php if (check_role("VIEW_ORDERS", $username) || check_role("ADMINISTRATOR", $username)): ?>
                    <div class="recentOrders">
                        <div class="cardHeader">
                            <h2>Recent Orders</h2>
                            <form method="POST" action="../../Back-end/orders/check_user_privileges.php">
                                <input type="hidden" name="action" value="view_orders">
                                <button type="submit" class="btn">View All</button>
                            </form>
                        </div>

                        <!-- Recent Orders Table -->
                        <table>
                            <thead>
                                <tr>
                                    <td>Number</td>
                                    <td>Client</td>
                                    <td>Price</td>
                                    <td>Status</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT ID_COMMANDE, CLIENT_NAME, TOTAL_PRICE_TTC, STATE FROM BON_COMMANDE_HEADER ORDER BY ID_COMMANDE DESC LIMIT 5";
                                $request = $bd->prepare($query);
                                try {
                                    $request->execute();
                                    $results = $request->fetchAll(PDO::FETCH_ASSOC);

                                    if (count($results) > 0) {
                                        foreach ($results as $result) {
                                            $stripped_status = str_replace(" ", "-", $result["STATE"]);
                                            echo "<tr>
                        <td>{$result['ID_COMMANDE']}</td>
                        <td>{$result['CLIENT_NAME']}</td>
                        <td>{$result['TOTAL_PRICE_TTC']} MAD</td>
                        <td><span class='status {$stripped_status}'>{$result['STATE']}</span></td>
                      </tr>";
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="empty">No orders found</td></tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="4" class="error">Error loading orders</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>


            <div class="details">
                <?php if (check_role("VIEW_SUPPLIERS", $username) || check_role("ADMINISTRATOR", $username)): ?>
                    <div class="recentOrders">
                        <div class="cardHeader">
                            <h2>Buying Invoices</h2>
                            <form method="POST" action="../../Back-end/invoices/check_user_privileges.php">
                                <input type="hidden" name="redirection_url" value="show_buy_invoices.php">
                                <input type="hidden" name="action" value="view_invoices">
                                <button type="submit" class="btn">View All</button>
                            </form>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <td>Number</td>
                                    <td>Supplier</td>
                                    <td>Total TTC</td>
                                    <td>Date</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT INVOICE_NUMBER, SUPPLIER_NAME, TOTAL_PRICE_TTC, DATE FROM BUY_INVOICE_HEADER ORDER BY DATE DESC LIMIT 5;";
                                $request = $bd->prepare($query);
                                try {
                                    $request->execute();
                                    $results = $request->fetchAll(PDO::FETCH_ASSOC);

                                    if (count($results) > 0) {
                                        foreach ($results as $result) {
                                            echo "<tr>
                                <td>{$result['INVOICE_NUMBER']}</td>
                                <td>{$result['SUPPLIER_NAME']}</td>
                                <td>{$result['TOTAL_PRICE_TTC']} MAD</td>
                                <td>{$result['DATE']}</td>
                            </tr>";
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="empty">No invoices found</td></tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="4" class="error">Error loading invoices</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>

            <div class="details">

                <!-- ================= Needed Products ================ -->
                <?php if (check_role("VIEW_PRODUCTS", $username) || check_role("ADMINISTRATOR", $username)): ?>
                    <div class="recentCustomers">
                        <div class="cardHeader">
                            <h2>Needed products</h2>
                        </div>
                        <table>
                            <thead>
                                <tr>

                                    <td>Details</td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT PRODUCT_NAME, QUANTITY, IMAGE FROM PRODUCT WHERE QUANTITY < 5 ORDER BY QUANTITY ASC LIMIT 5;";
                                $request = $bd->prepare($query);
                                try {
                                    $request->execute();
                                    $products = $request->fetchAll(PDO::FETCH_ASSOC);

                                    if (count($products) > 0) {
                                        foreach ($products as $product) {
                                            $inverse = $product["QUANTITY"] * (-1);
                                            echo '<tr>
                                                    <td width="60px">';
                                            if ($product["IMAGE"]) {
                                                $base64Image = base64_encode($product["IMAGE"]);
                                                echo '<div class="imgBx"><img src="data:image/jpeg;base64,' . $base64Image . '" alt=""></div>';
                                            } else {
                                                echo '<div class="imgBx"><img src="../../uploads/No_image_available.png" alt=""></div>';
                                            }
                                            echo '</td>
                                                <td>
                                                <h4>' . $product["PRODUCT_NAME"];
                                            if ($inverse > 0) {
                                                echo '<br><span>empty stock, ' . $inverse . ' articles needed</span>';
                                            } else {
                                                echo '<br><span> low stock, ' . $product["QUANTITY"] . ' articles left</span>';
                                            }
                                            echo '
                                                    </h4>
                                                </td>
                                            </tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="2" class="empty">All products sufficiently stocked</td></tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="2" class="error">Error loading products</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="details">
                <!-- ================= Recent Customers ================ -->
                <?php if (check_role("VIEW_CLIENTS", $username) || check_role("ADMINISTRATOR", $username)): ?>
                    <div class="recentCustomers">
                        <div class="cardHeader">
                            <h2>Recent Customers</h2>
                        </div>

                        <table>
                            <thead>
                                <tr>

                                    <td>Name</td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT CLIENTNAME FROM CLIENT ORDER BY ID DESC LIMIT 5;";
                                $request = $bd->prepare($query);
                                try {
                                    $request->execute();
                                    $results = $request->fetchAll(PDO::FETCH_ASSOC);

                                    if (count($results) > 0) {
                                        foreach ($results as $result) {
                                            echo "<tr>
                                <td width='60px'>
                                    <div class='imgBx'><img src='assets/imgs/default.jpg' alt=''></div>
                                </td>
                                <td><h4>{$result['CLIENTNAME']}</h4></td>
                            </tr>";
                                        }
                                    } else {
                                        echo '<tr><td colspan="2" class="empty">No customers found</td></tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="2" class="error">Error loading customers</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="details">
                <?php if (check_role("ADMINISTRATOR", $username)): ?>
                    <div class="recentCustomers">
                        <div class="cardHeader">
                            <h2>Sales Analytics</h2>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <select id="timePeriod" style="padding: 8px; margin-right: 10px;">
                                <option value="7">Last 7 days</option>
                                <option value="30">Last 30 days</option>
                                <option value="365">Last year</option>
                                <option value="0">All time</option>
                            </select>

                            <select id="dataType" style="padding: 8px;">
                                <option value="earnings">Earnings</option>
                                <option value="orders">Completed Orders</option>
                            </select>
                        </div>

                        <canvas id="salesChart" height="140"></canvas>
                    </div>

                    <script>
                        const timePeriodSelect = document.getElementById('timePeriod');
                        const dataTypeSelect = document.getElementById('dataType');
                        let salesChart;

                        function fetchSalesData(days, type) {
                            fetch(`get_sales_data.php?days=${days}&type=${type}`)
                                .then(response => response.json())
                                .then(data => {
                                    updateChart(data);
                                })
                                .catch(error => console.error('Error:', error));
                        }

                        function updateChart(data) {
                            const ctx = document.getElementById('salesChart').getContext('2d');

                            if (salesChart) {
                                salesChart.destroy();
                            }

                            const label = dataTypeSelect.value === 'earnings' ? 'Earnings (MAD)' : 'Completed Orders';

                            salesChart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: data.labels,
                                    datasets: [{
                                        label: label,
                                        data: data.values,
                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        borderWidth: 2,
                                        tension: 0.1,
                                        fill: true
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        }

                        timePeriodSelect.addEventListener('change', () => {
                            fetchSalesData(timePeriodSelect.value, dataTypeSelect.value);
                        });

                        dataTypeSelect.addEventListener('change', () => {
                            fetchSalesData(timePeriodSelect.value, dataTypeSelect.value);
                        });

                        // Load initial data
                        fetchSalesData(timePeriodSelect.value, dataTypeSelect.value);
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        if (error) {
            let message = error.replaceAll("_", " ");
            alert(message);
        }
        
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalSearch');
    const suggestionsContainer = document.getElementById('searchSuggestions');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 300);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            fetchSearchSuggestions(this.value.trim());
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });

    function fetchSearchSuggestions(query) {
        fetch('search_handler.php?query=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                displaySuggestions(data);
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
            });
    }

    function displaySuggestions(data) {
        if (!data || Object.keys(data).length === 0) {
            suggestionsContainer.innerHTML = '<div class="no-results">No results found</div>';
            suggestionsContainer.style.display = 'block';
            return;
        }

        let html = '';
        
        // Products
        if (data.products && data.products.length > 0) {
            html += '<div class="search-section">Products</div>';
            data.products.forEach(product => {
                html += `
                <div class="search-item" onclick="window.location.href='products/show_products.php?search=${encodeURIComponent(product.PRODUCT_NAME)}'">
                    <ion-icon name="cube-outline" class="icon"></ion-icon>
                    ${product.PRODUCT_NAME} (${product.REFERENCE})
                </div>`;
            });
        }

        // Clients
        if (data.clients && data.clients.length > 0) {
            html += '<div class="search-section">Clients</div>';
            data.clients.forEach(client => {
                html += `
                <div class="search-item" onclick="window.location.href='clients/show_clients.php?search=${encodeURIComponent(client.CLIENTNAME)}'">
                    <ion-icon name="people-outline" class="icon"></ion-icon>
                    ${client.CLIENTNAME} ${client.ICE ? '('+client.ICE+')' : ''}
                </div>`;
            });
        }

        // Suppliers
        if (data.suppliers && data.suppliers.length > 0) {
            html += '<div class="search-section">Suppliers</div>';
            data.suppliers.forEach(supplier => {
                html += `
                <div class="search-item" onclick="window.location.href='suppliers/show_suppliers.php?search=${encodeURIComponent(supplier.SUPPLIERNAME)}'">
                    <ion-icon name="storefront-outline" class="icon"></ion-icon>
                    ${supplier.SUPPLIERNAME} ${supplier.ICE ? '('+supplier.ICE+')' : ''}
                </div>`;
            });
        }

        // Invoices
        if (data.invoices && data.invoices.length > 0) {
            html += '<div class="search-section">Invoices</div>';
            data.invoices.forEach(invoice => {
                html += `
                <div class="search-item" onclick="window.location.href='invoices/show_invoices.php?search=${encodeURIComponent(invoice.INVOICE_NUMBER)}'">
                    <ion-icon name="document-text-outline" class="icon"></ion-icon>
                    ${invoice.INVOICE_NUMBER} - ${invoice.CLIENT_NAME} (${invoice.TOTAL_PRICE_TTC} MAD)
                </div>`;
            });
        }

        // Orders
        if (data.orders && data.orders.length > 0) {
            html += '<div class="search-section">Orders</div>';
            data.orders.forEach(order => {
                html += `
                <div class="search-item" onclick="window.location.href='orders/show_orders.php?search=${encodeURIComponent(order.ID_COMMANDE)}'">
                    <ion-icon name="clipboard-outline" class="icon"></ion-icon>
                    ${order.ID_COMMANDE} - ${order.CLIENT_NAME} (${order.TOTAL_PRICE_TTC} MAD)
                </div>`;
            });
        }

        // Delivery Notes (BL)
        if (data.deliveryNotes && data.deliveryNotes.length > 0) {
            html += '<div class="search-section">Delivery Notes</div>';
            data.deliveryNotes.forEach(note => {
                html += `
                <div class="search-item" onclick="window.location.href='delivery_notes/show_delivery_notes.php?search=${encodeURIComponent(note.ID_BON)}'">
                    <ion-icon name="car-outline" class="icon"></ion-icon>
                    ${note.ID_BON} - ${note.CLIENT_NAME} (${note.TOTAL_PRICE_TTC} MAD)
                </div>`;
            });
        }

        // Quotations (Devis)
        if (data.quotations && data.quotations.length > 0) {
            html += '<div class="search-section">Quotations</div>';
            data.quotations.forEach(quotation => {
                html += `
                <div class="search-item" onclick="window.location.href='quotations/show_quotations.php?search=${encodeURIComponent(quotation.DEVIS_NUMBER)}'">
                    <ion-icon name="pricetags-outline" class="icon"></ion-icon>
                    ${quotation.DEVIS_NUMBER} - ${quotation.CLIENT_NAME} (${quotation.TOTAL_PRICE_TTC} MAD)
                </div>`;
            });
        }

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';
    }
});
</script>
</body>

</html>