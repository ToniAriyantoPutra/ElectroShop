<?php
session_start();
require_once 'config.php';

// Ambil data pelanggan
try {
    $stmt = $conn->query("
        SELECT c.*, COUNT(t.id) as transaction_count, SUM(t.total_price) as total_spent
        FROM customers c
        LEFT JOIN transactions t ON c.id = t.customer_id
        GROUP BY c.id
        ORDER BY c.name
    ");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pelanggan - ElectroShop</title>
    <!-- Gunakan CSS yang sama dengan index.php -->
</head>
<body>
    <!-- Sertakan navbar yang sama -->
    
    <main class="container">
        <h1>Data Pelanggan</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Jumlah Transaksi</th>
                    <th>Total Belanja</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?= htmlspecialchars($customer['name']) ?></td>
                    <td><?= htmlspecialchars($customer['phone']) ?></td>
                    <td><?= htmlspecialchars($customer['email']) ?></td>
                    <td><?= $customer['transaction_count'] ?></td>
                    <td>Rp <?= number_format($customer['total_spent'] ?? 0, 0, ',', '.') ?></td>
                    <td>
                        <a href="customer_history.php?id=<?= $customer['id'] ?>">Riwayat</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>