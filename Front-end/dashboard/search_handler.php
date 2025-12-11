<?php
require_once '../../includes/db_connection.php';


header('Content-Type: application/json');

if (!isset($_GET['query']) || strlen($_GET['query']) < 2) {
    echo json_encode([]);
    exit;
}

$query = '%' . $_GET['query'] . '%';
$results = [];

try {
    // Search Products
    $stmt = $bd->prepare("SELECT REFERENCE, PRODUCT_NAME FROM PRODUCT WHERE PRODUCT_NAME LIKE ? OR REFERENCE LIKE ? LIMIT 5");
    $stmt->execute([$query, $query]);
    $results['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Search Clients
    $stmt = $bd->prepare("SELECT CLIENTNAME, ICE FROM CLIENT WHERE CLIENTNAME LIKE ? OR ICE LIKE ? LIMIT 5");
    $stmt->execute([$query, $query]);
    $results['clients'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Search Suppliers
    $stmt = $bd->prepare("SELECT SUPPLIERNAME, ICE FROM SUPPLIER WHERE SUPPLIERNAME LIKE ? OR ICE LIKE ? LIMIT 5");
    $stmt->execute([$query, $query]);
    $results['suppliers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Search Invoices
    $stmt = $bd->prepare("SELECT INVOICE_NUMBER, CLIENT_NAME, TOTAL_PRICE_TTC FROM SELL_INVOICE_HEADER WHERE INVOICE_NUMBER LIKE ? OR CLIENT_NAME LIKE ? ORDER BY DATE DESC LIMIT 5");
    $stmt->execute([$query, $query]);
    $results['invoices'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Search Orders
    $stmt = $bd->prepare("SELECT ID_COMMANDE, CLIENT_NAME, TOTAL_PRICE_TTC FROM BON_COMMANDE_HEADER WHERE ID_COMMANDE LIKE ? OR CLIENT_NAME LIKE ? ORDER BY DATE DESC LIMIT 5");
    $stmt->execute([$query, $query]);
    $results['orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Search Delivery Notes (BL)
    $stmt = $bd->prepare("SELECT ID_BON, CLIENT_NAME, TOTAL_PRICE_TTC FROM BON_LIVRAISON_HEADER WHERE ID_BON LIKE ? OR CLIENT_NAME LIKE ? ORDER BY DATE DESC LIMIT 5");
    $stmt->execute([$query, $query]);
    $results['deliveryNotes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Search Quotations (Devis)
    $stmt = $bd->prepare("SELECT DEVIS_NUMBER, CLIENT_NAME, TOTAL_PRICE_TTC FROM DEVIS_HEADER WHERE DEVIS_NUMBER LIKE ? OR CLIENT_NAME LIKE ? ORDER BY DATE DESC LIMIT 5");
    $stmt->execute([$query, $query]);
    $results['quotations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}