<?php
include_once '../includes/db_connect.php';

$action = $_REQUEST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'admin_login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($admin = $result->fetch_assoc()) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                header("Location: dashboard.php");
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "Invalid username.";
        }
        $stmt->close();
    }
    
    elseif ($action === 'change_password') {
        if (!isset($_SESSION['admin_id'])) exit('Unauthorized');
        
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $admin_id = $_SESSION['admin_id'];
        
        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $admin_id);
        $stmt->execute();
        header("Location: dashboard.php?pw=changed");
        $stmt->close();
    }
    
    elseif ($action === 'confirm_order') {
        if (!isset($_SESSION['admin_id'])) exit('Unauthorized');
        
        $order_id = $_POST['order_id'];
        $confirmed_version = $_POST['confirmed_version'];
        
        $stmt = $conn->prepare("UPDATE orders SET status = 'confirmed', confirmed_version = ? WHERE id = ?");
        $stmt->bind_param("si", $confirmed_version, $order_id);
        $stmt->execute();
        header("Location: dashboard.php");
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'cancel_order') {
        if (!isset($_SESSION['admin_id'])) exit('Unauthorized');
        
        $order_id = $_GET['id'];
        
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        header("Location: dashboard.php");
        $stmt->close();
    }
}

$conn->close();
?>