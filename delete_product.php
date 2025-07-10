
<?php
session_start();

include 'config.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // Periksa apakah produk dengan ID tersebut ada
        $check = $conn->prepare("SELECT * FROM products WHERE id = :id");
        $check->bindParam(':id', $id, PDO::PARAM_INT);
        $check->execute();

        if ($check->rowCount() > 0) {
            // Jika ada, hapus produk
            $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Redirect ke daftar produk dengan notifikasi berhasil
            header("Location: index.php?page=daftar-produk&success=deleted");
            exit;
        } else {
            // Produk tidak ditemukan
            header("Location: index.php?page=daftar-produk&error=not_found");
            exit;
        }
    } catch (PDOException $e) {
        // Tangani kesalahan database
        header("Location: index.php?page=daftar-produk&error=delete_failed");
        exit;
    }
} else {
    // ID tidak diberikan
    header("Location: index.php?page=daftar-produk&error=invalid_request");
    exit;
}
?>
