<?php
session_start();
require("../../includes/db_connection.php");

$username = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_supplier'])) {
    $supplierName = trim($_POST['supplier_name']);
    $ice = trim($_POST['ice']);

    if (!empty($supplierName) && preg_match('/^\d{15}$/', $ice)) {
        try {
            $stmt = $bd->prepare("INSERT INTO SUPPLIER (SUPPLIERNAME, ICE) VALUES (:name, :ice)");
            $stmt->bindParam(':name', $supplierName);
            $stmt->bindParam(':ice', $ice);
            $stmt->execute();
            $message = "Supplier added successfully";
            $message = str_replace(" ", "+", $message);
            header("Location: supplier.php?message=$message");
            exit();
        } catch (PDOException $e) {
            $message = $e->getMessage();
            $message = str_replace(" ", "+", $message);
            header("Location: supplier.php?error=$message");
            exit();
        }
    } else {
        echo "<p class='error'>Supplier Name is required and ICE must be exactly 15 digits.</p>";
    }
}

// Handle supplier deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_supplier'])) {
    $supplierId = $_POST['supplier_id'];
    $stmt = $bd->prepare("DELETE FROM SUPPLIER WHERE ID = :id");
    $stmt->bindParam(':id', $supplierId);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
    exit;
}

// Fetch all suppliers
$suppliers = $bd->query("SELECT * FROM SUPPLIER ORDER BY ID DESC")->fetchAll(PDO::FETCH_ASSOC);
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

    <script src="main.js"></script>

    <title>Supplier Manager</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Search bar styles */
        .search {
            position: relative;
        }
        
        .search-results {
            display: none;
            position: absolute;
            background: white;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .search-results div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .search-results div:hover {
            background: #f5f5f5;
        }
        
        .supplier-row {
            transition: all 0.3s;
        }
    </style>
</head>

<script defer>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const message_url = urlParams.get('message');

        if (error) {
            let message = error.replaceAll("_", " ");
            document.getElementById('error-tag').textContent = message;
            document.getElementById('error-tag').style.display = "block";
        }

        if (message_url) {
            let message = message_url.replaceAll("_", " ");
            document.getElementById('message-tag').textContent = message;
            document.getElementById('message-tag').style.display = "block";
        }

        if (document.getElementById('message-tag').textContent == "") {
            document.getElementById('message-tag').style.display = "none";
        }

        if (document.getElementById('error-tag').textContent == "") {
            document.getElementById('error-tag').style.display = "none";
        }

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
        
        // Supplier search functionality
        document.getElementById('supplier-search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tr.supplier-row');
            
            rows.forEach(row => {
                const supplierName = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const supplierIce = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                if (supplierName.includes(searchTerm) || supplierIce.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    function confirmDelete(supplierId) {
        if (confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
            document.getElementById('delete-form-' + supplierId).submit();
        }
    }
</script>

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

                <div class="search">
                    <label>
                        <input type="text" id="supplier-search-input" placeholder="Search suppliers by name or ICE...">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                    <div class="search-results" id="supplier-search-results"></div>
                </div>

                <div class="cardName">
                    <p style="color: white;">Welcome, <?php echo $first_name . " " . $last_name; ?></p>
                </div>
            </div>

            <!-- ======================= Cards ================== -->
            <p class="error" id="error-tag"></p>
            <p class="message" id="message-tag"></p>

            <div class="supplier">
                <?php if (check_role("ADD_SUPPLIERS", $username) || check_role("ADMINISTRATOR", $username)): ?>
                    <h2>Add New Supplier</h2>
                    <form method="post">
                        <label for="supplier_name">Supplier Name:</label>
                        <input type="text" id="supplier_name" name="supplier_name" required>

                        <label for="ice">ICE (15 digits):</label>
                        <input type="text" id="ice" name="ice" pattern="\d{15}" title="ICE must be exactly 15 digits" required>

                        <button type="submit" name="add_supplier">Add Supplier</button>
                    </form>
                <?php endif; ?>
                <h2>Supplier List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>ICE</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr class="supplier-row">
                                <td><?php echo htmlspecialchars($supplier['SUPPLIERNAME']); ?></td>
                                <td><?php echo $supplier['ICE']; ?></td>
                                <td>
                                    <?php if (check_role("DELETE_SUPPLIERS", $username) || check_role("ADMINISTRATOR", $username)): ?>
                                        <form id="delete-form-<?php echo $supplier['ID']; ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="supplier_id" value="<?php echo $supplier['ID']; ?>">
                                            <button type="button" class="delete-btn"
                                                onclick="confirmDelete(<?php echo $supplier['ID']; ?>)">Delete</button>
                                            <input type="hidden" name="delete_supplier">
                                        </form>
                                    <?php endif; ?>
                                    <a href="show_invoices.php?supplier=<?php echo urlencode($supplier['SUPPLIERNAME']); ?>">
                                        <button class="show-btn">Show Invoices</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>