
<?php
    include("user_infos.php");
    include("functions.php");
?>

<script src="/Stockify/main.js"></script>

<div class="navigation">
    <ul>
        <li>
            <a href="/Stockify/index.html">
                <span class="icon">
                    <img class="logo" src="/Stockify/uploads/logo.jpeg" alt="">
                </span>
                <span class="title1">Stockify</span>
            </a>
        </li>

        <li>
            <a href="/Stockify/Front-end/dashboard/index.php">
                <span class="icon">
                    <ion-icon name="home-outline"></ion-icon>
                </span>
                <span class="title">Dashboard</span>
            </a>
        </li>
        <?php if (check_role("VIEW_CLIENTS",$username) || check_role("ADMINISTRATOR",$username)): ?>
            <li>
                <a href="/Stockify/Back-end/clients/show_clients.php">
                    <span class="icon">
                        <ion-icon name="people-outline"></ion-icon>
                    </span>
                    <span class="title">Clients</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (check_role("VIEW_ORDERS",$username) || check_role("ADMINISTRATOR",$username)): ?>
            <li>
                <a href="/Stockify/Back-end/orders/submit_post.php">
                    <span class="icon">
                        <ion-icon name="bag-check-outline"></ion-icon>
                    </span>
                    <span class="title">Orders</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (check_role("VIEW_INVOICES",$username) || check_role("ADMINISTRATOR",$username)): ?>
            <li>
                <a href="/Stockify/Front-end/invoices/invoice.php">
                    <span class="icon">
                        <ion-icon name="receipt-outline"></ion-icon>
                    </span>
                    <span class="title">Invoices</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (check_role("VIEW_QUOTATIONS",$username) || check_role("ADMINISTRATOR",$username)): ?>
            <li>
                <a href="/Stockify/Back-end/quotations/submit_post.php">
                    <span class="icon">
                        <ion-icon name="reader-outline"></ion-icon>
                    </span>
                    <span class="title">Quotations</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (check_role("VIEW_PRODUCTS",$username) || check_role("VIEW_CATEGORIES",$username) || check_role("ADMINISTRATOR",$username)): ?>
            <li>
                <a href="/Stockify/Front-end/storage management/pick_choice.php">
                    <span class="icon">
                        <ion-icon name="server-outline"></ion-icon>
                    </span>
                    <span class="title">Storage</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (check_role("VIEW_USERS",$username) || check_role("VIEW_ROLES",$username) || check_role("ADMINISTRATOR",$username)): ?>
            <li>
                <a href="/Stockify/Front-end/user management/index.php">
                    <span class="icon">
                        <ion-icon name="person-add-outline"></ion-icon>
                    </span>
                    <span class="title">Users & Roles</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (check_role("VIEW_SUPPLIERS",$username) || check_role("ADMINISTRATOR",$username)): ?>
            <li>
                <a href="/Stockify/Front-end/supplier/supplier.php">
                    <span class="icon">
                        <ion-icon name="cube-outline"></ion-icon>
                    </span>
                    <span class="title">Suppliers</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (check_role("ADMINISTRATOR",$username)): ?>
            <li>
                <a href="/Stockify/Front-end/dashboard/settings.php">
                    <span class="icon">
                        <ion-icon name="settings-outline"></ion-icon>
                    </span>
                    <span class="title">Settings</span>
                </a>
            </li>
        <?php endif; ?>

        <li>
            <a href="/Stockify/Front-end/login/index.php">
                <span class="icon">
                    <ion-icon name="log-out-outline"></ion-icon>
                </span>
                <span class="title">Log out</span>
            </a>
        </li>
    </ul>
</div>