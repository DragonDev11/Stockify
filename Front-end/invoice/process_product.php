<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref = $_POST['ref'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Initialize the invoice array if not already set
    if (!isset($_SESSION['invoice'])) {
        $_SESSION['invoice'] = [];
    }

    // Add product to the invoice session
    $_SESSION['invoice'][] = [
        'ref' => $ref,
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
    ];

    echo 'Product added successfully.';
}
