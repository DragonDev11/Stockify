<?php
session_start();

require("../../../includes/db_connection.php");

// Only require FPDF once at the beginning
if (!class_exists('FPDF')) {
    require("../../libs/fpdf/fpdf.php");
}

function numberToFrenchWords($number) {
    $units = array('', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf');
    $teens = array('dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf');
    $tens = array('', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix');
    $scales = array('', 'mille', 'million', 'milliard');

    $words = array();
    $number = str_replace(',', '', $number);
    $parts = explode('.', $number);
    $whole = (int)$parts[0];
    $decimal = isset($parts[1]) ? substr($parts[1], 0, 2) : '00';

    if ($whole == 0) {
        $words[] = 'zéro';
    } else {
        $chunks = array_reverse(str_split(str_pad($whole, 12, '0', STR_PAD_LEFT), 3));

        foreach ($chunks as $i => $chunk) {
            if ($chunk != '000') {
                $chunkWords = array();
                $hundreds = (int)substr($chunk, 0, 1);
                $tensUnits = (int)substr($chunk, 1, 2);

                if ($hundreds > 0) {
                    $chunkWords[] = ($hundreds == 1 ? '' : $units[$hundreds]) . ' cent';
                }

                if ($tensUnits > 0) {
                    if ($tensUnits < 10) {
                        $chunkWords[] = $units[$tensUnits];
                    } elseif ($tensUnits < 20) {
                        $chunkWords[] = $teens[$tensUnits - 10];
                    } else {
                        $ten = (int)($tensUnits / 10);
                        $unit = $tensUnits % 10;

                        if ($ten == 7 || $ten == 9) {
                            $ten--;
                            $unit += 10;
                        }

                        if ($unit == 0) {
                            $chunkWords[] = $tens[$ten];
                        } elseif ($unit == 1 && $ten != 8) {
                            $chunkWords[] = $tens[$ten] . '-et-un';
                        } else {
                            $chunkWords[] = $tens[$ten] . '-' . $units[$unit];
                        }
                    }
                }

                if ($i > 0) {
                    $chunkWords[] = $scales[$i] . ($i > 1 && $chunk > 1 ? 's' : '');
                }

                $words[] = implode(' ', $chunkWords);
            }
        }
    }

    $result = implode(' ', array_reverse($words));
    $result = str_replace('  ', ' ', $result);

    if ($decimal != '00') {
        $decimalWords = [];
        foreach (str_split($decimal) as $digit) {
            $decimalWords[] = $units[$digit];
        }
        $result .= ' virgule ' . implode(' ', $decimalWords);
    }

    return ucfirst($result) . ' dirhams';
}

function check_order_quantities($bd, $id) {
    $query = "SELECT SUM(QUANTITY) as SQ, SUM(DELIVERED_QUANTITY) as SDQ FROM BON_COMMANDE_DETAILS WHERE ID_COMMANDE = :id;";
    $request = $bd->prepare($query);
    $request->bindParam(":id", $id, PDO::PARAM_INT);
    
    try {
        $request->execute();
    } catch (PDOException $e) {
        $bd->rollback();
        echo ($e->getMessage());
        exit();
    }

    $result = $request->fetch(PDO::FETCH_ASSOC);
    $quantities = $result["SQ"];
    $delivered_quantities = $result["SDQ"];
    $diff = $quantities - $delivered_quantities;
    
    if ($diff == 0) {
        $query = "UPDATE BON_COMMANDE_HEADER SET STATE = 5 WHERE ID = :id"; // switch order state to delivered
        $request = $bd->prepare($query);
        $request->bindValue(":id", $id);
        try {
            $request->execute();
        } catch (PDOException $e) {
            $bd->rollback();
            echo ($e->getMessage());
            exit();
        }
    }
}

function process_delivery_check($session, $post, $bd) {
    $products_references = $post["references"];
    $products_quantities = $post["new_quantities"];
    $products_names = $post["names"];
    $products_prices = $post["prices"];
    $date = $post["date"];
    $payment_method = $post["payement"];
    $payment_reference = $post["payement_reference"] ?? '';
    $orderID = $session["order"]["orderID"];
    $TVA = 0.2;

    // Get order header
    $stmt = $bd->prepare("SELECT * FROM BON_COMMANDE_HEADER WHERE ID = ?");
    $stmt->execute([$orderID]);
    $orderHeader = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$orderHeader) {
        throw new Exception("Order header not found");
    }

    // Generate delivery number
    $stmt = $bd->prepare("SELECT COUNT(*) FROM BON_LIVRAISON_HEADER");
    $stmt->execute();
    $deliveryNumber = "BL" . ($stmt->fetchColumn() + 1);
    
    // Calculate totals
    $grandTotal = 0;
    $grandTVA = 0;
    $grandHT = 0;
    
    // Insert delivery header
    $stmt = $bd->prepare("INSERT INTO BON_LIVRAISON_HEADER 
                        (ID_BON, ID_COMMANDE, CLIENT_NAME, COMPANY_ICE, INVOICE_TYPE, DATE, TOTAL_PRICE_TTC, TOTAL_PRICE_HT, TVA) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $deliveryNumber,
        $orderID,
        $orderHeader['CLIENT_NAME'],
        $orderHeader['COMPANY_ICE'],
        $orderHeader['TYPE'],
        $date,
        0, // Will be updated later
        0, // Will be updated later
        0  // Will be updated later
    ]);
    $deliveryId = $bd->lastInsertId();
    
    // Process products
    for ($i = 0; $i < count($products_references); $i++) {
        $quantity = $products_quantities[$i];
        $unitPrice = $products_prices[$i];
        $totalPrice = $quantity * $unitPrice;
        $priceBeforeTax = $unitPrice / (1 + $TVA);
        $TVAamount = $unitPrice - $priceBeforeTax;
        
        $grandTotal += $totalPrice;
        $grandTVA += $TVAamount * $quantity;
        $grandHT += $priceBeforeTax * $quantity;
        
        // Insert delivery details
        $stmt = $bd->prepare("INSERT INTO BON_LIVRAISON_DETAILS 
                            (ID_BON, PRODUCT_ID, PRODUCT_NAME, QUANTITY, UNIT_PRICE_TTC, TOTAL_PRICE_TTC) 
                            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $deliveryId,
            $products_references[$i],
            $products_names[$i],
            $quantity,
            $unitPrice,
            $totalPrice
        ]);
        
        // Update order delivered quantities
        $stmt = $bd->prepare("UPDATE BON_COMMANDE_DETAILS 
                            SET DELIVERED_QUANTITY = DELIVERED_QUANTITY + ? 
                            WHERE ID_COMMANDE = ? AND PRODUCT_ID = ?");
        $stmt->execute([$quantity, $orderID, $products_references[$i]]);
    }
    
    // Update delivery with totals
    $stmt = $bd->prepare("UPDATE BON_LIVRAISON_HEADER 
                        SET TOTAL_PRICE_TTC = ?, TOTAL_PRICE_HT = ?, TVA = ? 
                        WHERE ID = ?");
    $stmt->execute([$grandTotal, $grandHT, $grandTVA, $deliveryId]);

    // Check if order is fully delivered
    check_order_quantities($bd, $orderID);

    // Generate PDFs
    generate_sell_invoice($bd, $deliveryId, $orderHeader, $products_references, $products_names, $products_quantities, $products_prices, $date, $payment_method, $payment_reference, $grandTotal, $grandHT, $grandTVA, $deliveryNumber);

    return $deliveryId;
}

function generate_sell_invoice($database, $deliveryId, $orderHeader, $references, $names, $quantities, $prices, $date, $payment_method, $payment_reference, $grandTotal, $grandHT, $grandTVA, $deliveryNumber) {
    // Generate invoice number
    $stmt = $database->prepare("SELECT INVOICE_NUMBER FROM SELL_INVOICE_HEADER ORDER BY INVOICE_NUMBER DESC");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $invoiceNumber = "FAC" . ($result ? intval(substr($result["INVOICE_NUMBER"], 3)) + 1 : 1);
    
    // Calculate earnings (adjust this based on your business logic)
    $earnings = $grandTotal - ($grandHT * 0.2);
    
    // Insert invoice header
    $stmt = $database->prepare("INSERT INTO SELL_INVOICE_HEADER 
                              (INVOICE_NUMBER, ID_BON_LIVRAISON, CLIENT_NAME, COMPANY_ICE, INVOICE_TYPE, 
                              PAYEMENT, PAYEMENT_REFERENCE, TOTAL_PRICE_TTC, TOTAL_PRICE_HT, TVA, EARNINGS, DATE) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $invoiceNumber,
        $deliveryId,
        $orderHeader['CLIENT_NAME'],
        $orderHeader['COMPANY_ICE'],
        $orderHeader['TYPE'],
        $payment_method,
        $payment_reference,
        $grandTotal,
        $grandHT,
        $grandTVA,
        $earnings,
        $date
    ]);
    $invoiceId = $database->lastInsertId();

    // Insert invoice details
    for ($i = 0; $i < count($references); $i++) {
        $stmt = $database->prepare("INSERT INTO SELL_INVOICE_DETAILS 
                                  (ID_INVOICE, PRODUCT_ID, PRODUCT_NAME, QUANTITY, UNIT_PRICE_TTC, TOTAL_PRICE_TTC) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $invoiceId,
            $references[$i],
            $names[$i],
            $quantities[$i],
            $prices[$i],
            $quantities[$i] * $prices[$i]
        ]);
    }

    // Generate Delivery Note PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 11);

        // Client information
        $pdf->SetFont('Arial', '', 12);
        $boxWidth = 100;
        $boxX = 100;
        $boxHeight = 32;
        $pdf->Rect($boxX, 20, $boxWidth, $boxHeight);

        $pdf->SetXY($boxX + 5, 22);
        $pdf->Cell(13, 6, 'Nom :', 0, 0);
        $pdf->MultiCell($boxWidth - 20, 6, $orderHeader['CLIENT_NAME'], 0);

        if (!empty($orderHeader['COMPANY_ICE'])) {
            $pdf->SetFont('Arial', '', 10);
            $currentY = $pdf->GetY();
            $pdf->SetXY($boxX + 5, $currentY);
            $pdf->Cell(10, 6, 'ICE :', 0, 0);
            $pdf->Cell(22, 6, $orderHeader['COMPANY_ICE'], 0, 1);
        }

        // Delivery title
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetXY(10, 46);
        $pdf->Cell(0, 10, 'BON DE LIVRAISON', 0, 1);

        // Number/date block
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetXY(10, 58);
        $pdf->Cell(30, 8, 'Numero', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Date', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 10);

        $pdf->SetX(10);
        $pdf->Cell(30, 8, $deliveryNumber, 1, 0, 'C');
        $pdf->Cell(30, 8, date('d/m/Y', strtotime($date)), 1, 1, 'C');

        // Products table
        $pdf->Ln(10);
        $maxRows = 17;
        $rowHeight = 8;

        // Table header
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(20, $rowHeight, 'QTE', 'LTRB', 0, 'C');
        $pdf->Cell(115, $rowHeight, 'DESIGNATION', 'TRB', 0, 'C');
        $pdf->Cell(20, $rowHeight, 'P.U TTC', 'TRB', 0, 'C');
        $pdf->Cell(35, $rowHeight, 'TOTAL TTC', 'TRB', 1, 'C');

        // Table rows
        $pdf->SetFont('Arial', '', 11);
        $rowCount = 0;

        for ($i = 0; $i < count($references); $i++) {
            if ($rowCount >= $maxRows) break;
            $pdf->Cell(20, $rowHeight, $quantities[$i], 'LR', 0, 'C');
            $pdf->Cell(115, $rowHeight, $names[$i], 'R', 0, 'L');
            $pdf->Cell(20, $rowHeight, number_format($prices[$i], 2), 'R', 0, 'R');
            $pdf->Cell(35, $rowHeight, number_format($quantities[$i] * $prices[$i], 2), 'R', 1, 'R');
            $rowCount++;
        }

        // Fill empty rows
        while ($rowCount < $maxRows) {
            $pdf->Cell(20, $rowHeight, '', 'LR', 0, 'C');
            $pdf->Cell(115, $rowHeight, '', 'R', 0, 'L');
            $pdf->Cell(20, $rowHeight, '', 'R', 0, 'R');
            $pdf->Cell(35, $rowHeight, '', 'R', 1, 'R');
            $rowCount++;
        }

        // Bottom border
        $pdf->Cell(20, 0, '', 'LBR');
        $pdf->Cell(115, 0, '', 'BR');
        $pdf->Cell(20, 0, '', 'BR');
        $pdf->Cell(35, 0, '', 'BR', 1);

        // Totals
        $pdf->Ln(10);
        $startY = $pdf->GetY();

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->SetXY(10, $startY);
        $pdf->MultiCell(120, 6, "La presente livraison est arretee a la somme de : \n" . numberToFrenchWords(number_format($grandTotal, 2)));

        $pdf->SetXY(140, $startY);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(30, 8, 'TOTAL HT', 1, 0, 'C');
        $pdf->Cell(30, 8, number_format($grandHT, 2), 1, 1, 'R');
        $pdf->SetX(140);
        $pdf->Cell(30, 8, 'TVA', 1, 0, 'C');
        $pdf->Cell(30, 8, number_format($grandTVA, 2), 1, 1, 'R');
        $pdf->SetX(140);
        $pdf->Cell(30, 8, 'TOTAL TTC', 1, 0, 'C');
        $pdf->Cell(30, 8, number_format($grandTotal, 2), 1, 1, 'R');

    // Save Delivery Note PDF
    $deliveryPdfPath = '../../../PDF/BL/' . $deliveryNumber . '.pdf';
    if (!file_exists('../../../PDF/BL')) {
        mkdir('../../../PDF/BL', 0777, true);
    }
    $pdf->Output('F', $deliveryPdfPath);

    // Generate Invoice PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 11);

// Client information (same as delivery note)
        $pdf->SetFont('Arial', '', 12);
        $boxWidth = 100;
        $boxX = 100;
        $boxHeight = 32;
        $pdf->Rect($boxX, 20, $boxWidth, $boxHeight);

        $pdf->SetXY($boxX + 5, 22);
        $pdf->Cell(13, 6, 'Nom :', 0, 0);
        $pdf->MultiCell($boxWidth - 20, 6, $orderHeader['CLIENT_NAME'], 0);

        if (!empty($orderHeader['COMPANY_ICE'])) {
            $pdf->SetFont('Arial', '', 10);
            $currentY = $pdf->GetY();
            $pdf->SetXY($boxX + 5, $currentY);
            $pdf->Cell(10, 6, 'ICE :', 0, 0);
            $pdf->Cell(22, 6, $orderHeader['COMPANY_ICE'], 0, 1);
        }

        // Invoice title
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetXY(10, 46);
        $pdf->Cell(0, 10, 'FACTURE', 0, 1);

        // Number/date block
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetXY(10, 58);
        $pdf->Cell(30, 8, 'Numero', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Date', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 10);

        $pdf->SetX(10);
        $pdf->Cell(30, 8, $invoiceNumber, 1, 0, 'C');
        $pdf->Cell(30, 8, date('d/m/Y', strtotime($date)), 1, 1, 'C');

        // Products table (same as delivery note)
        $pdf->Ln(10);
        $maxRows = 17;
        $rowHeight = 8;

        // Table header
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(20, $rowHeight, 'QTE', 'LTRB', 0, 'C');
        $pdf->Cell(115, $rowHeight, 'DESIGNATION', 'TRB', 0, 'C');
        $pdf->Cell(20, $rowHeight, 'P.U TTC', 'TRB', 0, 'C');
        $pdf->Cell(35, $rowHeight, 'TOTAL TTC', 'TRB', 1, 'C');

        // Table rows
        $pdf->SetFont('Arial', '', 11);
        $rowCount = 0;

        for ($i = 0; $i < count($references); $i++) {
            if ($rowCount >= $maxRows) break;
            $pdf->Cell(20, $rowHeight, $quantities[$i], 'LR', 0, 'C');
            $pdf->Cell(115, $rowHeight, $names[$i], 'R', 0, 'L');
            $pdf->Cell(20, $rowHeight, number_format($prices[$i], 2), 'R', 0, 'R');
            $pdf->Cell(35, $rowHeight, number_format($quantities[$i] * $prices[$i], 2), 'R', 1, 'R');
            $rowCount++;
        }

        // Fill empty rows
        while ($rowCount < $maxRows) {
            $pdf->Cell(20, $rowHeight, '', 'LR', 0, 'C');
            $pdf->Cell(115, $rowHeight, '', 'R', 0, 'L');
            $pdf->Cell(20, $rowHeight, '', 'R', 0, 'R');
            $pdf->Cell(35, $rowHeight, '', 'R', 1, 'R');
            $rowCount++;
        }

        // Bottom border
        $pdf->Cell(20, 0, '', 'LBR');
        $pdf->Cell(115, 0, '', 'BR');
        $pdf->Cell(20, 0, '', 'BR');
        $pdf->Cell(35, 0, '', 'BR', 1);

        // Totals
        $pdf->Ln(10);
        $startY = $pdf->GetY();

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->SetXY(10, $startY);
        $pdf->MultiCell(120, 6, "La presente facture est arretee a la somme de : \n" . numberToFrenchWords(number_format($grandTotal, 2)));

        $pdf->SetXY(140, $startY);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(30, 8, 'TOTAL HT', 1, 0, 'C');
        $pdf->Cell(30, 8, number_format($grandHT, 2), 1, 1, 'R');
        $pdf->SetX(140);
        $pdf->Cell(30, 8, 'TVA', 1, 0, 'C');
        $pdf->Cell(30, 8, number_format($grandTVA, 2), 1, 1, 'R');
        $pdf->SetX(140);
        $pdf->Cell(30, 8, 'TOTAL TTC', 1, 0, 'C');
        $pdf->Cell(30, 8, number_format($grandTotal, 2), 1, 1, 'R');

        // Payment info
        $paymentMethods = [
            '1' => 'Espece',
            '2' => 'Cheque',
            '3' => 'Effet'
        ];
        
        $pdf->SetY($pdf->GetY() + 5);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(40, 8, 'Paye par:', 0, 0);
        $pdf->Cell(0, 8, $paymentMethods[$payment_method], 0, 1);
        
        if ($payment_method !== '1' && !empty($payment_reference)) {
            $pdf->Cell(40, 8, 'Numero:', 0, 0);
            $pdf->Cell(0, 8, $payment_reference, 0, 1);
        }


    // Save Invoice PDF
    $invoicePdfPath = '../../../PDF/Factures/' . $invoiceNumber . '.pdf';
    if (!file_exists('../../../PDF/Factures')) {
        mkdir('../../../PDF/Factures', 0777, true);
    }
    $pdf->Output('F', $invoicePdfPath);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['validate_delivery'])) {
    try {
        $bd->beginTransaction();
        
        // Process the delivery check
        $deliveryId = process_delivery_check($_SESSION, $_POST, $bd);
        
        $bd->commit();
        unset($_SESSION['order']);
        header("Location: ../show_orders.php");
        exit();
        
    } catch (Exception $e) {
        $bd->rollBack();
        header("Location: create_delivery_check.php?error=" . urlencode("Error processing delivery: " . $e->getMessage()));
        exit();
    }
}