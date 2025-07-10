
<?php

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];

    try {
        $stmt = $conn->prepare("UPDATE products SET nama = :nama, stok = :stok, harga = :harga WHERE id = :id");
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':stok', $stok, PDO::PARAM_INT);
        $stmt->bindParam(':harga', $harga, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        // Redirect kembali ke daftar produk dengan notifikasi
        header("Location: index.php?page=daftar-produk&success=update");
        exit;
    } catch (PDOException $e) {
        // Redirect dengan pesan error
        header("Location: index.php?page=daftar-produk&error=1");
        exit;
    }
} else {
    // Jika tidak melalui POST
    header("Location: index.php?page=daftar-produk");
    exit;
}
