<?php
    session_start();
    require("../../includes/user_infos.php");

    $zipName = basename($_GET["zip_name"]);
    $tmpZipPath = __DIR__ . "/../../uploads/" . $zipName;

    if (!file_exists($tmpZipPath)) {
        header("Location: /Stockify/Front-end/dashboard/settings.php?error=zip+file+not+found");
        exit();
    }

    
    // Extract the ZIP
    $extractPath = __DIR__ . "\..\..\uploads/extracted_" . pathinfo($zipName, PATHINFO_FILENAME);
    if (!is_dir($extractPath)) mkdir($extractPath, 0777, true);

    $zip = new ZipArchive;
    if ($zip->open($tmpZipPath) == TRUE) {
        echo "<pre>";
        var_dump($zip);
        echo "numFiles: " . $zip->numFiles . "<br>";
        echo "status: " . $zip->status  . "<br>";
        echo "statusSys: " . $zip->statusSys . "<br>";
        echo "filename: " . $zip->filename . "<br>";
        echo "comment: " . $zip->comment . "<br>";
        echo $extractPath;
        echo "</pre>";
        //exit();
        $zip->extractTo($extractPath);
        $zip->close();
    } else {
        header("Location: /Stockify/Front-end/dashboard/settings.php?error=zip+failed+:(");
        exit();
    }

    // Import JSON files
    $tables = [
        "DEVIS_DETAILS", "DEVIS_HEADER", "SELL_INVOICE_DETAILS", "SELL_INVOICE_HEADER",
        "BON_LIVRAISON_DETAILS", "BON_LIVRAISON_HEADER", "BON_COMMANDE_DETAILS", "BON_COMMANDE_HEADER",
        "BUY_INVOICE_DETAILS", "BUY_INVOICE_HEADER", "PRODUCT", "USERS",
        "CLIENT", "SUPPLIER", "CATEGORY", "ROLES"
    ];

    foreach ($tables as $table) {
        $jsonFile = $extractPath . "/$table.json";
        if (!file_exists($jsonFile)) continue;

        $jsonData = file_get_contents($jsonFile);
        $rows = json_decode($jsonData, true);

        if (!is_array($rows)) continue;

        // Empty the table first
        $bd->exec("DELETE FROM $table");

        // Get column names from the first row
        $columns = array_keys($rows[0]);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO $table (" . implode(",", $columns) . ") VALUES (" . implode(",", $placeholders) . ")";
        $stmt = $bd->prepare($sql);

        foreach ($rows as $row) {
            $stmt->execute($row);
        }
    }

    // Clean up
    unlink($tmpZipPath);
    array_map('unlink', glob("$extractPath/*.json"));
    rmdir($extractPath);

    session_destroy();

    // Redirect with success
    header("Location: /Stockify/Front-end/login/index.php?message=import+success+:D+\n+Please+login+again");
    exit();
?>