
<?php

include 'config.php';

try {
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(transaction_date, '%b') AS bulan,
            SUM(total_price) AS total
        FROM transactions
        GROUP BY MONTH(transaction_date)
        ORDER BY MONTH(transaction_date)
    ");

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $labels = [];
    $data = [];

    foreach ($result as $row) {
        $labels[] = $row['bulan'];
        $data[] = (float)$row['total'];
    }

    echo json_encode([
        'labels' => $labels,
        'data' => $data
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
