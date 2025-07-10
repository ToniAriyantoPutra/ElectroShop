<?php
session_start();
require 'config.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil data produk dan pelanggan
try {
    $products = $conn->query("SELECT id, nama, harga, stok, barcode FROM products WHERE stok > 0 ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
    $customers = $conn->query("SELECT id, name FROM customers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil data dari tabel settings
    $settings = $conn->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch(PDOException $e) {
    $_SESSION['error'] = "Gagal memuat data: " . $e->getMessage();
}

// Proses form jika ada data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Validasi data
        if (empty($_POST['transaction_items'])) {
            throw new Exception("Tidak ada item transaksi");
        }
        
        $transaction_items = json_decode($_POST['transaction_items'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Format item transaksi tidak valid");
        }
        
        // Data transaksi utama
        $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
        $payment_method = $_POST['payment_method'];
        $amount_paid = $_POST['amount_paid'] ?? 0;
        $discount = $_POST['discount'] ?? 0;
        $promo_code = !empty($_POST['promo_code']) ? $_POST['promo_code'] : null;
        $user_id = $_SESSION['user_id'];
        
        // Hitung total dari item transaksi
        $total_price = 0;
        foreach ($transaction_items as $item) {
            // Cek apakah product_id ada dalam tabel products
            $checkProductStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE id = :product_id");
            $checkProductStmt->execute([':product_id' => $item['product_id']]);
            $productExists = $checkProductStmt->fetchColumn();

            if (!$productExists) {
                throw new Exception("Produk dengan ID {$item['product_id']} tidak ditemukan.");
            }

            $total_price += $item['price'] * $item['quantity'];
        }
        
        $total_price -= $discount;
        
        // Validasi pembayaran tunai
        if ($payment_method === 'tunai' && $amount_paid < $total_price) {
            throw new Exception("Jumlah pembayaran tidak mencukupi");
        }
        
        $change_amount = $payment_method === 'tunai' ? ($amount_paid - $total_price) : 0;
        
        // Simpan transaksi utama
        $stmt = $conn->prepare("
            INSERT INTO transactions (
                customer_id, 
                total_price, 
                payment_method, 
                product_name,
                quantity, 
                amount_paid, 
                change_amount, 
                discount, 
                promo_code,
                user_id,
                transaction_date,
                shop_address,
                customer_name,
                shop_phone,
                receipt_footer,
                shop_name
            ) VALUES (
                :customer_id, 
                :total_price, 
                :payment_method,
                :product_name, 
                :quantity,
                :amount_paid, 
                :change_amount, 
                :discount, 
                :promo_code,
                :user_id,
                NOW(),
                :shop_address,
                :customer_name,
                :shop_phone,
                :receipt_footer,
                :shop_name
            )
        ");
        
        $stmt->execute([
            ':customer_id' => $customer_id,
            ':total_price' => $total_price,
            ':payment_method' => $payment_method,
            ':product_name' => $item['product_name'],
            ':quantity' => $item['quantity'],
            ':amount_paid' => $amount_paid,
            ':change_amount' => $change_amount,
            ':discount' => $discount,
            ':promo_code' => $promo_code,
            ':user_id' => $user_id,
            ':shop_address' => $settings['shop_address'],
            ':customer_name' => $customer_id ? $customers[array_search($customer_id, array_column($customers, 'id'))]['name'] : 'Tanpa Pelanggan',
            ':shop_phone' => $settings['shop_phone'],
            ':receipt_footer' => $settings['receipt_footer'],
            ':shop_name' => $settings['shop_name']
        ]);
        
        $transaction_id = $conn->lastInsertId();
        
        // Simpan detail item transaksi dan update stok
        foreach ($transaction_items as $item) {
            // Simpan detail transaksi
            $detail_stmt = $conn->prepare("
                INSERT INTO transaction_items (
                    transaction_id, 
                    product_id, 
                    product_name, 
                    quantity, 
                    price
                ) VALUES (
                    :transaction_id, 
                    :product_id, 
                    :product_name, 
                    :quantity, 
                    :price
                )
            ");
            
            $detail_stmt->execute([
                ':transaction_id' => $transaction_id,
                ':product_id' => $item['product_id'], // Pastikan product_id valid
                ':product_name' => $item['product_name'],
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);
            
            // Update stok produk
            $update_stmt = $conn->prepare("
                UPDATE products 
                SET stok = stok - :quantity 
                WHERE id = :product_id AND stok >= :quantity
            ");
            
            $update_stmt->execute([
                ':product_id' => $item['product_id'], // Pastikan product_id valid
                ':quantity' => $item['quantity']
            ]);
            
            if ($update_stmt->rowCount() === 0) {
                throw new Exception("Stok produk tidak mencukupi untuk {$item['product_name']}");
            }
        }
        
        $conn->commit();
        
        // Redirect ke halaman struk
        header("Location: print_receipt.php?id=$transaction_id");
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = 'Transaksi gagal: ' . $e->getMessage();
        header('Location: transaksi.php');
        exit();
    }
}
?>




<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - ElectroShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root {
            --primary: #3498db;
            --secondary: #2ecc71;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            color: var(--primary);
            margin-bottom: 10px;
        }

        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: var(--danger);
            color: white;
        }

        .transaction-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .transaction-wrapper {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #27ae60;
        }

        .btn-secondary {
            background-color: var(--primary);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }

        .items-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 4px;
            margin: 15px 0;
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

        .item-info {
            flex: 1;
        }

        .item-price {
            margin-left: 10px;
            font-weight: 500;
        }

        .summary {
            background-color: var(--light);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .summary-total {
            font-weight: bold;
            font-size: 1.1em;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            margin-top: 8px;
        }

        .hidden {
            display: none;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            display: none;
        }

        .toast-success {
            background-color: var(--secondary);
        }

        .toast-error {
            background-color: var(--danger);
        }

        .customer-form {
            margin-top: 15px;
            padding: 15px;
            background-color: var(--light);
            border-radius: 4px;
            display: none;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Transaksi</h1>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="transaction-wrapper">
            <!-- Bagian Input Produk -->
            <div class="product-input">
                <div class="card">
                    <div class="form-group">
                        <label for="barcode-input">Scan Barcode</label>
                        <input type="text" id="barcode-input" class="form-control" placeholder="Scan barcode produk..." autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="product-select">Pilih Produk</label>
                        <select id="product-select" class="form-control">
                            <option value="">-- Pilih Produk --</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" 
                                        data-price="<?= $product['harga'] ?>" 
                                        data-stock="<?= $product['stok'] ?>"
                                        data-barcode="<?= $product['barcode'] ?>">
                                    <?= htmlspecialchars($product['nama']) ?> - Rp <?= number_format($product['harga'], 0, ',', '.') ?> (Stok: <?= $product['stok'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Jumlah</label>
                        <input type="number" id="quantity" class="form-control" min="1" value="1">
                    </div>
                    
                    <button id="add-item-btn" class="btn btn-primary">Tambah ke Transaksi</button>
                </div>
                
                <div class="card">
                    <h3>Item Transaksi</h3>
                    <div class="items-list" id="items-list">
                        <!-- Item akan muncul di sini -->
                    </div>
                </div>
            </div>
            
            <!-- Bagian Pembayaran -->
            <div class="payment-section">
                <form id="transaction-form" method="POST" class="card">
                    <div class="form-group">
                        <label for="customer-select">Pelanggan</label>
                        <select id="customer-select" name="customer_id" class="form-control">
                            <option value="">-- Tanpa Pelanggan --</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>"><?= htmlspecialchars($customer['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="new-customer-btn" class="btn btn-secondary" style="margin-top: 5px;">
                            <i class="fas fa-plus"></i> Pelanggan Baru
                        </button>
                        
                        <div id="new-customer-form" class="customer-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="customer-name">Nama</label>
                                    <input type="text" id="customer-name" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="customer-phone">Telepon</label>
                                    <input type="text" id="customer-phone" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="customer-email">Email</label>
                                    <input type="email" id="customer-email" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="customer-address">Alamat</label>
                                    <input type="text" id="customer-address" class="form-control">
                                </div>
                            </div>
                            <button type="button" id="save-customer-btn" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Pelanggan
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="promo-code">Kode Promo</label>
                        <div style="display: flex; gap: 5px;">
                            <input type="text" id="promo-code" class="form-control" placeholder="Masukkan kode promo">
                            <button type="button" id="apply-promo-btn" class="btn btn-secondary">Terapkan</button>
                        </div>
                        <div id="promo-info" style="margin-top: 5px; display: none;">
                            <span id="promo-description" style="color: var(--secondary);"></span>
                            <button type="button" id="remove-promo-btn" class="btn btn-danger btn-sm">Hapus</button>
                        </div>
                    </div>
                    
                    <div class="summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">Rp 0</span>
                        </div>
                        <div class="summary-row">
                            <span>Diskon:</span>
                            <span id="discount">Rp 0</span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span id="total">Rp 0</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment-method">Metode Pembayaran</label>
                        <select id="payment-method" name="payment_method" class="form-control" required>
                            <option value="tunai">Tunai</option>
                            <option value="kartu_debit">Kartu Debit</option>
                            <option value="kartu_kredit">Kartu Kredit</option>
                            <option value="e-wallet">E-Wallet</option>
                        </select>
                    </div>
                    
                    <div id="cash-payment" class="form-group">
                        <label for="amount-paid">Jumlah Dibayar</label>
                        <input type="number" id="amount-paid" name="amount_paid" class="form-control" min="0">
                        <div style="margin-top: 5px;">
                            <span>Kembalian:</span>
                            <span id="change-amount">Rp 0</span>
                        </div>
                    </div>
                    
                    <input type="hidden" id="transaction-items" name="transaction_items">
                    <input type="hidden" id="transaction-discount" name="discount" value="0">
                    
                    <button type="submit" id="process-transaction-btn" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-cash-register"></i> Proses Transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div id="toast" class="toast"></div>
    
    <script>
        // Variabel global
        let transactionItems = [];
        let currentPromo = null;
        
        // DOM Elements
        const productSelect = document.getElementById('product-select');
        const quantityInput = document.getElementById('quantity');
        const itemsList = document.getElementById('items-list');
        const addItemBtn = document.getElementById('add-item-btn');
        const barcodeInput = document.getElementById('barcode-input');
        const customerSelect = document.getElementById('customer-select');
        const newCustomerBtn = document.getElementById('new-customer-btn');
        const newCustomerForm = document.getElementById('new-customer-form');
        const saveCustomerBtn = document.getElementById('save-customer-btn');
        const promoCodeInput = document.getElementById('promo-code');
        const applyPromoBtn = document.getElementById('apply-promo-btn');
        const removePromoBtn = document.getElementById('remove-promo-btn');
        const promoInfo = document.getElementById('promo-info');
        const promoDescription = document.getElementById('promo-description');
        const paymentMethod = document.getElementById('payment-method');
        const amountPaidInput = document.getElementById('amount-paid');
        const subtotalElement = document.getElementById('subtotal');
        const discountElement = document.getElementById('discount');
        const totalElement = document.getElementById('total');
        const changeAmountElement = document.getElementById('change-amount');
        const transactionForm = document.getElementById('transaction-form');
        const transactionItemsInput = document.getElementById('transaction-items');
        const transactionDiscountInput = document.getElementById('transaction-discount');
        const toast = document.getElementById('toast');
        
        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Tampilkan/sembunyikan input pembayaran tunai
            paymentMethod.addEventListener('change', function() {
                document.getElementById('cash-payment').style.display = 
                    this.value === 'tunai' ? 'block' : 'none';
            });
            
            // Hitung kembalian saat jumlah dibayar diubah
            amountPaidInput.addEventListener('input', calculateChange);
            
            // Tambahkan item ke transaksi
            addItemBtn.addEventListener('click', addItemToTransaction);
            
            // Barcode scanner
            barcodeInput.addEventListener('change', scanBarcode);
            
            // Pelanggan baru
            newCustomerBtn.addEventListener('click', function() {
                newCustomerForm.style.display = 'block';
            });
            
            saveCustomerBtn.addEventListener('click', saveNewCustomer);
            
            // Promo
            applyPromoBtn.addEventListener('click', applyPromo);
            removePromoBtn.addEventListener('click', removePromo);
            
            // Proses transaksi
            transactionForm.addEventListener('submit', function(e) {
                if (transactionItems.length === 0) {
                    e.preventDefault();
                    showToast('Tambahkan minimal 1 item ke transaksi', 'error');
                    return;
                }
                
                // Validasi pembayaran tunai
                if (paymentMethod.value === 'tunai') {
                    const totalText = totalElement.textContent.replace(/[^\d]/g, '');
                    const total = parseFloat(totalText) || 0;
                    const amountPaid = parseFloat(amountPaidInput.value) || 0;
                    
                    if (amountPaid < total) {
                        e.preventDefault();
                        showToast('Jumlah pembayaran tidak mencukupi', 'error');
                        return;
                    }
                }
                
                // Set nilai hidden input sebelum submit
                transactionItemsInput.value = JSON.stringify(transactionItems);
            });
        });
        
        // Fungsi untuk menambahkan item ke transaksi
        function addItemToTransaction() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productId = productSelect.value;
            const quantity = parseInt(quantityInput.value);
            
            if (!productId) {
                showToast('Pilih produk terlebih dahulu', 'error');
                return;
            }
            
            if (isNaN(quantity) || quantity < 1) {
                showToast('Masukkan jumlah yang valid', 'error');
                return;
            }
            
            const stock = parseInt(selectedOption.getAttribute('data-stock'));
            
            if (quantity > stock) {
                showToast('Stok tidak mencukupi', 'error');
                return;
            }
            
            // Cek apakah produk sudah ada di transaksi
            const existingItemIndex = transactionItems.findIndex(item => item.product_id == productId);
            
            if (existingItemIndex >= 0) {
                // Update quantity jika produk sudah ada
                transactionItems[existingItemIndex].quantity += quantity;
            } else {
                // Tambahkan produk baru ke transaksi
                transactionItems.push({
                    product_id: productId,
                    product_name: selectedOption.text.split(' - ')[0],
                    price: parseFloat(selectedOption.getAttribute('data-price')),
                    quantity: quantity
                });
            }
            
            // Update tampilan
            updateItemsList();
            updateSummary();
            
            // Reset input
            productSelect.value = '';
            quantityInput.value = '1';
            barcodeInput.value = '';
            productSelect.focus();
            
            showToast('Produk berhasil ditambahkan', 'success');
        }
        
        // Fungsi untuk scan barcode
        function scanBarcode() {
            const barcode = this.value.trim();
            if (!barcode) return;
            
            // Cari produk berdasarkan barcode
            const options = productSelect.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].getAttribute('data-barcode') === barcode) {
                    productSelect.value = options[i].value;
                    quantityInput.focus();
                    showToast('Produk ditemukan', 'success');
                    this.value = '';
                    return;
                }
            }
            
            showToast('Produk tidak ditemukan', 'error');
            this.value = '';
        }
        
        // Fungsi untuk update daftar item
        function updateItemsList() {
            itemsList.innerHTML = '';
            
            transactionItems.forEach((item, index) => {
                const itemElement = document.createElement('div');
                itemElement.className = 'item';
                
                const itemTotal = item.price * item.quantity;
                
                itemElement.innerHTML = `
                    <div class="item-info">
                        ${item.product_name} (${item.quantity} x Rp ${item.price.toLocaleString('id-ID')})
                    </div>
                    <div class="item-price">
                        Rp ${itemTotal.toLocaleString('id-ID')}
                    </div>
                    <button onclick="removeItem(${index})" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                
                itemsList.appendChild(itemElement);
            });
        }
        
        // Fungsi untuk menghapus item
        function removeItem(index) {
            transactionItems.splice(index, 1);
            updateItemsList();
            updateSummary();
            showToast('Item dihapus dari transaksi', 'success');
        }
        
        // Fungsi untuk update ringkasan transaksi
        function updateSummary() {
            let subtotal = 0;
            
            transactionItems.forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            let discount = 0;
            if (currentPromo) {
                if (currentPromo.discount_percent > 0) {
                    discount = subtotal * (currentPromo.discount_percent / 100);
                } else if (currentPromo.discount_amount > 0) {
                    discount = currentPromo.discount_amount;
                }
                
                // Pastikan diskon tidak melebihi subtotal
                if (discount > subtotal) {
                    discount = subtotal;
                }
            }
            
            const total = subtotal - discount;
            
            subtotalElement.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
            discountElement.textContent = `Rp ${discount.toLocaleString('id-ID')}`;
            totalElement.textContent = `Rp ${total.toLocaleString('id-ID')}`;
            
            // Update hidden input untuk diskon
            transactionDiscountInput.value = discount;
            
            // Hitung kembalian jika metode tunai
            if (paymentMethod.value === 'tunai') {
                calculateChange();
            }
        }
        
        // Fungsi untuk menghitung kembalian
        function calculateChange() {
            const totalText = totalElement.textContent.replace(/[^\d]/g, '');
            const total = parseFloat(totalText) || 0;
            const amountPaid = parseFloat(amountPaidInput.value) || 0;
            const change = amountPaid - total;
            
            changeAmountElement.textContent = `Rp ${change >= 0 ? change.toLocaleString('id-ID') : '0'}`;
        }
        
        // Fungsi untuk menambahkan pelanggan baru
        function saveNewCustomer() {
            const name = document.getElementById('customer-name').value.trim();
            const phone = document.getElementById('customer-phone').value.trim();
            const email = document.getElementById('customer-email').value.trim();
            const address = document.getElementById('customer-address').value.trim();
            
            if (!name) {
                showToast('Nama pelanggan harus diisi', 'error');
                return;
            }
            
            // Kirim data ke server menggunakan AJAX
            fetch('add_customer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}&email=${encodeURIComponent(email)}&address=${encodeURIComponent(address)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Tambahkan ke select pelanggan
                    const option = document.createElement('option');
                    option.value = data.id;
                    option.textContent = name;
                    customerSelect.appendChild(option);
                    customerSelect.value = data.id;
                    
                    // Reset form
                    document.getElementById('customer-name').value = '';
                    document.getElementById('customer-phone').value = '';
                    document.getElementById('customer-email').value = '';
                    document.getElementById('customer-address').value = '';
                    newCustomerForm.style.display = 'none';
                    
                    showToast('Pelanggan berhasil ditambahkan', 'success');
                } else {
                    showToast(data.message || 'Gagal menambahkan pelanggan', 'error');
                }
            })
            .catch(error => {
                showToast('Terjadi kesalahan: ' + error, 'error');
            });
        }
        
        // Fungsi untuk menerapkan promo
        function applyPromo() {
            const promoCode = promoCodeInput.value.trim();
            if (!promoCode) {
                showToast('Masukkan kode promo', 'error');
                return;
            }
            
            fetch(`check_promo.php?code=${encodeURIComponent(promoCode)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        currentPromo = data.promo;
                        promoDescription.textContent = data.promo.description;
                        promoInfo.style.display = 'block';
                        updateSummary();
                        showToast('Promo berhasil diterapkan', 'success');
                    } else {
                        showToast(data.message || 'Promo tidak valid', 'error');
                    }
                })
                .catch(error => {
                    showToast('Terjadi kesalahan: ' + error, 'error');
                });
        }
        
        // Fungsi untuk menghapus promo
        function removePromo() {
            currentPromo = null;
            promoCodeInput.value = '';
            promoInfo.style.display = 'none';
            updateSummary();
            showToast('Promo dihapus', 'success');
        }
        
        // Fungsi untuk menampilkan notifikasi toast
        function showToast(message, type = 'success') {
            toast.textContent = message;
            toast.className = `toast toast-${type}`;
            toast.style.display = 'block';
            
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>