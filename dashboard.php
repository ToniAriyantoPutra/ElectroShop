<?php
session_start();
require_once 'config.php';

// Ambil data untuk dashboard
try {
    // Total penjualan hari ini
    $stmt = $conn->query("SELECT SUM(total_price) as total FROM transactions WHERE DATE(transaction_date) = CURDATE()");
    $today_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total produk
    $stmt = $conn->query("SELECT COUNT(*) as total FROM products");
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Produk stok rendah
    $stmt = $conn->query("SELECT COUNT(*) as total FROM products WHERE stok <= 5");
    $low_stock = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Transaksi hari ini
    $stmt = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE DATE(transaction_date) = CURDATE()");
    $today_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Grafik penjualan 7 hari terakhir
    $stmt = $conn->query("
        SELECT DATE(transaction_date) as date, SUM(total_price) as total
        FROM transactions
        WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(transaction_date)
        ORDER BY DATE(transaction_date)
    ");
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Produk terlaris
    $stmt = $conn->query("
        SELECT p.nama, SUM(t.quantity) as total_sold
        FROM transactions t
        JOIN products p ON t.product_id = p.id
        GROUP BY t.product_id
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - ElectroShop</title>
    <!-- Gunakan CSS yang sama dengan index.php -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sertakan navbar yang sama -->
    
    <main class="container">
        <h1>Dashboard</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php else: ?>
            <div class="dashboard-cards" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
                <div class="card" style="background: #3498db; color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3>Penjualan Hari Ini</h3>
                    <p style="font-size: 2rem; margin: 0.5rem 0;">Rp <?= number_format($today_sales, 0, ',', '.') ?></p>
                </div>
                <div class="card" style="background: #2ecc71; color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3>Total Produk</h3>
                    <p style="font-size: 2rem; margin: 0.5rem 0;"><?= $total_products ?></p>
                </div>
                <div class="card" style="background: #e74c3c; color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3>Stok Rendah</h3>
                    <p style="font-size: 2rem; margin: 0.5rem 0;"><?= $low_stock ?></p>
                </div>
                <div class="card" style="background: #f39c12; color: white; padding: 1.5rem; border-radius: 8px;">
                    <h3>Transaksi Hari Ini</h3>
                    <p style="font-size: 2rem; margin: 0.5rem 0;"><?= $today_transactions ?></p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <div class="chart-container">
                    <h2>Penjualan 7 Hari Terakhir</h2>
                    <canvas id="salesChart" height="300"></canvas>
                </div>
                
                <div>
                    <h2>Produk Terlaris</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['nama']) ?></td>
                                <td><?= $product['total_sold'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <script>
                // Grafik penjualan
                const salesCtx = document.getElementById('salesChart').getContext('2d');
                const salesChart = new Chart(salesCtx, {
                    type: 'bar',
                    data: {
                        labels: [<?php 
                            $dates = array_column($sales_data, 'date');
                            echo "'" . implode("','", $dates) . "'";
                        ?>],
                        datasets: [{
                            label: 'Total Penjualan',
                            data: [<?php echo implode(',', array_column($sales_data, 'total')); ?>],
                            backgroundColor: 'rgba(52, 152, 219, 0.7)',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Rp ' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            </script>
        <?php endif; ?>
    </main>
</body>
</html>