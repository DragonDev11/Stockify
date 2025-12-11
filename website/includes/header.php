<?php
// This ensures the session is started on every page
include_once 'db_connect.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Stockify-AI Inventory Management'; ?></title>
    <link rel="stylesheet" href="css/styles.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/641a7b5ed2.js" crossorigin="anonymous"></script>
    
    </head>
<body>
    <header>
        <nav class="navbar" role="navigation" aria-label="Main Navigation">
            <div class="navbar__container">
                <a href="index.html" id="navbar-logo">Stockify</a>
                <ul class="navbar__menu">
                    <li class="navbar__item">
                        <a href="index.html" class="navbar__links">Home</a>
                    </li>
                    <li class="navbar__item">
                        <a href="index.html#services" class="navbar__links">Services</a>
                    </li>
                    <li class="navbar__item">
                        <a href="index.html#pricing" class="navbar__links">Pricing</a>
                    </li>
                    <?php if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])): ?>
                    <li class="navbar__btn mobile">
                        <a href="signup.php" class="button">Sign Up</a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="navbar__btn desktop">
                    <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
                        <a href="logout.php" class="button">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="button">Login</a>
                        <a href="signup.php" class="button" style="margin-left: 10px;">Sign Up</a>
                    <?php endif; ?>
                </div>

                <button class="navbar__toggle" id="mobile-menu" aria-label="Toggle Navigation" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>
    <main>