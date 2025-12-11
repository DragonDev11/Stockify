<?php
$pageTitle = "User Dashboard - Stockify";
// The header include handles session start and db connection
include_once 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's confirmed order
$stmt = $conn->prepare("SELECT confirmed_version FROM orders WHERE user_id = ? AND status = 'confirmed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$confirmed_order = $result->fetch_assoc();
$stmt->close();
?>

<style>
    body {
        background-color:rgb(49, 47, 47);
        color: #fff;
    }

    .main-content {
        padding-top: 100px;
        /* Adjust for fixed navbar */
    }

    .dashboard-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 20px;
        background-color: #000;
        border-radius: 8px;
    }

    .dashboard-section {
        margin-bottom: 40px;
        padding: 20px;
        border: 1px solid #333;
        border-radius: 8px;
    }

    .dashboard-section h2 {
        color: #0ce48d;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    select,
    textarea {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #333;
        background-color: #222;
        color: #fff;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #0ce48d;
        color: #fff;
        border: none;
        border-radius: 4px;
        text-decoration: none;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn:hover {
        background: #0ba467;
    }

    #free-download-button {
        display: none;
        margin-top: 15px;
    }
</style>

<div class="main-content">
    <div class="dashboard-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

        <?php if ($confirmed_order): ?>
            <div class="dashboard-section">
                <h2>Your Premium App is Ready!</h2>
                <p>The administrator has confirmed your order. You can now download the <strong><?php echo htmlspecialchars($confirmed_order['confirmed_version']); ?></strong>.</p>
                <a href="#" class="btn"><?php echo htmlspecialchars($confirmed_order['confirmed_version']); ?> - Download</a>
            </div>
        <?php endif; ?>

        <div class="dashboard-section">
            <h2>Get Started with Stockify</h2>
            <div class="form-group">
                <label for="version-select">Choose your version:</label>
                <select id="version-select">
                    <option value="">--Select a Plan--</option>
                    <option value="free">Free Plan</option>
                    <option value="standard">Standard Plan</option>
                    <option value="premium">Premium Plan</option>
                </select>
            </div>
            <a href="#" id="free-download-button" class="btn">Download Free Version</a>
        </div>

        <div class="dashboard-section">
            <h2>Ask for the Premium Version</h2>
            <p>Order the Standard or Premium version and an admin will confirm your request.</p>
            <form action="user_logic.php" method="POST">
                <input type="hidden" name="action" value="place_order">
                <div class="form-group">
                    <label for="premium-version-select">Select Version:</label>
                    <select id="premium-version-select" name="requested_version" required>
                        <option value="Standard Plan">Standard Plan</option>
                        <option value="Premium Plan">Premium Plan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="note">Add a Note (Optional):</label>
                    <textarea id="note" name="note" rows="4"></textarea>
                </div>
                <button type="submit" class="btn">Validate Order</button>
            </form>
        </div>
    </div>
</div>
<script src="js/dashboard.js"></script>

<?php
// The footer include handles the closing tags
include_once 'includes/footer.php';
?>