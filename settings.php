<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'config.php';

// Simpan pengaturan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $conn->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
        }
        
        $_SESSION['success'] = "Pengaturan berhasil diperbarui!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header('Location: settings.php');
    exit();
}

// Ambil data pengaturan
$settings = $conn->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);
$settings = array_column($settings, 'setting_value', 'setting_key');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pengaturan - ElectroShop</title>
    <!-- Gunakan CSS yang sama dengan index.php -->
</head>
<body>
    <!-- Sertakan navbar yang sama -->
    
    <main class="container">
        <h1>Pengaturan Aplikasi</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; align-items: center;">
                <label for="shop_name">Nama Toko:</label>
                <input type="text" id="shop_name" name="settings[shop_name]" value="<?= htmlspecialchars($settings['shop_name'] ?? '') ?>" required>
                
                <label for="shop_address">Alamat Toko:</label>
                <input type="text" id="shop_address" name="settings[shop_address]" value="<?= htmlspecialchars($settings['shop_address'] ?? '') ?>">
                
                <label for="shop_phone">Telepon Toko:</label>
                <input type="text" id="shop_phone" name="settings[shop_phone]" value="<?= htmlspecialchars($settings['shop_phone'] ?? '') ?>">
                
                <label for="receipt_footer">Footer Struk:</label>
                <input type="text" id="receipt_footer" name="settings[receipt_footer]" value="<?= htmlspecialchars($settings['receipt_footer'] ?? '') ?>">
                
                <label for="tax_percentage">Pajak (%):</label>
                <input type="number" id="tax_percentage" name="settings[tax_percentage]" value="<?= htmlspecialchars($settings['tax_percentage'] ?? '0') ?>" min="0" max="100" step="0.1">
            </div>
            
            <button type="submit" style="margin-top: 1rem;">Simpan Pengaturan</button>
        </form>
    </main>
</body>
</html>