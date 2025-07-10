<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'config.php';

// Tambah promo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_promo'])) {
    $code = $_POST['code'];
    $description = $_POST['description'];
    $discount_percent = $_POST['discount_percent'];
    $discount_amount = $_POST['discount_amount'];
    $min_purchase = $_POST['min_purchase'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO promos (code, description, discount_percent, discount_amount, min_purchase, start_date, end_date) VALUES (:code, :description, :discount_percent, :discount_amount, :min_purchase, :start_date, :end_date)");
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':discount_percent', $discount_percent);
        $stmt->bindParam(':discount_amount', $discount_amount);
        $stmt->bindParam(':min_purchase', $min_purchase);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        
        $_SESSION['success'] = "Promo berhasil ditambahkan!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header('Location: promos.php');
    exit();
}

// Hapus promo
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM promos WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $_SESSION['success'] = "Promo berhasil dihapus!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header('Location: promos.php');
    exit();
}

// Ambil data promos
$promos = $conn->query("SELECT * FROM promos ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Promo - ElectroShop</title>
    <!-- Gunakan CSS yang sama dengan index.php -->
</head>
<body>
    <!-- Sertakan navbar yang sama -->
    
    <main class="container">
        <h1>Manajemen Promo</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST" style="margin-bottom: 2rem;">
            <h2>Tambah Promo Baru</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label for="code">Kode Promo</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div>
                    <label for="description">Deskripsi</label>
                    <input type="text" id="description" name="description" required>
                </div>
                <div>
                    <label for="discount_percent">Diskon Persen (%)</label>
                    <input type="number" id="discount_percent" name="discount_percent" min="0" max="100" step="0.01" value="0">
                </div>
                <div>
                    <label for="discount_amount">Diskon Nominal (Rp)</label>
                    <input type="number" id="discount_amount" name="discount_amount" min="0" step="1000" value="0">
                </div>
                <div>
                    <label for="min_purchase">Minimal Pembelian (Rp)</label>
                    <input type="number" id="min_purchase" name="min_purchase" min="0" step="1000" value="0">
                </div>
                <div>
                    <label for="start_date">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div>
                    <label for="end_date">Tanggal Berakhir</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
            </div>
            <button type="submit" name="add_promo" style="margin-top: 1rem;">Tambah Promo</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Deskripsi</th>
                    <th>Diskon</th>
                    <th>Min. Pembelian</th>
                    <th>Periode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promos as $promo): ?>
                <tr>
                    <td><?= htmlspecialchars($promo['code']) ?></td>
                    <td><?= htmlspecialchars($promo['description']) ?></td>
                    <td>
                        <?php if ($promo['discount_percent'] > 0): ?>
                            <?= $promo['discount_percent'] ?>%
                        <?php else: ?>
                            Rp <?= number_format($promo['discount_amount'], 0, ',', '.') ?>
                        <?php endif; ?>
                    </td>
                    <td>Rp <?= number_format($promo['min_purchase'], 0, ',', '.') ?></td>
                    <td>
                        <?= date('d/m/Y', strtotime($promo['start_date'])) ?> - 
                        <?= date('d/m/Y', strtotime($promo['end_date'])) ?>
                    </td>
                    <td>
                        <a href="promos.php?delete=<?= $promo['id'] ?>" onclick="return confirm('Yakin ingin menghapus promo ini?')" style="color: #e74c3c;">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>