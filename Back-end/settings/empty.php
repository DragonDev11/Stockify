<?php
    session_start();
    require("../../includes/user_infos.php");

    // 1. Fetch Super Admin user data
    $query2 = "SELECT * FROM USERS WHERE USERNAME = :username";
    $request = $bd->prepare($query2);
    $request->bindValue(":username", $username);
    try {
        $request->execute();
        $superAdmin = $request->fetch(PDO::FETCH_ASSOC);
        if (!$superAdmin) exit("Super Admin not found.");
    } catch(PDOException $e) {
        exit($e->getMessage());
    }
    try {
        //$bd->beginTransaction();
        $bd->exec("SET FOREIGN_KEY_CHECKS = 0;");
        // 2. Truncate tables one by one
        $tables = [
            "DEVIS_DETAILS", "DEVIS_HEADER", "SELL_INVOICE_DETAILS", "SELL_INVOICE_HEADER",
            "BON_LIVRAISON_DETAILS", "BON_LIVRAISON_HEADER", "BON_COMMANDE_DETAILS", "BON_COMMANDE_HEADER",
            "BUY_INVOICE_DETAILS", "BUY_INVOICE_HEADER", "PRODUCT", "USERS",
            "CLIENT", "SUPPLIER", "CATEGORY", "ROLES"
        ];

        foreach ($tables as $table) {
            $bd->exec("TRUNCATE TABLE $table");
        }

        $bd->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // 3. Reinsert Super Admin Role
        $bd->exec("
            INSERT INTO ROLES (ROLE_ID, NAME, ADMINISTRATOR, VIEW_USERS, ADD_USERS, MODIFY_USERS, DELETE_USERS) VALUES
            ('0', 'Super Admin', 1,0,0,0,0),
            ('1', 'Admin', 0,1,1,1,1),
            ('2', 'User', 0,0,0,0,0);
        ");

        // 4. Reinsert Super Admin User
        $query3 = "INSERT INTO USERS (USER_ID, last_name, first_name, username, user_role_id, telephone, email, user_password) 
                VALUES (:user_id, :last_name, :first_name, :username, :role_id, :phone, :email, :password)";
        $request = $bd->prepare($query3);
        $request->bindValue(":user_id", $superAdmin["USER_ID"]);
        $request->bindValue(':last_name', $superAdmin["LAST_NAME"]);
        $request->bindValue(':first_name', $superAdmin["FIRST_NAME"]);
        $request->bindValue(':username', $superAdmin["USERNAME"]);
        $request->bindValue(':role_id', $superAdmin["USER_ROLE_ID"]);
        $request->bindValue(':phone', $superAdmin["TELEPHONE"]);
        $request->bindValue(':email', $superAdmin["EMAIL"]);
        $request->bindValue(':password', $superAdmin["USER_PASSWORD"]);
        $request->execute();

        // 5. Update number of users in ROLES (optional: check if role exists)
        $bd->exec("
            UPDATE ROLES r
            JOIN (
                SELECT USER_ROLE_ID, COUNT(*) AS user_count
                FROM USERS
                GROUP BY USER_ROLE_ID
            ) counts ON r.ROLE_ID = counts.USER_ROLE_ID
            SET r.NUMBER_OF_USERS = counts.user_count
        ");

        $bd->exec("INSERT INTO CATEGORY(CATEGORYNAME) VALUES('Uncategorized');");

        //$bd->commit();
        header("Location: /Stockify/Front-end/dashboard/settings.php?message=success");
        exit();

    } catch (PDOException $e) {
        //$bd->rollBack();
        exit($e->getMessage());
    }

?>