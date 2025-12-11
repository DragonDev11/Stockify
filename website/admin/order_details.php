<?php
$pageTitle = "Order Details";
include_once 'includes/header.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];
$stmt = $conn->prepare("SELECT orders.id, users.username, orders.requested_version, orders.note FROM orders JOIN users ON orders.user_id = users.id WHERE orders.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit();
}
?>
<style>
    body {
        background-color:rgb(49, 47, 47);
        color: #fff;
    }

    .main-content {
        padding-top: 100px;
    }

    .details-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
        background-color: #000;
        border-radius: 8px;
    }

    .details-section {
        margin-bottom: 20px;
    }

    .details-section h2 {
        color: #0ce48d;
        margin-bottom: 20px;
    }

    .details-section p {
        line-height: 1.6;
    }

    .note-box {
        background-color: #222;
        border: 1px solid #333;
        padding: 15px;
        border-radius: 4px;
        min-height: 100px;
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

    select {
        width: 100%;
        max-width: 300px;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #333;
        background-color: #222;
        color: #fff;
        margin-right: 10px;
    }
</style>

<div class="main-content">
    <div class="details-container">
        <a href="dashboard.php">&larr; Back to Dashboard</a>
        <h1>Order Details #<?php echo $order['id']; ?></h1>

        <div class="details-section">
            <p><strong>User:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Requested Version:</strong> <?php echo htmlspecialchars($order['requested_version']); ?></p>
        </div>

        <div class="details-section">
            <h2>User Note:</h2>
            <div class="note-box">
                <p><?php echo nl2br(htmlspecialchars($order['note'] ? $order['note'] : 'No note provided.')); ?></p>
            </div>
        </div>

        <div class="details-section">
            <h2>Confirm Section</h2>
            <form action="admin_logic.php" method="POST">
                <input type="hidden" name="action" value="confirm_order">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <label for="confirmed-version">Choose version to submit to client:</label><br><br>
                <select name="confirmed_version" id="confirmed-version">
                    <option value="Standard Plan" <?php echo ($order['requested_version'] == 'Standard Plan') ? 'selected' : ''; ?>>Standard Plan</option>
                    <option value="Premium Plan" <?php echo ($order['requested_version'] == 'Premium Plan') ? 'selected' : ''; ?>>Premium Plan</option>
                </select>
                <button type="submit" class="btn">Confirm</button>
            </form>
        </div>
    </div>
</div>

<?php
include_once 'includes/footer.php';
?>