<?php
    require("../../includes/db_connection.php");

    $query = null;
    $request = null;

    if ($_SERVER["REQUEST_METHOD"] === "GET"){
        if (isset($_GET['invoiceID']) && isset($_GET['invoiceType'])){
            $id = $_GET['invoiceID'];
            $type = $_GET['invoiceType'];
            
            if ($type === "Sell"){
                $query = "DELETE FROM SELL_INVOICE_DETAILS WHERE ID_INVOICE = :id; DELETE FROM SELL_INVOICE_HEADER WHERE ID_INVOICE = :id;";
            }elseif ($type === "Buy"){
                $query = "DELETE FROM BUY_INVOICE_DETAILS WHERE ID_INVOICE = :id; DELETE FROM BUY_INVOICE_HEADER WHERE ID_INVOICE = :id;";
            }
            $request = $bd->prepare($query);
            $request->bindValue(":id", $id);
            
            try{
                $request->execute();
            }catch (PDOException $e){
                echo $e->getMessage();
            }
            if ($type === "Sell"){
                header("Location: show_sell_invoices.php");
            }elseif ($type === "Buy"){
                header("Location: show_buy_invoices.php");
            }
            exit();
        }
    }
?>