<pre>
<?php
    session_start();
    require("../libs/fpdf/fpdf.php"); // Include FPDF library

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["quotationID"])){
        $quotationID = $_GET["quotationID"];
        $address = "";
        $date = date("Y-m-d");

        require("../../includes/db_connection.php");
        $bd->beginTransaction();
        
        // Process confirmation and generate PDF
        process_quotation_confirmation($bd, $quotationID, $date, $address);

        try{
            $bd->commit();
            header("Location: show_quotations.php?message=Quotation+confirmed+and+order+created");
        }catch (PDOException $e){
            $bd->rollBack();
            header("Location: show_quotations.php?error=".urlencode("Error: ".$e->getMessage()));
            exit();
        }
    }

    function process_quotation_confirmation($database, $id, $date, $address){
        // Get quotation header
        $query = "SELECT * FROM DEVIS_HEADER WHERE ID = :id;";
        $request = $database->prepare($query);
        $request->bindValue(":id", $id);
        $request->execute();
        $quotation_header = $request->fetch(PDO::FETCH_ASSOC);

        // Get quotation details
        $query = "SELECT * FROM DEVIS_DETAILS WHERE ID_DEVIS = :id;";
        $request = $database->prepare($query);
        $request->bindValue(":id", $id);
        $request->execute();
        $quotation_details = $request->fetchAll(PDO::FETCH_ASSOC);

        // Get next order number
        $query = "SELECT ID_COMMANDE FROM BON_COMMANDE_HEADER ORDER BY ID_COMMANDE DESC LIMIT 1;";
        $request = $database->prepare($query);
        $request->execute();
        $result = $request->fetchColumn(0);
        
        $order_number = 1; // Default if no orders exist
        if ($result) {
            var_dump($result);
            $splited_result = str_split($result, 3);
            echo
            $order_number = intval(str_split($result  , 3)[1]) + 1;
        }
        $order_number_string = "CMD".$order_number;
        echo $order_number;
        echo $order_number_string;
        if ($quotation_header && $quotation_details){
            // Determine order type
            $sell_type = ($quotation_header["COMPANY_ICE"] != "") ? "Company" : "Personal";
            
            // Insert order header
            $query = "
                INSERT INTO BON_COMMANDE_HEADER(
                    ID_COMMANDE,
                    CLIENT_ID,
                    CLIENT_NAME,
                    COMPANY_ICE,
                    ADDRESSE,
                    TYPE,
                    DATE,
                    TOTAL_PRICE_TTC,
                    STATE
                )
                VALUES(
                    :order_number,
                    :client_id,
                    :client_name,
                    :company_ICE,
                    :address,
                    :invoice_type,
                    :creation_date,
                    :total_ttc,
                    :state
                );
            ";

            $request = $database->prepare($query);
            $request->execute([
                ":order_number" => $order_number_string,
                ":client_id" => $quotation_header["CLIENT_ID"],
                ":client_name" => $quotation_header["CLIENT_NAME"],
                ":company_ICE" => $quotation_header["COMPANY_ICE"],
                ":address" => $address,
                ":invoice_type" => $sell_type,
                ":creation_date" => $date,
                ":total_ttc" => $quotation_header["TOTAL_PRICE_TTC"],
                ":state" => 1
            ]);

            $orderID = $database->lastInsertId();

            // Insert order details and get product names for PDF
            $product_names = [];
            foreach ($quotation_details as $detail){
                $query = "
                    INSERT INTO BON_COMMANDE_DETAILS(
                        ID_COMMANDE, 
                        COMMANDE_NUMBER, 
                        PRODUCT_ID, 
                        QUANTITY, 
                        UNIT_PRICE_TTC, 
                        TOTAL_PRICE_TTC
                    ) VALUES(
                        :id_commande,
                        :num,
                        :p_id,
                        :quantity,
                        :unit_price_ttc,
                        :total_price_ttc
                    );
                ";

                $request = $database->prepare($query);
                $request->execute([
                    ":id_commande" => $orderID,
                    ":num" => $order_number_string,
                    ":p_id" => $detail["PRODUCT_ID"],
                    ":quantity" => $detail["QUANTITY"],
                    ":unit_price_ttc" => $detail["UNIT_PRICE_TTC"],
                    ":total_price_ttc" => $detail["TOTAL_PRICE_TTC"]
                ]);

                // Get product name for PDF
                $product_query = "SELECT PRODUCT_NAME FROM PRODUCT WHERE REFERENCE = :ref";
                $product_request = $database->prepare($product_query);
                $product_request->execute([":ref" => $detail["PRODUCT_ID"]]);
                $product = $product_request->fetch(PDO::FETCH_ASSOC);
                $product_names[$detail["PRODUCT_ID"]] = $product["PRODUCT_NAME"];
            }

            // Generate PDF for the order
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
            $pdf->MultiCell($boxWidth - 20, 6, $quotation_header["CLIENT_NAME"], 0);

            if (!empty($quotation_header["COMPANY_ICE"])) {
                $pdf->SetFont('Arial', '', 10);
                $currentY = $pdf->GetY();
                $pdf->SetXY($boxX + 5, $currentY);
                $pdf->Cell(10, 6, 'ICE :', 0, 0);
                $pdf->Cell(22, 6, $quotation_header["COMPANY_ICE"], 0, 1);
            }

            // Order title
            $pdf->SetFont('Arial', 'B', 20);
            $pdf->SetXY(10, 46);
            $pdf->Cell(0, 10, 'BON DE COMMANDE', 0, 1);

            // Number/date block
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetXY(10, 58);
            $pdf->Cell(30, 8, 'Numéro', 1, 0, 'C');
            $pdf->Cell(30, 8, 'Date', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 10);

            $pdf->SetX(10);
            $pdf->Cell(30, 8, $order_number_string, 1, 0, 'C');
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

            foreach ($quotation_details as $detail) {
                if ($rowCount >= $maxRows) break;
                
                $pdf->Cell(20, $rowHeight, $detail["QUANTITY"], 'LR', 0, 'C');
                $pdf->Cell(115, $rowHeight, $product_names[$detail["PRODUCT_ID"]], 'R', 0, 'L');
                $pdf->Cell(20, $rowHeight, number_format($detail["UNIT_PRICE_TTC"], 2), 'R', 0, 'R');
                $pdf->Cell(35, $rowHeight, number_format($detail["TOTAL_PRICE_TTC"], 2), 'R', 1, 'R');
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
            $pdf->Cell(30, 8, number_format($quotation_header["TOTAL_PRICE_HT"], 2), 1, 1, 'R');
            $pdf->SetX(140);
            $pdf->Cell(30, 8, 'TVA', 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($quotation_header["TVA"], 2), 1, 1, 'R');
            $pdf->SetX(140);
            $pdf->Cell(30, 8, 'TOTAL TTC', 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($quotation_header["TOTAL_PRICE_TTC"], 2), 1, 1, 'R');

            // Save PDF
            $pdfPath = '../../Commandes/' . $order_number_string . '.pdf';
            if (!file_exists('../../Commandes')) {
                mkdir('../../Commandes', 0777, true);
            }
            $pdf->Output('F', $pdfPath);

            // Delete the quotation after successful conversion to order
            $query = "DELETE FROM DEVIS_HEADER WHERE ID = :id";
            $request = $database->prepare($query);
            $request->execute([":id" => $quotation_header["ID"]]);
        }
    }
?>