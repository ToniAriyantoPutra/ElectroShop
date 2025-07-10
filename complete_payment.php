
<?php

include 'config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['transactions'])) {
    $transaction = $_SESSION['transactions'];
    $payment_method = $_POST['metode-pembayaran'];
    $amount_paid = $_POST['jumlah-pembayaran'];
    
    // Hitung kembalian
    $change_amount = $amount_paid - $transaction['total_price'];
    
    // Validasi pembayaran
    if ($change_amount < 0) {
        header("Location: index.php?page=pembayaran&error=kurang");
        // Di file complete_payment.php setelah transaksi berhasil
        header('Location: print_receipt.php?id=' . $transaction_id);
        exit();
    }
    
    try {
        // Simpan transaksi ke database
        $stmt = $conn->prepare("INSERT INTO transactions 
            (product_id, product_name, quantity, total_price, payment_method, amount_paid, change_amount) 
            VALUES (:product_id, :product_name, :quantity, :total_price, :payment_method, :amount_paid, :change_amount)");
        
        $stmt->bindParam(':product_id', $transaction['product_id']);
        $stmt->bindParam(':product_name', $transaction['product_name']);
        $stmt->bindParam(':quantity', $transaction['quantity']);
        $stmt->bindParam(':total_price', $transaction['total_price']);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':amount_paid', $amount_paid);
        $stmt->bindParam(':change_amount', $change_amount);
        $stmt->execute();
        
        // Hapus session transaksi
        unset($_SESSION['transaction']);
        
        // Redirect dengan data untuk ditampilkan
        header("Location: index.php?page=pembayaran&success=1&total=".$transaction['total_price'].
              "&paid=".$amount_paid."&change=".$change_amount."&method=".$payment_method);
        exit();
    } catch(PDOException $e) {
        header("Location: index.php?page=pembayaran&error=server");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>