




<?php
// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $_SESSION['client_name'] = $_POST['client_name'];
    $_SESSION['invoice_type'] = $_POST['invoice_type'];

    // Store company details only if the invoice type is "company"
    if ($_POST['invoice_type'] === 'company') {
        $_SESSION['company_name'] = $_POST['company_name'];
        $_SESSION['company_ice'] = $_POST['company_ice'];
    } else {
        $_SESSION['company_name'] = null;
        $_SESSION['company_ice'] = null;
    }

    // Redirect to the categories page
    header('Location: categories.php');
    exit();
}
?>











<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="invoice.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css"
        integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <title>Stockify</title>
</head>


<body>

    <header>
        <div class="face">

            <img class="logo" src="../images/logo.jpeg" alt="Stockify">
            <a class="name">
                <h1>Invoice</h1>
            </a>

        </div>

        <div class="nav">
            <a class="contactus" href="">Contact us</a>
            <a class="login" href="">Login</a>
        </div>
    </header>



    <div class="all">




        <div class="first">
        
            <input class="side" type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="toggle">
        
            </label>


            

                    
        
            <div id="sidebar" class="slide">
                <h1>Menu</h1>
                <ul>
        
                    <li><a href="#"><i class="fas fa-tv"></i>Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-file-lines"></i>Invoice</a></li>
                    <li><a href="#"><i class="fas fa-user"></i>User management</a></li>
                    <li><a href="../../Back-end/products/show_products.php"><i class="fas fa-cubes"></i>Storage management</a></li>
                    <li><a href="#"><i class="fas fa-parachute-box"></i>Supplier management</a></li>
                </ul>
            </div>
        
        
           




           
              
       


    </div>
    <div class="container">
                <h1>Create Invoice</h1>
<form action="categories.php" method="POST">
    <div class="form-group">
        <label for="client-name">Client Name</label>
        <input type="text" id="client-name" name="client_name" required>
    </div>
    <div class="form-group">
        <label for="invoice-type">Invoice Type</label>
        <select id="invoice-type" name="invoice_type" required>
            <option value="personal">Personal</option>
            <option value="company">Company</option>
        </select>
    </div>
    <div id="company-fields" style="display:none;">
        <div class="form-group">
            <label for="company-name">Company Name</label>
            <input type="text" id="company-name" name="company_name">
        </div>
        <div class="form-group">
            <label for="company-ice">ICE</label>
            <input type="text" id="company-ice" name="company_ice">
        </div>
    </div>
    <button type="submit">Next</button>
</form>
<script>
    const invoiceType = document.getElementById('invoice-type');
    const companyFields = document.getElementById('company-fields');

    invoiceType.addEventListener('change', function () {
        companyFields.style.display = (this.value === 'company') ? 'block' : 'none';
    });
</script>




    



 
















    <script>
        // Show/Hide Company Fields based on Invoice Type
        const invoiceTypeSelect = document.getElementById('invoiceType');
        const companyFields = document.getElementById('companyFields');

        invoiceTypeSelect.addEventListener('change', function () {
            if (this.value === 'company') {
                companyFields.style.display = 'block';
            } else {
                companyFields.style.display = 'none';
            }
        });

        // Trigger change event to hide/show fields on page load
        invoiceTypeSelect.dispatchEvent(new Event('change'));
    </script>





    



    
</body>

</html>