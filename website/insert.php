<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stockify_website";

// Admin credentials
$adminUsername = "admin";
$adminPassword = "admin123";

// Hash the password
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL insert statement
$sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $adminUsername, $hashedPassword);

// Execute the statement
if ($stmt->execute()) {
    echo "Admin user inserted successfully.";
} else {
    echo "Error: " . $stmt->error;
}

// Close connections
$stmt->close();
$conn->close();
?>
