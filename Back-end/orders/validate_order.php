<?php
session_start();
require "../../includes/db_connection.php";
require "../libs/fpdf/fpdf.php";

// Function to convert number to French words
function numberToFrenchWords($number)
{
    $units = [
        "",
        "un",
        "deux",
        "trois",
        "quatre",
        "cinq",
        "six",
        "sept",
        "huit",
        "neuf",
    ];
    $teens = [
        "dix",
        "onze",
        "douze",
        "treize",
        "quatorze",
        "quinze",
        "seize",
        "dix-sept",
        "dix-huit",
        "dix-neuf",
    ];
    $tens = [
        "",
        "dix",
        "vingt",
        "trente",
        "quarante",
        "cinquante",
        "soixante",
        "soixante-dix",
        "quatre-vingt",
        "quatre-vingt-dix",
    ];
    $thousands = ["", "mille", "million", "milliard"];

    $words = [];
    $number = str_replace(",", "", $number);
    $parts = explode(".", $number);
    $whole = (int) $parts[0];
    $decimal = isset($parts[1]) ? substr($parts[1], 0, 2) : "00";

    if ($whole == 0) {
        $words[] = "zero";
    } else {
        $chunks = array_reverse(
            str_split(str_pad($whole, 12, "0", STR_PAD_LEFT), 3),
        );

        foreach ($chunks as $i => $chunk) {
            if ($chunk != "000") {
                $chunkWords = [];
                $hundreds = (int) substr($chunk, 0, 1);
                $tensUnits = (int) substr($chunk, 1, 2);

                if ($hundreds > 0) {
                    $chunkWords[] =
                        ($hundreds == 1 ? "" : $units[$hundreds]) . " cent";
                }

                if ($tensUnits > 0) {
                    if ($tensUnits < 10) {
                        $chunkWords[] = $units[$tensUnits];
                    } elseif ($tensUnits < 20) {
                        $chunkWords[] = $teens[$tensUnits - 10];
                    } else {
                        $ten = (int) ($tensUnits / 10);
                        $unit = $tensUnits % 10;

                        if ($ten == 7 || $ten == 9) {
                            $ten--;
                            $unit += 10;
                        }

                        if ($unit == 0) {
                            $chunkWords[] = $tens[$ten];
                        } elseif ($unit == 1 && $ten != 8) {
                            $chunkWords[] = $tens[$ten] . "-et-un";
                        } else {
                            $chunkWords[] = $tens[$ten] . "-" . $units[$unit];
                        }
                    }
                }

                if ($i > 0) {
                    $chunkWords[] =
                        $thousands[$i] . ($i > 1 && $chunk > 1 ? "s" : "");
                }

                $words[] = implode(" ", $chunkWords);
            }
        }
    }

    $result = implode(" ", array_reverse($words));
    $result = str_replace("  ", " ", $result);

    if ($decimal != "00") {
        $result .= " virgule " . numberToFrenchWords($decimal);
    }

    return $result . " dirhams";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION["order"])) {
        try {
            $bd->beginTransaction();

            if (
                isset($_SESSION["order"]["state"]) &&
                $_SESSION["order"]["state"] == "modifying_order"
            ) {
                process_order_modifications($bd, $_SESSION);
            } else {
                process_order($bd, $_SESSION);
            }

            $bd->commit();
            unset($_SESSION["order"]);
            header("Location: show_orders.php");
            exit();
        } catch (PDOException $e) {
            $bd->rollBack();
            die("Error processing order: " . $e->getMessage());
        }
    } else {
        die("No session has been set");
    }
} else {
    die("Invalid request method");
}

