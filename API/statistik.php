<?php require_once 'check_auth.php'; ?>

<?php
include '../config.php';

try {
    // Ambil data penjualan per bulan
    $stmt = $conn->query("SELECT MONTH(tanggal) AS bulan, SUM(jumlah) AS total FROM penjualan GROUP BY bulan ORDER BY bulan ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Siapkan array untuk labels dan data
    $labels = [];
    $totals = [];

    foreach ($data as $row) {
        $labels[] = date('M', mktime(0, 0, 0, $row['bulan'], 10)); // Nama bulan
        $totals[] = (int) $row['total'];
    }

    // Kembalikan data dalam format JSON
    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Penjualan per Bulan',
                'data' => $totals,
                'backgroundColor' => 'rgba(52, 152, 219, 0.2)',
                'borderColor' => 'rgba(52, 152, 219, 1)',
                'borderWidth' => 1
            ]
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Gagal mengambil data: ' . $e->getMessage()]);
}
?>
