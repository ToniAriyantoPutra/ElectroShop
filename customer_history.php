<?php
session_start();
require_once 'config.php';

$customer_id = $_GET['id'] ?? 0;

try {
    // Ambil data pelanggan
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->bindParam(':id', $customer_id);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        throw new Exception("Pelanggan tidak ditemukan");
    }
    
    // Ambri riwayat transaksi
    $stmt = $conn->prepare("
        SELECT t.*, p.nama as product_name 
        FROM transactions t
        JOIN products p ON t.product_id = p.id
        WHERE t.customer_id = :customer_id
        ORDER BY t.transaction_date DESC
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Hitung total belanja
    $stmt = $conn->prepare("
        SELECT SUM(total_price) as total 
        FROM transactions 
        WHERE customer_id = :customer_id
    ");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Pelanggan - ElectroShop</title>
    <!-- Gunakan CSS yang sama dengan index.php -->
</head>
<body>
    <!-- Sertakan navbar yang sama -->
    
    <main class="container">
        <h1>Riwayat Transaksi Pelanggan</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php else: ?>
            <div style="margin-bottom: 2rem;">
                <h2><?= htmlspecialchars($customer['name']) ?></h2>
                <p>Telepon: <?= htmlspecialchars($customer['phone']) ?></p>
                <p>Email: <?= htmlspecialchars($customer['email']) ?></p>
                <p>Total Belanja: <strong>Rp <?= number_format($total['total'] ?? 0, 0, ',', '.') ?></strong></p>
            </div>
            
            <table>
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
</body>
</html>