<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama-produk'];
    $stok = $_POST['jumlah-stok'];
    $harga = $_POST['harga'];

    try {
        $stmt = $conn->prepare("INSERT INTO products (nama, stok, harga) VALUES (:nama, :stok, :harga)");
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':stok', $stok);
        $stmt->bindParam(':harga', $harga);
        $stmt->execute();
        
        header("Location: index.php?page=daftar-produk&success=1");
        exit();
    } catch(PDOException $e) {
        header("Location: index.php?page=tambah-produk&error=1");
        exit();
    }
}
?>