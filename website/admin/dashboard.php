<?php
$pageTitle = "Admin Dashboard";
include_once 'includes/header.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch pending orders
$result = $conn->query("SELECT orders.id, users.username, orders.requested_version, orders.order_date FROM orders JOIN users ON orders.user_id = users.id WHERE orders.status = 'pending' ORDER BY orders.order_date DESC");

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
        max-width: 1200px;
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

    .btn {
        display: inline-block;
        padding: 8px 15px;
        background: #0ce48d;
        color: #fff;
        border: none;
        border-radius: 4px;
        text-decoration: none;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-danger {
        background-color: #e40c4d;
    }

    .btn-danger:hover {
        background-color: #a40b37;
    }

    .btn:hover {
        background: #0ba467;
    }

    .order-list table {
        width: 100%;
        border-collapse: collapse;
        overflow-x: auto;
    }

    .order-list th,
    .order-list td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #333;
    }

    .order-list th {
        background-color: #1a1a1a;
    }
</style>

<div class="main-content">
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>

        <div class="dashboard-section">
            <h2>Confirm Orders</h2>
            <div class="order-list">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Username</th>
                            <th>Requested Version</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['requested_version']); ?></td>
                                    <td><?php echo $row['order_date']; ?></td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn">Details</a>
                                        <a href="admin_logic.php?action=cancel_order&id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Cancel</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No pending orders.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>Change Password</h2>
            <form action="admin_logic.php" method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required style="width: 300px; padding: 10px; background-color: #222; color: #fff; border: 1px solid #333;">
                </div>
                <button type="submit" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php
include_once 'includes/footer.php';
?>