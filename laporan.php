<?php
session_start();

include 'config.php';

// Filter tanggal
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

try {
    // Query untuk mendapatkan data transaksi
    $stmt = $conn->prepare("
        SELECT t.*, p.nama as product_name 
        FROM transactions t
        JOIN products p ON t.product_id = p.id
        WHERE t.transaction_date BETWEEN :start_date AND :end_date + INTERVAL 1 DAY
        ORDER BY t.transaction_date DESC
    ");
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Hitung total penjualan
    $stmt = $conn->prepare("
        SELECT SUM(total_price) as total 
        FROM transactions 
        WHERE transaction_date BETWEEN :start_date AND :end_date + INTERVAL 1 DAY
    ");
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi - ElectroShop</title>
    <!-- Gunakan CSS yang sama dengan index.php -->
</head>
<body>
    <!-- Sertakan navbar yang sama -->
    
    <main class="container">
        <h1>Laporan Transaksi</h1>
        
        <form method="GET" style="margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="<?= $start_date ?>" required>
                </div>
                <div>
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="<?= $end_date ?>" required>
                </div>
                <div>
                    <button type="submit">Filter</button>
                    <button type="button" onclick="exportToExcel()" style="margin-left: 10px;">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </form>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php else: ?>
            <div style="margin-bottom: 1rem; font-size: 1.2rem;">
                Total Penjualan: <strong>Rp <?= number_format($total['total'] ?? 0, 0, ',', '.') ?></strong>
            </div>
            
            <table id="tabel-laporan">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Total Harga</th>
                        <th>Metode Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trx): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($trx['transaction_date'])) ?></td>
                        <td><?= htmlspecialchars($trx['product_name']) ?></td>
                        <td><?= $trx['quantity'] ?></td>
                        <td>Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></td>
                        <td><?= ucfirst(str_replace('-', ' ', $trx['payment_method'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <script>
        function exportToExcel() {
            const table = document.getElementById('tabel-laporan');
            const workbook = XLSX.utils.table_to_book(table);
            XLSX.writeFile(workbook, `laporan_transaksi_${new Date().toISOString().slice(0,10)}.xlsx`);
        }
    </script>
</body>
</html>