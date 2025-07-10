<?php
session_start();
require_once 'check_auth.php';
require_once 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->query("SELECT id, nama, harga, barcode FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>