<?php
    session_start();
    require("../../includes/user_infos.php");

    $tables = [
        "DEVIS_DETAILS", "DEVIS_HEADER", "SELL_INVOICE_DETAILS", "SELL_INVOICE_HEADER",
        "BON_LIVRAISON_DETAILS", "BON_LIVRAISON_HEADER", "BON_COMMANDE_DETAILS", "BON_COMMANDE_HEADER",
        "BUY_INVOICE_DETAILS", "BUY_INVOICE_HEADER", "PRODUCT", "USERS",
        "CLIENT", "SUPPLIER", "CATEGORY", "ROLES"
    ];

    $zip = new ZipArchive();
    $zip_file_name = "stockify_export_".date("Y-m-d_H-i-s").".zip";
    $temp_zip_path = tempnam(sys_get_temp_dir(), "zip");

    if ($zip->open($temp_zip_path, ZipArchive::OVERWRITE) !== TRUE){
        exit("Cannot open <$temp_zip_path>");
    }

    foreach ($tables as $table) {
        echo $table."<br>";
        $data = $bd->query("SELECT * FROM $table;")->fetchAll(PDO::FETCH_ASSOC);
        for($i=0;$i<sizeof($data);$i++){
            if (isset($data[$i]["IMAGE"])){
                $data[$i]["IMAGE"] = null;
            }
        }
        $json_format = json_encode($data, JSON_PRETTY_PRINT);
        $zip->addFromString($table .".json", $json_format);
    }


    $zip->close();

    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename=\"$zip_file_name\"");
    header('Content-Length: ' . filesize($temp_zip_path));
    readfile($temp_zip_path);

    unlink($temp_zip_path);
    header("Location: /Stockify/Front-end/dashboard/settings.php?message=success");
    exit();

?>