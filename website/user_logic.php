<?php
include_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'signup') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        
        if ($stmt->execute()) {
            header("Location: login.php");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    elseif ($action === 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: user_dashboard.php");
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that email.";
        }
        $stmt->close();
    }
    
    elseif ($action === 'place_order') {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        $requested_version = $_POST['requested_version'];
        $note = $_POST['note'];
        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, requested_version, note) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $requested_version, $note);
        
        if($stmt->execute()){
            header("Location: user_dashboard.php?order=success");
        } else {
            echo "Error placing order.";
        }
        $stmt->close();
    }
}
$conn->close();
?>