function process_order($bd, $session)
{
    // Generate order number
    $query =
        "SELECT ID_COMMANDE FROM BON_COMMANDE_HEADER ORDER BY ID_COMMANDE DESC LIMIT 1;";
    $request = $bd->prepare($query);
    $request->execute();
    $result = $request->fetch(PDO::FETCH_ASSOC);

    $order_number = 1;
    if ($result) {
        $splited_result = str_split($result["ID_COMMANDE"], 3);
        $order_number = intval($splited_result[1]) + 1;
    }
    $order_number_string = "CMD" . $order_number;

    // Get order data
    $sell_type = $session["order"]["sell_type"];
    $client_name = $session["order"]["client_name"];
    $company_ICE =
        $sell_type === "Company" ? $session["order"]["company_ICE"] : null;
    $date = $session["order"]["creation_date"];
    $client_address = $session["order"]["client_address"] ?? "";
    $state = 1;

    // Get products
    $selected_products = array_values($session["order"]["products"]);
    $placeholders = implode(",", $selected_products);
    $query = "SELECT ID, REFERENCE, PRODUCT_NAME, PRICE FROM PRODUCT WHERE ID IN ($placeholders)";
    $request = $bd->prepare($query);
    $request->execute();
    $products = $request->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $TVA = isset($GENERAL_VARIABLES["TVA_AMOUNT"])
        ? $GENERAL_VARIABLES["TVA_AMOUNT"] / 100.0
        : 0.2; // Default to 20% if not set
    $grandTotal = 0.0;
    $grandTVA = 0.0;
    $grandHT = 0.0;

    // Get or create client
    $clientID = null;
    if (!empty($client_name)) {
        $stmt = $bd->prepare("SELECT ID FROM CLIENT WHERE CLIENTNAME = ?");
        $stmt->execute([$client_name]);
        $clientID = $stmt->fetchColumn();

        if (!$clientID) {
            $stmt = $bd->prepare(
                "INSERT INTO CLIENT (CLIENTNAME, ICE) VALUES (?, ?)",
            );
            $stmt->execute([$client_name, $company_ICE]);
            $clientID = $bd->lastInsertId();
        }
    }

    // Insert order header
    $stmt = $bd->prepare("INSERT INTO BON_COMMANDE_HEADER
                        (ID_COMMANDE, CLIENT_ID, CLIENT_NAME, COMPANY_ICE, ADDRESSE, TYPE, DATE, STATE)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $order_number_string,
        $clientID,
        $client_name,
        $company_ICE,
        $client_address,
        $sell_type,
        $date,
        $state,
    ]);
    $orderId = $bd->lastInsertId();

    // Insert order details and calculate totals
    foreach ($products as $product) {
        $ref = $product["ID"];
        $unitPriceTTC = floatval($session["order"]["prices"][$ref]);
        $quantity = intval($session["order"]["quantities"][$ref]);

        $priceBeforeTax = $unitPriceTTC / (1 + $TVA);
        $TVAamount = $unitPriceTTC - $priceBeforeTax;
        $totalPriceTTC = $unitPriceTTC * $quantity;
        $totalTVA = $TVAamount * $quantity;

        $grandTotal += $totalPriceTTC;
        $grandTVA += $totalTVA;
        $grandHT += $priceBeforeTax * $quantity;

        $stmt = $bd->prepare("INSERT INTO BON_COMMANDE_DETAILS
                            (ID_COMMANDE, COMMANDE_NUMBER, PRODUCT_ID, QUANTITY, UNIT_PRICE_TTC, TOTAL_PRICE_TTC)
                            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $orderId,
            $order_number_string,
            $product["REFERENCE"],
            $quantity,
            $unitPriceTTC,
            $totalPriceTTC,
        ]);
    }

    // Update order with totals
    $stmt = $bd->prepare("UPDATE BON_COMMANDE_HEADER
                        SET TOTAL_PRICE_TTC = ?
                        WHERE ID = ?");
    $stmt->execute([$grandTotal, $orderId]);

    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont("Arial", "", 11);

    // Client info box
    $pdf->SetFont("Arial", "", 12);
    $address = !empty($client_address) ? $client_address : false;
    $boxHeight = $address
        ? 20 + count(explode("\n", wordwrap($address, 40, "\n"))) * 5
        : 32;
    $boxWidth = 100;
    $boxX = 100;
    $pdf->Rect($boxX, 20, $boxWidth, $boxHeight);

    $pdf->SetXY($boxX + 5, 22);
    $pdf->Cell(13, 6, "Nom :", 0, 0);
    $pdf->MultiCell($boxWidth - 20, 6, $client_name, 0);

    if ($address) {
        $pdf->SetFont("Arial", "", 10);
        $currentY = $pdf->GetY();
        $pdf->SetXY($boxX + 5, $currentY);
        $pdf->Cell(18, 6, "Adresse :", 0, 0);
        $pdf->MultiCell($boxWidth - 20, 5, $address, 0);
    }

    if (!empty($company_ICE)) {
        $pdf->SetFont("Arial", "", 10);
        $currentY = $pdf->GetY();
        $pdf->SetXY($boxX + 5, $currentY);
        $pdf->Cell(10, 6, "ICE :", 0, 0);
        $pdf->Cell(22, 6, $company_ICE, 0, 1);
    }

    // Order title
    $pdf->SetFont("Arial", "B", 20);
    $pdf->SetXY(10, 46);
    $pdf->Cell(0, 10, "BON DE COMMANDE", 0, 1);

    // Number/date
    $pdf->SetFont("Arial", "", 11);
    $pdf->SetXY(10, 58);
    $pdf->Cell(30, 8, "Numero", 1, 0, "C");
    $pdf->Cell(30, 8, "Date", 1, 1, "C");
    $pdf->SetFont("Arial", "", 10);
    $pdf->SetX(10);
    $pdf->Cell(30, 8, $order_number_string, 1, 0, "C");
    $pdf->Cell(30, 8, date("d/m/Y", strtotime($date)), 1, 1, "C");

    // Products table
    $pdf->Ln(10);
    $maxRows = 17;
    $rowHeight = 8;

    $pdf->SetFont("Arial", "B", 11);
    $pdf->Cell(20, $rowHeight, "QTE", "LTRB", 0, "C");
    $pdf->Cell(115, $rowHeight, "DESIGNATION", "TRB", 0, "C");
    $pdf->Cell(20, $rowHeight, "P.U TTC", "TRB", 0, "C");
    $pdf->Cell(35, $rowHeight, "TOTAL TTC", "TRB", 1, "C");

    $pdf->SetFont("Arial", "", 11);
    $rowCount = 0;
    foreach ($products as $product) {
        $ref = $product["ID"];
        $quantity = intval($session["order"]["quantities"][$ref]);
        $unitPriceTTC = floatval($session["order"]["prices"][$ref]);
        $totalPriceTTC = $unitPriceTTC * $quantity;

        if ($rowCount >= $maxRows) {
            break;
        }
        $pdf->Cell(20, $rowHeight, $quantity, "LR", 0, "C");
        $pdf->Cell(115, $rowHeight, $product["PRODUCT_NAME"], "R", 0, "L");
        $pdf->Cell(
            20,
            $rowHeight,
            number_format($unitPriceTTC, 2),
            "R",
            0,
            "R",
        );
        $pdf->Cell(
            35,
            $rowHeight,
            number_format($totalPriceTTC, 2),
            "R",
            1,
            "R",
        );
        $rowCount++;
    }

    while ($rowCount < $maxRows) {
        $pdf->Cell(20, $rowHeight, "", "LR", 0, "C");
        $pdf->Cell(115, $rowHeight, "", "R", 0, "L");
        $pdf->Cell(20, $rowHeight, "", "R", 0, "R");
        $pdf->Cell(35, $rowHeight, "", "R", 1, "R");
        $rowCount++;
    }

    $pdf->Cell(20, 0, "", "LBR");
    $pdf->Cell(115, 0, "", "BR");
    $pdf->Cell(20, 0, "", "BR");
    $pdf->Cell(35, 0, "", "BR", 1);

    // Totals
    $pdf->Ln(10);
    $startY = $pdf->GetY();

    $pdf->Ln(5);
    $pdf->SetFont("Arial", "I", 11);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(
        120,
        6,
        "La presente commande est arretee a la somme de : \n" .
            numberToFrenchWords(number_format($grandTotal, 2)),
    );

    $pdf->SetXY(140, $startY);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont("Arial", "", 12);
    $pdf->Cell(30, 8, "TOTAL HT", 1, 0, "C");
    $pdf->Cell(30, 8, number_format($grandHT, 2), 1, 1, "R");
    $pdf->SetX(140);
    $pdf->Cell(30, 8, "TVA", 1, 0, "C");
    $pdf->Cell(30, 8, number_format($grandTVA, 2), 1, 1, "R");
    $pdf->SetX(140);
    $pdf->Cell(30, 8, "TOTAL TTC", 1, 0, "C");
    $pdf->Cell(30, 8, number_format($grandTotal, 2), 1, 1, "R");

    // Save PDF
    $pdfPath = "../../PDF/Commandes/" . $order_number_string . ".pdf";
    if (!file_exists("../../PDF/Commandes")) {
        mkdir("../../PDF/Commandes", 0777, true);
    }
    $pdf->Output("F", $pdfPath);
}

