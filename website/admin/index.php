<?php
$pageTitle = "Admin Login - Stockify";
// Note the change in path for the include
include_once 'includes/header.php';

// Redirect if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<style>
    .auth-container {
        background-color: #131313;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 120px 20px 20px 20px;
    }
    .auth-form {
        background-color: #000;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        width: 100%;
        max-width: 400px;
        color: #fff;
    }
    .auth-form h2 {
        color: #0ce48d;
        margin-bottom: 20px;
        text-align: center;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
    }
    .form-group input {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #333;
        background-color: #222;
        color: #fff;
    }
    .auth-btn {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 4px;
        background-color: #0ce48d;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .auth-btn:hover {
        background-color: #0ba467;
    }
    .auth-link {
        text-align: center;
        margin-top: 20px;
    }
    .auth-link a {
        color: #0ce48d;
        text-decoration: none;
    }
</style>

<div class="auth-container">
    <div class="auth-form">
        <h2>Admin Login</h2>
        <form action="admin_logic.php" method="POST">
            <input type="hidden" name="action" value="admin_login">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="admin">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required value="admin123">
            </div>
            <button type="submit" class="auth-btn">Login</button>
             <div class="auth-link">
                <p><a href="../login.php">User Login</a></p>
            </div>
        </form>
    </div>
</div>

<?php
// Note the change in path for the include
include_once '../includes/footer.php';
?>