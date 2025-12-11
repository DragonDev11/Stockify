<?php
try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Database connection
        require("../../includes/db_connection.php");

        // Get role ID and new role details
        $ID = $_POST['id'] ?? null;
        $new_id = $_POST['new_id'] ?? '';
        $new_name = $_POST['new_name'] ?? '';
        $privileges = $_POST['privileges'] ?? [];

        // Validate inputs
        if (!$ID || !$privileges) {
            throw new Exception('Missing required fields.');
        }

        // First, get the current role information from database
        $getRoleQuery = "SELECT ROLE_ID, NAME FROM ROLES WHERE ROLE_ID = :id";
        $getRoleStmt = $bd->prepare($getRoleQuery);
        $getRoleStmt->bindValue(':id', $ID);
        $getRoleStmt->execute();
        $currentRole = $getRoleStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentRole) {
            throw new Exception('Role not found in database.');
        }

        // Use original values if new ones aren't provided
        $final_id = $new_id ?: $currentRole['ROLE_ID'];
        $final_name = $new_name ?: $currentRole['NAME'];

        // Prepare the SQL query to update the role with ALL columns
        $query = "
            UPDATE ROLES SET 
                ROLE_ID = :new_id,
                NAME = :new_name,
                ADMINISTRATOR = :administrator,
                VIEW_PRODUCTS = :view_products,
                ADD_PRODUCTS = :add_products,
                MODIFY_PRODUCTS = :modify_products,
                DELETE_PRODUCTS = :delete_products,
                VIEW_CATEGORIES = :view_categories,
                ADD_CATEGORIES = :add_categories,
                MODIFY_CATEGORIES = :modify_categories,
                DELETE_CATEGORIES = :delete_categories,
                VIEW_INVOICES = :view_invoices,
                ADD_INVOICES = :add_invoices,
                MODIFY_INVOICES = :modify_invoices,
                DELETE_INVOICES = :delete_invoices,
                VIEW_USERS = :view_users,
                ADD_USERS = :add_users,
                MODIFY_USERS = :modify_users,
                DELETE_USERS = :delete_users,
                VIEW_ROLES = :view_roles,
                ADD_ROLES = :add_roles,
                MODIFY_ROLES = :modify_roles,
                DELETE_ROLES = :delete_roles,
                VIEW_ORDERS = :view_orders,
                ADD_ORDERS = :add_orders,
                MODIFY_ORDERS = :modify_orders,
                DELETE_ORDERS = :delete_orders,
                VIEW_QUOTATIONS = :view_quotations,
                ADD_QUOTATIONS = :add_quotations,
                CONFIRM_QUOTATIONS = :confirm_quotations,
                DELETE_QUOTATIONS = :delete_quotations,
                VIEW_CLIENTS = :view_clients,
                DELETE_CLIENTS = :delete_clients,
                VIEW_SUPPLIERS = :view_suppliers,
                ADD_SUPPLIERS = :add_suppliers,
                DELETE_SUPPLIERS = :delete_suppliers,
                USE_AI = :use_ai,
                STATE_INITIATED = :state_initiated,
                STATE_IN_PROGRESS = :state_in_progress,
                STATE_DELIVERING = :state_delivering,
                STATE_HALTED = :state_halted,
                STATE_DELIVERED = :state_delivered,
                STATE_CANCELED = :state_canceled
            WHERE ROLE_ID = :id
        ";

        $request = $bd->prepare($query);

        // Bind role info
        $request->bindValue(':id', $ID, PDO::PARAM_STR);
        $request->bindValue(':new_id', $final_id, PDO::PARAM_STR);
        $request->bindValue(':new_name', $final_name, PDO::PARAM_STR);

        // Complete list of all privilege columns
        $columns = [
            'ADMINISTRATOR',
            'VIEW_PRODUCTS', 'ADD_PRODUCTS', 'MODIFY_PRODUCTS', 'DELETE_PRODUCTS',
            'VIEW_CATEGORIES', 'ADD_CATEGORIES', 'MODIFY_CATEGORIES', 'DELETE_CATEGORIES',
            'VIEW_INVOICES', 'ADD_INVOICES', 'MODIFY_INVOICES', 'DELETE_INVOICES',
            'VIEW_USERS', 'ADD_USERS', 'MODIFY_USERS', 'DELETE_USERS',
            'VIEW_ROLES', 'ADD_ROLES', 'MODIFY_ROLES', 'DELETE_ROLES',
            'VIEW_ORDERS', 'ADD_ORDERS', 'MODIFY_ORDERS', 'DELETE_ORDERS',
            'VIEW_QUOTATIONS', 'ADD_QUOTATIONS', 'CONFIRM_QUOTATIONS', 'DELETE_QUOTATIONS',
            'VIEW_CLIENTS', 'DELETE_CLIENTS',
            'VIEW_SUPPLIERS', 'ADD_SUPPLIERS','DELETE_SUPPLIERS',
            'USE_AI',
            'STATE_INITIATED', 'STATE_IN_PROGRESS', 'STATE_DELIVERING',
            'STATE_HALTED', 'STATE_DELIVERED', 'STATE_CANCELED'
        ];

        // Bind each privilege (set to 1 if checked, 0 if not)
        foreach ($columns as $column) {
            $param_name = ':' . strtolower($column);
            $value = isset($privileges[$column]) ? 1 : 0;
            $request->bindValue($param_name, $value, PDO::PARAM_INT);
        }

        // Execute the update
        if ($request->execute()) {
            // Update successful - redirect
            header('Location: show_roles.php?success=1');
            exit;
        } else {
            throw new Exception('Failed to update role.');
        }
    }
} catch (Exception $e) {
    // Log error and redirect with error message
    error_log("Role update error: " . $e->getMessage());
    header('Location: show_roles.php?id=' . urlencode($ID ?? '') . '&error=' . urlencode($e->getMessage()));
    exit;
}
?>