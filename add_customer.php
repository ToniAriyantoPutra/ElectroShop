<?php
session_start();
require_once 'check_auth.php';
require_once 'config.php';

header('Content-Type: application/json');

try {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ??
    $address = $_POST['address'] ?? '';
    
    if (empty($name)) {
        throw new Exception('Nama pelanggan harus diisi');
    }
    
    $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address) VALUES (:name, :phone, :email, :address)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':address', $address);
    $stmt->execute();
    
    $customer_id = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'id' => $customer_id,
        'message' => 'Pelanggan berhasil ditambahkan'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>