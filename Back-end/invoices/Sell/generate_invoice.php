<?php
require('fpdf/fpdf.php');
session_start();

if (!isset($_SESSION['invoice'])) {
    die("No invoice data found.");
}

$client_name = $_SESSION['invoice']['client_name'];
$sell_type = $_SESSION['invoice']['sell_type'];
$company_ICE = $_SESSION['invoice']['company_ICE'] ?? '';
$date = $_SESSION['invoice']['creation_date'];
$products = $_SESSION['invoice']['products'];
$prices = $_SESSION['invoice']['prices'];
$quantities = $_SESSION['invoice']['quantities'];

// Créer le PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

// En-tête

$pdf->Cell(190, 10, 'FACTURE', 0, 1, 'C');
$pdf->Ln(5);

// Infos Client
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, "Client: " . $client_name, 0, 0);
if ($sell_type === 'Company') {
    $pdf->Cell(90, 10, "ICE: " . $company_ICE, 0, 1);
} else {
    $pdf->Ln(10);
}
$pdf->Cell(100, 10, "Date: " . $date, 0, 1);
$pdf->Ln(5);

// Tableau des produits
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 10, "QTE", 1, 0, 'C');

$pdf->Cell(80, 10, "DESIGNATION", 1, 0, 'C');
$pdf->Cell(40, 10, "P.U TTC", 1, 0, 'C');
$pdf->Cell(40, 10, "TOTAL TTC", 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$grandTotal = 0;
$grandTVA = 0;
$TVA = 0.2;

foreach ($products as $ref => $product) {
    $quantity = $quantities[$ref];
    $unitPriceTTC = $prices[$ref];
    $totalPriceTTC = $unitPriceTTC * $quantity;
    $TVAamount = ($unitPriceTTC / (1 + $TVA)) * $quantity * $TVA;
    
    $grandTotal += $totalPriceTTC;
    $grandTVA += $TVAamount;

    $pdf->Cell(20, 10, $quantity, 1, 0, 'C');
    $pdf->Cell(80, 10, $product, 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($unitPriceTTC, 2), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($totalPriceTTC, 2), 1, 1, 'C');
}

// Totaux
$grandHT = $grandTotal - $grandTVA;
$pdf->Ln(5);
$pdf->Cell(140, 10, "TOTAL HT", 1, 0);
$pdf->Cell(50, 10, number_format($grandHT, 2) . " MAD", 1, 1);
$pdf->Cell(140, 10, "TVA", 1, 0);
$pdf->Cell(50, 10, number_format($grandTVA, 2) . " MAD", 1, 1);
$pdf->Cell(140, 10, "TOTAL TTC", 1, 0);
$pdf->Cell(50, 10, number_format($grandTotal, 2) . " MAD", 1, 1);



// Générer le PDF
$pdf->Output('invoice.pdf', 'I'); // 'I' affiche directement le PDF dans le navigateur

$pdf->Output("I", "Facture.pdf");
?>
