<?php
require_once "../../includes/db_connection.php";

$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
$type = isset($_GET['type']) ? $_GET['type'] : 'earnings';

// Handle "Today" case differently
if ($days === 1) {
    $startDate = $endDate = date('Y-m-d');
} else {
    $endDate = date('Y-m-d');
    $startDate = $days > 0 ? date('Y-m-d', strtotime("-$days days")) : '2000-01-01';
}

// Generate all dates in the range for complete data
$dateRange = [];
$currentDate = new DateTime($startDate);
$endDateObj = new DateTime($endDate);

while ($currentDate <= $endDateObj) {
    $dateKey = $days === 1 ? date('H:00', $currentDate->getTimestamp()) : $currentDate->format('Y-m-d');
    $dateRange[$dateKey] = 0;
    $currentDate->modify($days === 1 ? '+1 hour' : '+1 day');
}

if ($type === 'earnings') {
    if ($days === 1) {
        // Hourly earnings for today
        $query = "SELECT DATE_FORMAT(DATE, '%H:00') as hour, SUM(TOTAL_PRICE_TTC - TOTAL_PRICE_HT) as total 
                  FROM SELL_INVOICE_HEADER 
                  WHERE DATE(DATE) = CURDATE()
                  GROUP BY hour 
                  ORDER BY hour";
    } else {
        // Daily earnings
        $query = "SELECT DATE(DATE) as day, SUM(TOTAL_PRICE_TTC - TOTAL_PRICE_HT) as total 
                  FROM SELL_INVOICE_HEADER 
                  WHERE DATE BETWEEN :startDate AND :endDate
                  GROUP BY day 
                  ORDER BY day";
    }
} else {
    if ($days === 1) {
        // Hourly orders for today
        $query = "SELECT DATE_FORMAT(DATE, '%H:00') as hour, COUNT(*) as total 
                  FROM BON_COMMANDE_HEADER 
                  WHERE STATE = 'delivered' AND DATE(DATE) = CURDATE()
                  GROUP BY hour 
                  ORDER BY hour";
    } else {
        // Daily orders
        $query = "SELECT DATE(DATE) as day, COUNT(*) as total 
                  FROM BON_COMMANDE_HEADER 
                  WHERE STATE = 'delivered' AND DATE BETWEEN :startDate AND :endDate
                  GROUP BY day 
                  ORDER BY day";
    }
}

$request = $bd->prepare($query);
if ($days !== 1) {
    $request->bindParam(':startDate', $startDate);
    $request->bindParam(':endDate', $endDate);
}
$request->execute();
$results = $request->fetchAll(PDO::FETCH_ASSOC);

// Merge database results with date range
foreach ($results as $result) {
    $key = $days === 1 ? $result['hour'] : $result['day'];
    $dateRange[$key] = (float)$result['total'];
}

// Prepare response
$labels = array_keys($dateRange);
$values = array_values($dateRange);

// If more than 30 days (and not today), aggregate by month
if ($days > 30 && $days !== 1) {
    $aggregated = [];
    foreach ($dateRange as $date => $value) {
        $month = date('M Y', strtotime($date));
        if (!isset($aggregated[$month])) {
            $aggregated[$month] = 0;
        }
        $aggregated[$month] += $value;
    }
    
    $labels = array_keys($aggregated);
    $values = array_values($aggregated);
}

header('Content-Type: application/json');
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
?>