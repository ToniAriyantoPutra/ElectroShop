<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// try {
//     $stmt = $conn->query("SELECT nama FROM products WHERE stok <= 5");
//     $low_stock_products = $stmt->fetchAll(PDO::FETCH_COLUMN);
// } catch(PDOException $e) {
//     $low_stock_products = [];
// }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectroShop - Toko Elektronik</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #ecf0f1;
            --text-color: #34495e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @media screen and (max-width: 600px) {
        .chart-container canvas {
        width: 100% !important;
        height: auto !important;
        }
}

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            padding-top: 62px; /* Added space for fixed navbar */
        }

        .container {
            max-width: 1050px;
            width: 90%;
            margin: auto;
        }

        /* Navbar Styles */
        .navbar {
            width: 100%;
            box-shadow: 0 1px 4px rgb(146 161 176 / 15%);
            position: fixed;
            top: 0;
            z-index: 1000;
            background-color: white;
            height: 62px;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 62px;
        }

        .navbar .menu-items {
            display: flex;
        }

        .navbar .nav-container li {
            list-style: none;
        }

        .navbar .nav-container a {
            text-decoration: none;
            color: #0e2431;
            font-weight: 500;
            font-size: 1.2rem;
            padding: 0.7rem;
        }

        .navbar .nav-container a:hover {
            font-weight: bolder;
            color: var(--primary-color);
        }

        .nav-container {
            display: block;
            position: relative;
            height: 60px;
        }

        .nav-container .checkbox {
            position: absolute;
            display: block;
            height: 32px;
            width: 32px;
            top: 20px;
            left: 20px;
            z-index: 5;
            opacity: 0;
            cursor: pointer;
        }

        .nav-container .hamburger-lines {
            display: block;
            height: 26px;
            width: 32px;
            position: absolute;
            top: 17px;
            left: 20px;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .nav-container .hamburger-lines .line {
            display: block;
            height: 4px;
            width: 100%;
            border-radius: 10px;
            background: #0e2431;
        }

        .nav-container .hamburger-lines .line1 {
            transform-origin: 0% 0%;
            transition: transform 0.4s ease-in-out;
        }

        .nav-container .hamburger-lines .line2 {
            transition: transform 0.2s ease-in-out;
        }

        .nav-container .hamburger-lines .line3 {
            transform-origin: 0% 100%;
            transition: transform 0.4s ease-in-out;
        }

        .navbar .menu-items {
            padding-top: 120px;
            box-shadow: inset 0 0 2000px rgba(255, 255, 255, .5);
            height: 100vh;
            width: 100%;
            transform: translate(-150%);
            display: flex;
            flex-direction: column;
            margin-left: -40px;
            padding-left: 50px;
            transition: transform 0.5s ease-in-out;
            text-align: center;
            background-color: white;
        }

        .navbar .menu-items li {
            margin-bottom: 1.2rem;
            font-size: 1.5rem;
            font-weight: 500;
        }

        .logo {
            position: absolute;
            top: 10px;
            right: 75px;
            font-size: 1.5rem;
            color: var(--primary-color);
            font-weight: bold;
        }

        .nav-container input[type="checkbox"]:checked ~ .menu-items {
            transform: translateX(0);
        }

        .nav-container input[type="checkbox"]:checked ~ .hamburger-lines .line1 {
            transform: rotate(45deg);
        }

        .nav-container input[type="checkbox"]:checked ~ .hamburger-lines .line2 {
            transform: scaleY(0);
        }

        .nav-container input[type="checkbox"]:checked ~ .hamburger-lines .line3 {
            transform: rotate(-45deg);
        }

        .nav-container input[type="checkbox"]:checked ~ .logo {
            display: none;
        }

        /* Main Content Styles */
        main {
            padding: 2rem 0;
        }

        .page {
            display: none;
            animation: fadeIn 0.5s;
        }

        .page.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        h1, h2 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        form {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        button {
            background-color: var(--secondary-color);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #27ae60;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .payment-methods {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .success-message {
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }

        .error-message {
            background-color: #e74c3c;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }

        /* Tambahkan di CSS */
@media (max-width: 768px) {
    .container {
        width: 95%;
    }
    
    table {
        display: block;
        overflow-x: auto;
    }
    
    form {
        padding: 1rem;
    }
    
    .payment-methods {
        flex-direction: column;
    }
    
    .dark-mode-toggle {
        right: 100px;
        top: 10px;
    }
    
    .dark-mode-toggle span {
        display: none;
    }
}

@media (max-width: 480px) {
    .logo {
        font-size: 1.2rem;
    }
    
    .search-container input {
        width: 100%;
    }
    
    .filters {
        flex-direction: column;
    }
}
    </style>

    <style>
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Dark mode styles */
body.dark-mode {
    background-color: #121212;
    color: #ffffff;
}

body.dark-mode .navbar {
    background-color: #1e1e1e;
}

body.dark-mode .menu-items {
    background-color: #1e1e1e;
}

body.dark-mode form,
body.dark-mode table,
body.dark-mode .chart-container {
    background-color: #1e1e1e;
    color: #ffffff;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
}

body.dark-mode th {
    background-color: #0d47a1;
}
</style>

<style>
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Dark mode styles */
body.dark-mode {
    background-color: #121212;
    color: #ffffff;
}

body.dark-mode .navbar {
    background-color: #1e1e1e;
}

body.dark-mode .menu-items {
    background-color: #1e1e1e;
}

body.dark-mode form,
body.dark-mode table,
body.dark-mode .chart-container {
    background-color: #1e1e1e;
    color: #ffffff;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
}

body.dark-mode th {
    background-color: #0d47a1;
}

/* Gaya untuk halaman transaksi */
.transaction-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.product-input-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.transaction-items-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    grid-column: span 2;
}

.payment-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.input-group {
    margin-bottom: 15px;
}

.items-list {
    max-height: 300px;
    overflow-y: auto;
    margin: 15px 0;
    border: 1px solid #eee;
    border-radius: 4px;
}

.item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.item:nth-child(even) {
    background-color: #f9f9f9;
}

.transaction-summary {
    background: #f5f7fa;
    padding: 15px;
    border-radius: 4px;
    margin-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.summary-row.total {
    font-weight: bold;
    border-top: 1px solid #ddd;
    padding-top: 8px;
    margin-top: 8px;
}

.btn-primary {
    background: #2ecc71;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-secondary {
    background: #3498db;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-danger {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 24px;
    border-radius: 4px;
    color: white;
    z-index: 1000;
}

.toast.success {
    background: #2ecc71;
}

.toast.error {
    background: #e74c3c;
}

@media (max-width: 768px) {
    .transaction-container {
        grid-template-columns: 1fr;
    }
    
    .transaction-items-section {
        grid-column: span 1;
    }
}
</style>


</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <input class="checkbox" type="checkbox" name="" id="" />
            <div class="hamburger-lines">
                <span class="line line1"></span>
                <span class="line line2"></span>
                <span class="line line3"></span>
            </div>  
            <div class="logo">ElectroShop</div>
            <div class="menu-items" style="overflow-y: auto; max-height: 80vh;">
    <li><a href="#" onclick="showPage('tambah-produk')">Tambah Produk</a></li>
    <li><a href="#" onclick="showPage('daftar-produk')">Daftar Produk</a></li>
    <li><a href="transaksi.php" onclick="showPage('transaksi')">Transaksi</a></li>
    <li><a href="#" onclick="showPage('statistik')">Statistik Penjualan</a></li>
    <li><a href="promos.php" onclick="showPage('promos')">Promo</a></li>
    <li><a href="customers.php" onclick="showPage('customers')">Customers</a></li>
    <li><a href="customer_history.php" onclick="showPage('customer history')">Customer History</a></li>
    <li><a href="dashboard.php" onclick="showPage('dashboard')">Dashboard</a></li>
    <li><a href="settings.php" onclick="showPage('settings')">Settings</a></li>
    <li><a href="print_receipt.php" onclick="showPage('print receipt')">Print Receipt</a></li>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="users.php">Manajemen User</a></li>
    <?php endif; ?>
    
    <li><a href="laporan.php" onclick="showPage('laporan')">Laporan</a></li>
    
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="backup.php">Backup Database</a></li>
    <?php endif; ?>

    <li><a href="logout.php" onclick="showPage('Log Out')">Log Out</a></li>

</div>

        </div>
        <div class="dark-mode-toggle" style="position: absolute; right: 150px; top: 15px;">
            <label class="switch">
                <input type="checkbox" id="dark-mode-toggle">
                <span class="slider round"></span>
            </label>
            <span style="margin-left: 5px;">Dark Mode</span>
        </div>
<div class="notifications" style="position: absolute; right: 250px; top: 15px;">
    <?php if (!empty($low_stock_products)): ?>
        <div class="notification-icon" style="position: relative;">
            <i class="fas fa-bell" style="font-size: 1.2rem; color: #e74c3c; cursor: pointer;"></i>
            <span class="badge" style="
                position: absolute;
                top: -5px;
                right: -5px;
                background: #e74c3c;
                color: white;
                border-radius: 50%;
                width: 18px;
                height: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.7rem;
            "><?= count($low_stock_products) ?></span>
            
            <div class="notification-dropdown" style="
                display: none;
                position: absolute;
                right: 0;
                top: 30px;
                background: white;
                border-radius: 4px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                width: 250px;
                z-index: 1000;
                padding: 1rem;
            ">
                <h4 style="margin-bottom: 0.5rem;">Stok Hampir Habis</h4>
                <ul style="list-style: none;">
                    <?php foreach ($low_stock_products as $product): ?>
                        <li style="padding: 0.3rem 0; border-bottom: 1px solid #eee;"><?= htmlspecialchars($product) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
        </div>

    <?php endif; ?>
</div>

<script>
// Toggle dropdown notifikasi
document.querySelector('.notification-icon').addEventListener('click', function() {
    const dropdown = this.querySelector('.notification-dropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
});
</script>
        
    </nav>

    <main class="container">
        <div id="tambah-produk" class="page">
            <h1>Tambah Produk</h1>
            <form id="tambah-produk-form" action="add_product.php" method="POST">
                <label for="nama-produk">Nama Produk:</label>
                <input type="text" id="nama-produk" name="nama-produk" required>
                
                <label for="jumlah-stok">Jumlah Stok:</label>
                <input type="number" id="jumlah-stok" name="jumlah-stok" required>
                
                <label for="harga">Harga:</label>
                <input type="number" id="harga" name="harga" required>
                
                <button type="submit">Tambah Produk</button>
            </form>
        </div>
        <div id="statistik" class="page">
            <div class="chart-container" style="background: white; padding: 15px 20px; border-radius: 8px; margin-top: 20px; max-width: 600px; margin: 20px auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h2 style="font-size: 1.4rem; margin-bottom: 15px;">Statistik Penjualan</h2>
                <div style="position: relative; height: 600px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>



        <div id="daftar-produk" class="page">
    <h1>Daftar Produk</h1>
    
        <button onclick="exportToExcel()" style="background: #27ae60; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 20px;">
            <i class="fas fa-file-excel"></i> Export ke Excel
        </button>
    <!-- Search Box -->
    <div class="search-container" style="margin-bottom: 20px;">
        <input type="text" id="search-input" placeholder="Cari produk..." style="padding: 8px; width: 300px; border-radius: 4px; border: 1px solid #ddd;">
    </div>
<!-- Tambahkan di atas tabel produk -->
<div class="filters" style="margin-bottom: 20px; display: flex; gap: 10px;">
    <select id="price-filter" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
        <option value="">Filter Harga</option>
        <option value="0-500000">Rp 0 - 500,000</option>
        <option value="500000-1000000">Rp 500,000 - 1,000,000</option>
        <option value="1000000-">> Rp 1,000,000</option>
    </select>
    
    <select id="stock-filter" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
        <option value="">Filter Stok</option>
        <option value="0">Habis</option>
        <option value="1-10">1-10</option>
        <option value="10-">> 10</option>
    </select>
</div>

<script>
// Tambahkan filter functionality
document.getElementById('price-filter').addEventListener('change', filterProducts);
document.getElementById('stock-filter').addEventListener('change', filterProducts);

function filterProducts() {
    const priceFilter = document.getElementById('price-filter').value;
    const stockFilter = document.getElementById('stock-filter').value;
    const rows = document.querySelectorAll('#tabel-produk tbody tr');
    
    rows.forEach(row => {
        const price = parseInt(row.cells[2].textContent.replace(/\D/g,''));
        const stock = parseInt(row.cells[1].textContent);
        let priceMatch = true;
        let stockMatch = true;
        
        if (priceFilter) {
            const [min, max] = priceFilter.split('-');
            priceMatch = max ? 
                (price >= parseInt(min) && price <= parseInt(max)) : 
                (price >= parseInt(min));
        }
        
        if (stockFilter) {
            const [min, max] = stockFilter.split('-');
            stockMatch = max ? 
                (stock >= parseInt(min) && stock <= parseInt(max)) : 
                (min === '0' ? stock === 0 : stock >= parseInt(min));
        }
        
        row.style.display = (priceMatch && stockMatch) ? '' : 'none';
    });
}
</script>
    <table id="tabel-produk">
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Jumlah Stok</th>
                <th>Harga</th>
                <th>aksi</th>

            </tr>
        </thead>
        <tbody>
            <?php
            include 'config.php';
            $itemsPerPage = 5;
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $itemsPerPage;

            try {
                $totalItems = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
                $totalPages = ceil($totalItems / $itemsPerPage);

                $stmt = $conn->prepare("SELECT * FROM products LIMIT :limit OFFSET :offset");
                $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($products as $product) {
                    echo "<tr data-id='{$product['id']}'>";
                    echo "<td>" . htmlspecialchars($product['nama']) . "</td>";
                    echo "<td>" . $product['stok'] . "</td>";
                    echo "<td>Rp " . number_format($product['harga'], 0, ',', '.') . "</td>";
                    echo "<td>
                        <button onclick='editProduct({$product['id']})' style='background: #3498db; padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer; margin-right: 5px;'>Edit</button>
                        <button onclick='confirmDelete({$product['id']})' style='background: #e74c3c; padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer;'>Hapus</button>
                    </td>";
                    echo "</tr>";
                }
            } catch(PDOException $e) {
                echo "<tr><td colspan='3'>Error: ".$e->getMessage()."</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" style="margin: 0 5px; padding: 5px 10px; background: #2ecc71; color: white; border-radius: 4px; text-decoration: none;">Sebelumnya</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" style="margin: 0 5px; padding: 5px 10px; background: <?= $page == $i ? '#3498db' : '#2ecc71' ?>; color: white; border-radius: 4px; text-decoration: none;"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" style="margin: 0 5px; padding: 5px 10px; background: #2ecc71; color: white; border-radius: 4px; text-decoration: none;">Selanjutnya</a>
        <?php endif; ?>
    </div>
</div>

<!-- Script untuk Fitur Pencarian -->
<script>
document.getElementById('search-input').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#tabel-produk tbody tr');

    rows.forEach(row => {
        const productName = row.cells[0].textContent.toLowerCase();
        row.style.display = productName.includes(searchTerm) ? '' : 'none';
    });
});
</script>


<!-- Script untuk Fitur Pencarian -->
<script>
document.getElementById('search-input').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#tabel-produk tbody tr');
    
    rows.forEach(row => {
        const productName = row.cells[0].textContent.toLowerCase();
        row.style.display = productName.includes(searchTerm) ? '' : 'none';
    });
});
</script>

        <div id="pembayaran" class="page">
            <h1>Pembayaran</h1>
            <?php if (isset($_SESSION['transaction'])): ?>
                <p>Total yang harus dibayar: <span id="total-pembayaran">Rp <?= number_format($_SESSION['transactions']['total_price'], 0, ',', '.') ?></span></p>
            <?php endif; ?>
            <form id="pembayaran-form" action="complete_payment.php" method="POST">
                <div class="payment-methods">
                    <div class="payment-method">
                        <input type="radio" id="tunai" name="metode-pembayaran" value="tunai" checked>
                        <label for="tunai">Tunai</label>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="kartu-kredit" name="metode-pembayaran" value="kartu-kredit">
                        <label for="kartu-kredit">Kartu Kredit</label>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="e-wallet" name="metode-pembayaran" value="e-wallet">
                        <label for="e-wallet">E-Wallet</label>
                    </div>
                </div>
                
                <label for="jumlah-pembayaran">Jumlah Pembayaran:</label>
                <input type="number" id="jumlah-pembayaran" name="jumlah-pembayaran" required>
                
                <button type="submit">Bayar</button>
            </form>
            <div id="kembalian">
                <?php
                if (isset($_GET['success'])) {
                    echo '<div class="success-message">
                        <p>Pembayaran berhasil!</p>
                        <p>Metode Pembayaran: '.htmlspecialchars($_GET['method']).'</p>
                        <p>Total Pembayaran: Rp '.number_format($_GET['total'], 0, ',', '.').'</p>
                        <p>Jumlah Dibayar: Rp '.number_format($_GET['paid'], 0, ',', '.').'</p>
                        <p>Kembalian: Rp '.number_format($_GET['change'], 0, ',', '.').'</p>
                    </div>';
                } elseif (isset($_GET['error'])) {
                    echo '<div class="error-message">Terjadi kesalahan saat memproses pembayaran.</div>';
                }
                ?>
            </div>
        </div>
    </main>

    <script>
        // Fungsi untuk menampilkan halaman
        function showPage(pageId) {
            document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));
            document.getElementById(pageId).classList.add('active');
            
            // Update URL tanpa reload halaman
            history.pushState(null, null, '?page=' + pageId);
            
            // Close mobile menu after selecting a page
            document.querySelector('.checkbox').checked = false;
        }
        
        // Hitung total harga saat memilih produk dan jumlah
        document.getElementById('transaksi-form').addEventListener('input', function() {
            const productSelect = document.getElementById('produk');
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const harga = selectedOption.getAttribute('data-harga');
            const jumlah = document.getElementById('jumlah').value;
            
            if (harga && jumlah) {
                const total = harga * jumlah;
                document.getElementById('total-harga').value = `Rp ${parseInt(total).toLocaleString('id-ID')}`;
            }
        });
        
        // Tampilkan halaman berdasarkan parameter URL saat pertama kali dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page') || 'tambah-produk';
            showPage(page);
            
            // Tangani notifikasi
            const success = urlParams.get('success');
            const error = urlParams.get('error');
            
            if (page === 'daftar-produk' && success) {
                alert('Produk berhasil ditambahkan!');
            }
        });
    </script>

    <!-- Tambahkan sebelum </body> -->
