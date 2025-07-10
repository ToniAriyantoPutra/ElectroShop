<?php
session_start();
require_once 'config.php';

$transaction_id = $_GET['id'] ?? 0;

try {
    // Ambil data transaksi
    $stmt = $conn->prepare("
        SELECT t.* 
        FROM transactions t
        WHERE t.id = :id
    ");
    $stmt->bindParam(':id', $transaction_id);
    $stmt->execute();
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        throw new Exception("Transaksi tidak ditemukan");
    }
} catch(Exception $e) {
    die($e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Struk #<?= $transaction_id ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            width: 80mm;
            margin: 0;
            padding: 10px;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .shop-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .shop-info {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .transaction-info {
            margin: 15px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 12px;
        }
        table th {
            text-align: left;
            padding: 5px 0;
            border-bottom: 1px dashed #000;
        }
        table td {
            padding: 5px 0;
            border-bottom: 1px dashed #ccc;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="shop-name"><?= htmlspecialchars($transaction['shop_name'] ?: 'ElectroShop') ?></div>
        <div class="shop-info">
            <?= htmlspecialchars($transaction['shop_address'] ?: 'Jl. Contoh No. 123') ?><br>
            Telp: <?= $transaction['shop_phone'] ?: '081234567890' ?>
        </div>
    </div>
    
    <div class="transaction-info">
        <div><strong>No. Transaksi:</strong> #<?= $transaction_id ?></div>
        <div><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($transaction['transaction_date'])) ?></div>
        <?php if (!empty($transaction['customer_name'])): ?>
            <div><strong>Pelanggan:</strong> <?= htmlspecialchars($transaction['customer_name']) ?></div>
        <?php endif; ?>
        <?php if (!empty($transaction['promo_code'])): ?>
            <div><strong>Promo:</strong> <?= htmlspecialchars($transaction['promo_code']) ?></div>
        <?php endif; ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($transaction['product_name']) ?></td>
                <td class="text-right"><?= $transaction['quantity'] ?></td>
                <td class="text-right">Rp <?= number_format($transaction['total_price'] / $transaction['quantity'], 0, ',', '.') ?></td>
                <td class="text-right">Rp <?= number_format($transaction['total_price'], 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp <?= number_format($transaction['total_price'], 0, ',', '.') ?></span>
        </div>
        
        <?php if ($transaction['discount'] > 0): ?>
            <div class="total-row">
                <span>Diskon:</span>
                <span>- Rp <?= number_format($transaction['discount'], 0, ',', '.') ?></span>
            </div>
        <?php endif; ?>
        
        <div class="divider"></div>
        
        <div class="total-row" style="font-weight: bold;">
            <span>Total:</span>
            <span>Rp <?= number_format($transaction['total_price'] - $transaction['discount'], 0, ',', '.') ?></span>
        </div>
        
        <div class="total-row">
            <span>Tunai:</span>
            <span>Rp <?= number_format($transaction['amount_paid'], 0, ',', '.') ?></span>
        </div>
        
        <div class="total-row">
            <span>Kembali:</span>
            <span>Rp <?= number_format($transaction['change_amount'], 0, ',', '.') ?></span>
        </div>
    </div>
    
    <div class="footer">
        <?= htmlspecialchars($transaction['receipt_footer'] ?: 'Terima kasih telah berbelanja') ?>
    </div>
    
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
                window.close();
            }, 500);
        };
    </script>
</body>
</html>