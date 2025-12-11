<?php
    function check_role($role, $username){
        require("db_connection.php");

        $valid_columns = [
            'ADMINISTRATOR',
            
            // Products
            'VIEW_PRODUCTS',
            'ADD_PRODUCTS',
            'MODIFY_PRODUCTS',
            'DELETE_PRODUCTS',
            
            // Categories
            'VIEW_CATEGORIES',
            'ADD_CATEGORIES',
            'MODIFY_CATEGORIES',
            'DELETE_CATEGORIES',
            
            // Invoices
            'VIEW_INVOICES',
            'ADD_INVOICES',
            'MODIFY_INVOICES',
            'DELETE_INVOICES',
            
            // Roles
            'VIEW_ROLES',
            'ADD_ROLES',
            'MODIFY_ROLES',
            'DELETE_ROLES',

            // Users
            'VIEW_USERS',
            'ADD_USERS',
            'MODIFY_USERS',
            'DELETE_USERS',
            
            // Orders
            'VIEW_ORDERS',
            'ADD_ORDERS',
            'MODIFY_ORDERS',
            'DELETE_ORDERS',
            
            // Quotations
            'VIEW_QUOTATIONS',
            'ADD_QUOTATIONS',
            'CONFIRM_QUOTATIONS',
            'DELETE_QUOTATIONS',
            
            // Clients
            'VIEW_CLIENTS',
            'DELETE_CLIENTS',
            
            // Suppliers
            'VIEW_SUPPLIERS',
            'ADD_SUPPLIERS',
            'DELETE_SUPPLIERS',
            
            // AI
            'USE_AI',
            
            // States
            'STATE_INITIATED',
            'STATE_IN_PROGRESS',
            'STATE_DELIVERING',
            'STATE_HALTED',
            'STATE_DELIVERED',
            'STATE_CANCELED'
        ];

        if (!in_array($role, $valid_columns)) {
            return false;
        }

        $query = "SELECT $role FROM ROLES r JOIN USERS u ON u.USER_ROLE_ID = r.ROLE_ID WHERE u.USERNAME = :username;";
        $request = $bd->prepare($query);

        $request->bindValue(":username", $username);
        try{
            $request->execute();
        }catch (PDOException $e){
            echo "<br><pre>ERROR: ".$e->getMessage()."</pre><br>";
            exit();
        }

        $result = $request->fetchColumn(0);

        if ($result){
            if ($result == 1){
                return true;
            }
            return false;
        }
        return false;
    }
?>