<div id="toast" style="position: fixed; bottom: 20px; right: 20px; background: #2ecc71; color: white; padding: 12px 24px; border-radius: 4px; display: none; z-index: 1000;"></div>

<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.backgroundColor = type === 'success' ? '#2ecc71' : '#e74c3c';
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Contoh penggunaan:

// showToast('Terjadi kesalahan', 'error');
</script>

<script>
function confirmDelete(productId) {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        window.location.href = `delete_product.php?id=${productId}`;
    }
}
</script>


<script>
function editProduct(productId) {
    const row = document.querySelector(`tr[data-id="${productId}"]`);
    const cells = row.cells;

    const originalValues = {
        name: cells[0].textContent,
        stock: cells[1].textContent,
        price: cells[2].textContent.replace(/\D/g, '')
    };

    cells[0].innerHTML = `<input type="text" value="${originalValues.name}" style="width: 100%; padding: 5px;">`;
    cells[1].innerHTML = `<input type="number" value="${originalValues.stock}" style="width: 100%; padding: 5px;">`;
    cells[2].innerHTML = `<input type="number" value="${originalValues.price}" style="width: 100%; padding: 5px;">`;

    cells[3].innerHTML = `
        <button onclick="saveProduct(${productId})" style="background: #2ecc71; padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer; margin-right: 5px;">Simpan</button>
        <button onclick='cancelEdit(${productId}, ${JSON.stringify(originalValues)})' style='background: #95a5a6; padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer;'>Batal</button>
    `;
}

