<?php
    require("../../includes/db_connection.php");
    session_start();

    if ($_SERVER["REQUEST_METHOD"] === "POST"){
        $redirect_url = $_POST['redirect'];
        if (!isset($_SESSION['order'])){
            $_SESSION['order']=[];
        }
        $_SESSION["order"] = $_POST;
        
        header("Location: ".$redirect_url);
    }
?>