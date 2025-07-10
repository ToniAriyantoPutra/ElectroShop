<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Validasi data
$required_fields = ['transaction_items', 'payment_method'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = 'Data transaksi tidak lengkap';
        header('Location: transaksi.php');
        exit();
    }
}

try {
    // Mulai transaksi database
    $conn->beginTransaction();
    
    // Parse data item transaksi
    $transaction_items = json_decode($_POST['transaction_items'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($transaction_items)) {
        throw new Exception('Format item transaksi tidak valid');
    }
    
    $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
    $payment_method = $_POST['payment_method'];
    $amount_paid = $_POST['amount_paid'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $promo_code = !empty($_POST['promo_code']) ? $_POST['promo_code'] : null;
    
    // Hitung total harga
    $total_price = 0;
    foreach ($transaction_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
    $total_price -= $discount;
    
    // Validasi pembayaran tunai
    if ($payment_method === 'cash' && $amount_paid < $total_price) {
        throw new Exception('Jumlah pembayaran tidak mencukupi');
    }
    
    $change_amount = $payment_method === 'cash' ? ($amount_paid - $total_price) : 0;
    
    // Simpan transaksi utama
    $stmt = $conn->prepare("
        INSERT INTO transactions (
            customer_id, 
            total_price, 
            payment_method, 
            amount_paid, 
            change_amount, 
            discount, 
            promo_code,
            id
        ) VALUES (
            :customer_id, 
            :total_price, 
            :payment_method, 
            :amount_paid, 
            :change_amount, 
            :discount, 
            :promo_code,
            :id
        )
    ");
    
    $stmt->execute([
        ':customer_id' => $customer_id,
        ':total_price' => $total_price,
        ':payment_method' => $payment_method,
        ':amount_paid' => $amount_paid,
        ':change_amount' => $change_amount,
        ':discount' => $discount,
        ':promo_code' => $promo_code,
        ':id' => $_SESSION['id']
    ]);
    
    $transaction_id = $conn->lastInsertId();
    
    // Simpan detail item transaksi dan update stok
    foreach ($transaction_items as $item) {
        // Simpan detail transaksi
        $detail_stmt = $conn->prepare("
            INSERT INTO transaction_items (
                transaction_id, 
                product_id, 
                product_name, 
                quantity, 
                price
            ) VALUES (
                :transaction_id, 
                :product_id, 
                :product_name, 
                :quantity, 
                :price
            )
        ");
        
        $detail_stmt->execute([
            ':transaction_id' => $transaction_id,
            ':product_id' => $item['product_id'],
            ':product_name' => $item['product_name'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price']
        ]);
        
        // Update stok produk
        $update_stmt = $conn->prepare("
            UPDATE products 
            SET stok = stok - :quantity 
            WHERE id = :product_id AND stok >= :quantity
        ");
        
        $update_stmt->execute([
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity']
        ]);
        
        if ($update_stmt->rowCount() === 0) {
            throw new Exception("Stok produk tidak mencukupi untuk {$item['product_name']}");
        }
    }
    
    // Commit transaksi jika semua berhasil
    $conn->commit();
    
    // Redirect ke halaman struk
    header("Location: print_receipt.php?id=$transaction_id");
    exit();
    
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollBack();
    $_SESSION['error'] = 'Transaksi gagal: ' . $e->getMessage();
    header('Location: transaksi.php');
    exit();
}