function process_order_modifications($bd, $session)
{
    $query = "SELECT ID_COMMANDE FROM BON_COMMANDE_HEADER WHERE ID=:id;";
    $request = $bd->prepare($query);
    $request->bindValue(":id", $session["order"]["orderID"]);

    try {
        $request->execute();
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    $order_number = $request->fetchColumn(0);

    $sell_type = $session["order"]["sell_type"];
    $client_name = $session["order"]["client_name"];
    $company_ICE =
        $sell_type === "Company" ? $session["order"]["company_ICE"] : null;
    $date = $session["order"]["creation_date"];

    $selected_products = array_values($session["order"]["products"]);
    $placeholders = implode(",", $selected_products);

    $query = "SELECT ID, REFERENCE, PRODUCT_NAME, PRICE FROM PRODUCT WHERE ID IN ($placeholders)";
    $request = $bd->prepare($query);
    $request->execute();

    $products = $request->fetchAll(PDO::FETCH_ASSOC);

    $TVA = isset($GENERAL_VARIABLES["TVA_AMOUNT"])
        ? $GENERAL_VARIABLES["TVA_AMOUNT"] / 100.0
        : 0.2; // Default to 20% if not set
    $grandTotal = 0.0;
    $grandTVA = 0.0;

    foreach ($products as $product) {
        $ref = $product["ID"];
        $unitPriceTTC = floatval($session["order"]["prices"][$ref]);
        $quantity = intval($session["order"]["quantities"][$ref]);
        $priceBeforeTax = $unitPriceTTC / (1 + $TVA);
        $TVAamount = $unitPriceTTC - $priceBeforeTax;
        $totalPriceTTC = $unitPriceTTC * $quantity;
        $totalTVA = $TVAamount * $quantity;

        $grandTotal += $totalPriceTTC;
        $grandTVA += $totalTVA;
    }

    $query =
        "UPDATE BON_COMMANDE_HEADER SET TOTAL_PRICE_TTC = :total_ttc WHERE ID=:id;";
    $request = $bd->prepare($query);
    $request->bindValue(":total_ttc", $grandTotal);
    $request->bindValue(":id", $session["order"]["orderID"]);

    try {
        $request->execute();
    } catch (PDOException $e) {
        $bd->rollBack();
        echo $e->getMessage();
    }

    $query = "DELETE FROM BON_COMMANDE_DETAILS WHERE ID_COMMANDE = :id;";
    $request = $bd->prepare($query);
    $request->bindValue(":id", $session["order"]["orderID"]);

    try {
        $request->execute();
    } catch (PDOException $e) {
        $bd->rollBack();
        echo "ERROR:" . $e->getMessage();
    }

    foreach ($products as $product) {
        $ref = $product["ID"];
        $p_id = $product["REFERENCE"];
        $unitPriceTTC = floatval($session["order"]["prices"][$ref]);
        $quantity = intval($session["order"]["quantities"][$ref]);
        $totalPriceTTC = $unitPriceTTC * $quantity;

        $query = "INSERT INTO BON_COMMANDE_DETAILS(ID_COMMANDE, COMMANDE_NUMBER, PRODUCT_ID, QUANTITY, UNIT_PRICE_TTC, TOTAL_PRICE_TTC)
                VALUES(:id_commande, :num, :p_id, :quantity, :unit_price_ttc, :total_price_ttc);";

        $request = $bd->prepare($query);
        $request->bindValue(":id_commande", $session["order"]["orderID"]);
        $request->bindValue(":num", $order_number);
        $request->bindValue(":p_id", $p_id);
        $request->bindValue(":quantity", $quantity);
        $request->bindValue(":unit_price_ttc", $unitPriceTTC);
        $request->bindValue(":total_price_ttc", $totalPriceTTC);

        try {
            $request->execute();
        } catch (PDOException $e) {
            $bd->rollBack();
            echo $e->getMessage();
        }
    }

    foreach ($products as $product) {
        $ref = $product["ID"];
        $quantity = intval($session["order"]["quantities"][$ref]);

        $query =
            "UPDATE PRODUCT SET QUANTITY = QUANTITY - :quantity WHERE ID = :ref_p;";
        $request = $bd->prepare($query);
        $request->bindValue(":quantity", $quantity);
        $request->bindValue(":ref_p", $ref);

        try {
            $request->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
?>
