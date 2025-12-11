<?php
    $bd = new PDO("mysql:hostname=localhost;dbname=STOCKIFY_DATABASE;charset=utf8", "root", "");
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $GENERAL_VARIABLES = $bd->query("SELECT * FROM GENERAL_VARIABLES WHERE ID=0;")->fetch(PDO::FETCH_ASSOC)
?>
