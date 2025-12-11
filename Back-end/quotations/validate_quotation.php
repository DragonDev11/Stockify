<?php
session_start();
require("../libs/fpdf/fpdf.php"); // Include FPDF library

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION["quotation"])) {
        try {
            require("../../includes/db_connection.php");

            $bd->beginTransaction();

            // Process quotation and generate PDF
            process_quotation($bd, $_SESSION);
            try {
                $bd->commit();
            } catch (PDOException $e) {
                $bd->rollBack();
                echo $e->getMessage();
                //header("Location: quotation_summary.php?error=" . urlencode("Database error: " . $e->getMessage()));
                exit();
            }

            unset($_SESSION["quotation"]);
            header("Location: show_quotations.php");
            exit();
        } catch (PDOException $e) {
            echo $e->getMessage();
            //header("Location: quotation_summary.php?error=" . urlencode("Database error: " . $e->getMessage()));
            exit();
        }
    } else {
        header("Location: show_quotations.php?error=" . urlencode("Session expired"));
        exit();
    }
} else {
    header("Location: show_quotations.php?error=" . urlencode("Invalid request method"));
    exit();
}

function process_quotation($bd, $session)
{
    // Get next quotation number
    $query = "SELECT DEVIS_NUMBER FROM DEVIS_HEADER ORDER BY DEVIS_NUMBER DESC LIMIT 1;";
    $request = $bd->prepare($query);
    $request->execute();
    $result = $request->fetchColumn(0);

    $number = 1; // Default if no quotations exist
    var_dump($result);
    if ($result) {
        $splited_result = str_split($result, 3);
        var_dump($splited_result);
        $number = intval(str_split($result, 3)[1]) + 1;
    }
    $quotation_number_string = "DEV" . $number;

    // Get client and quotation data
    $sell_type = $session['quotation']['sell_type'];
    $client_name = $session['quotation']['client_name'];
    $company_ICE = ($sell_type === 'Company') ? $session['quotation']['company_ICE'] : "";
    $date = $session['quotation']['creation_date'];
    $address = $session['quotation']['address'];
    $selected_products = $session['quotation']['products'];
    $placeholders = implode(',', $selected_products);

    // Get products info
    $query = "SELECT ID, REFERENCE, PRODUCT_NAME, PRICE, QUANTITY FROM PRODUCT WHERE ID IN ($placeholders)";
    $request = $bd->prepare($query);
    $request->execute();
    $products = $request->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $total_price_ttc = 0.0;
    $tva = 0.2;

    foreach ($products as $product) {
        $ref = $product['ID'];
        $unitPriceTTC = floatval($session['quotation']['prices'][$ref]);
        $quantity = intval($session['quotation']['quantities'][$ref]);
        $total_price_ttc += $unitPriceTTC * $quantity;
    }

    $total_price_ht = $total_price_ttc / (1 + $tva);
    $total_price_tva = $total_price_ttc - $total_price_ht;

    // Handle client (same as your existing code)
    // Handle client
    $clientID = 0;
    if (!empty($client_name)) {
        $query = "SELECT * FROM CLIENT WHERE CLIENTNAME = :client_name";
        $params = [":client_name" => $client_name];

        // Add ICE to query if it's not empty
        if (!empty($company_ICE)) {
            $query .= " AND ICE = :ice";
            $params[":ice"] = $company_ICE;
        } else {
            $query .= " AND (ICE IS NULL OR ICE = '')";
        }

        $request = $bd->prepare($query);
        $request->execute($params);
        $result = $request->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            $query = "INSERT INTO CLIENT(CLIENTNAME, ICE) VALUES(:name, :ice)";
            $request = $bd->prepare($query);
            $request->bindValue(":name", $client_name);
            // Handle empty ICE properly
            $request->bindValue(":ice", !empty($company_ICE) ? $company_ICE : null);
            $request->execute();
            $clientID = $bd->lastInsertId();
        } else {
            $clientID = $result["ID"];
        }
    }

    // Insert quotation header
    $query = "INSERT INTO DEVIS_HEADER(
            DEVIS_NUMBER,
            CLIENT_ID,
            CLIENT_NAME,
            COMPANY_ICE,
            TOTAL_PRICE_TTC,
            TOTAL_PRICE_HT,
            TVA,
            `DATE`
        ) VALUES(
            :quotation_number,
            :client_id,
            :client_name,
            :company_ICE,
            :total_ttc,
            :total_ht,
            :total_tva,
            :date
        )";

    $request = $bd->prepare($query);
    $request->execute([
        ":quotation_number" => $quotation_number_string,
        ":client_id" => $clientID,
        ":client_name" => $client_name,
        ":company_ICE" => $company_ICE,
        ":total_ttc" => $total_price_ttc,
        ":total_ht" => $total_price_ht,
        ":total_tva" => $total_price_tva,
        ":date" => $date
    ]);

    $quotationID = $bd->lastInsertId();

    // Insert quotation details
    foreach ($products as $product) {
        $ref = $product['ID'];
        $p_id = $product['REFERENCE'];
        $p_name = $product['PRODUCT_NAME'];
        $unitPriceTTC = floatval($session['quotation']['prices'][$ref]);
        $quantity = intval($session['quotation']['quantities'][$ref]);
        $totalPriceTTC = $unitPriceTTC * $quantity;

        $query = "
            INSERT INTO DEVIS_DETAILS(ID_DEVIS, PRODUCT_ID, QUANTITY, UNIT_PRICE_TTC, TOTAL_PRICE_TTC)    
            VALUES(
                :id_devis,
                :product_id,
                :quantity,
                :unit_price_ttc,
                :total_price_ttc
            )";

        $request = $bd->prepare($query);
        $request->execute([
            ":id_devis" => $quotationID,
            ":product_id" => $p_id,
            ":quantity" => $quantity,
            ":unit_price_ttc" => $unitPriceTTC,
            ":total_price_ttc" => $totalPriceTTC
        ]);
    }

    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 11);

    // Client information box
    $pdf->SetFont('Arial', '', 12);
    $boxWidth = 100;
    $boxX = 100;
    $boxHeight = 32;
    $pdf->Rect($boxX, 20, $boxWidth, $boxHeight);

    $pdf->SetXY($boxX + 5, 22);
    $pdf->Cell(13, 6, 'Nom :', 0, 0);
    $pdf->MultiCell($boxWidth - 20, 6, $client_name, 0);

    if (!empty($company_ICE)) {
        $pdf->SetFont('Arial', '', 10);
        $currentY = $pdf->GetY();
        $pdf->SetXY($boxX + 5, $currentY);
        $pdf->Cell(10, 6, 'ICE :', 0, 0);
        $pdf->Cell(22, 6, $company_ICE, 0, 1);
    }

    // Quotation title
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetXY(10, 46);
    $pdf->Cell(0, 10, 'DEVIS', 0, 1);

    // Number/date block
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetXY(10, 58);
    $pdf->Cell(30, 8, 'Numero', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Date', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 10);

    $pdf->SetX(10);
    $pdf->Cell(30, 8, $quotation_number_string, 1, 0, 'C');
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

    foreach ($products as $product) {
        if ($rowCount >= $maxRows) break;
        $ref = $product['ID'];
        $quantity = intval($session['quotation']['quantities'][$ref]);
        $unitPriceTTC = floatval($session['quotation']['prices'][$ref]);

        $pdf->Cell(20, $rowHeight, $quantity, 'LR', 0, 'C');
        $pdf->Cell(115, $rowHeight, $product['PRODUCT_NAME'], 'R', 0, 'L');
        $pdf->Cell(20, $rowHeight, number_format($unitPriceTTC, 2), 'R', 0, 'R');
        $pdf->Cell(35, $rowHeight, number_format($quantity * $unitPriceTTC, 2), 'R', 1, 'R');
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

    $pdf->SetXY(140, $startY);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 8, 'TOTAL HT', 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($total_price_ht, 2), 1, 1, 'R');
    $pdf->SetX(140);
    $pdf->Cell(30, 8, 'TVA', 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($total_price_tva, 2), 1, 1, 'R');
    $pdf->SetX(140);
    $pdf->Cell(30, 8, 'TOTAL TTC', 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($total_price_ttc, 2), 1, 1, 'R');

    // Save PDF
    $pdfPath = '../../PDF/Devis/' . $quotation_number_string . '.pdf';
    if (!file_exists('../../PDF/Devis')) {
        mkdir('../../PDF/Devis', 0777, true);
    }
    $pdf->Output('F', $pdfPath);
}
