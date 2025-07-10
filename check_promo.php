<?php
session_start();
require_once 'check_auth.php';
require_once 'config.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';

try {
    if (empty($code)) {
        throw new Exception('Kode promo harus diisi');
    }
    
    $stmt = $conn->prepare("SELECT * FROM promos WHERE code = :code AND start_date <= CURDATE() AND end_date >= CURDATE()");
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$promo) {
        throw new Exception('Kode promo tidak valid atau sudah kadaluarsa');
    }
    
    echo json_encode([
        'valid' => true,
        'promo' => $promo,
        'message' => 'Promo berhasil diterapkan'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'valid' => false,
        'message' => $e->getMessage()
    ]);
}
?>