function saveProduct(productId) {
    const row = document.querySelector(`tr[data-id="${productId}"]`);
    const inputs = row.querySelectorAll('input');

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'update_product.php';

    form.innerHTML = `
        <input type="hidden" name="id" value="${productId}">
        <input type="hidden" name="nama" value="${inputs[0].value}">
        <input type="hidden" name="stok" value="${inputs[1].value}">
        <input type="hidden" name="harga" value="${inputs[2].value}">
    `;

    document.body.appendChild(form);
    form.submit();
}

function cancelEdit(productId, originalValues) {
    const row = document.querySelector(`tr[data-id="${productId}"]`);
    const cells = row.cells;

    cells[0].textContent = originalValues.name;
    cells[1].textContent = originalValues.stock;
    cells[2].textContent = 'Rp ' + parseInt(originalValues.price).toLocaleString('id-ID');

    cells[3].innerHTML = `
        <button onclick='editProduct(${productId})' style='background: #3498db; padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer; margin-right: 5px;'>Edit</button>
        <button onclick='confirmDelete(${productId})' style='background: #e74c3c; padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer;'>Hapus</button>
    `;
}
</script>

<!-- buat statistik chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('salesChart')) {
        fetch('sales_data.php')
            .then(res => res.json())
            .then(data => {
                const ctx = document.getElementById('salesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Total Penjualan per Bulan (Rp)',
                            data: data.data,
                            backgroundColor: 'rgba(52, 152, 219, 0.2)',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    }
});
</script>


<script>
document.getElementById('dark-mode-toggle').addEventListener('change', function() {
    document.body.classList.toggle('dark-mode', this.checked);
    localStorage.setItem('darkMode', this.checked);
});

// Cek preferensi dark mode saat load
if (localStorage.getItem('darkMode') === 'true') {
    document.getElementById('dark-mode-toggle').checked = true;
    document.body.classList.add('dark-mode');
}
</script>

<!-- buat file excel -->
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
<script>
function exportToExcel() {
    const table = document.getElementById('tabel-produk');
    const workbook = XLSX.utils.table_to_book(table);
    XLSX.writeFile(workbook, 'daftar_produk.xlsx');
}
</script>

</body>
</html>