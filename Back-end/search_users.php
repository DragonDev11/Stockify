<?php
header('Content-Type: application/json');
require("../includes/db_connection.php");

try {
    if (isset($_GET['term'])) {
        $searchTerm = '%' . $_GET['term'] . '%';
        
        $query = "SELECT ID, FIRST_NAME, LAST_NAME, USERNAME FROM USERS 
                 WHERE FIRST_NAME LIKE :term OR LAST_NAME LIKE :term OR USERNAME LIKE :term
                 LIMIT 10";
        $stmt = $bd->prepare($query);
        $stmt->bindValue(':term', $searchTerm);
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>