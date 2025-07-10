
<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'config.php';

if (isset($_GET['backup'])) {
    try {
        $tables = array();
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $return = '';
        foreach ($tables as $table) {
            $result = $conn->query("SELECT * FROM $table");
            $num_fields = $result->columnCount();
            
            $return .= "DROP TABLE IF EXISTS $table;";
            $create_table = $conn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM);
            $return .= "\n\n" . $create_table[1] . ";\n\n";
            
            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $return .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }
                        if ($j < ($num_fields - 1)) {
                            $return .= ',';
                        }
                    }
                    $return .= ");\n";
                }
            }
            $return .= "\n\n\n";
        }
        
        // Simpan file
        $backup_name = "backup_" . date('Y-m-d_H-i-s') . ".sql";
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup_name . '"');
        echo $return;
        exit();
        
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: backup.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Backup Database - ElectroShop</title>
    <!-- Gunakan CSS yang sama dengan index.php -->
</head>
<body>
    <!-- Sertakan navbar yang sama -->
    
    <main class="container">
        <h1>Backup Database</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 3rem;">
            <p style="margin-bottom: 2rem;">Klik tombol di bawah ini untuk melakukan backup seluruh database</p>
            <a href="backup.php?backup=1" class="btn-backup" style="
                background-color: #3498db;
                color: white;
                padding: 1rem 2rem;
                border-radius: 4px;
                text-decoration: none;
                font-size: 1.2rem;
                display: inline-block;
            ">
                <i class="fas fa-database"></i> Backup Sekarang
            </a>
        </div>
    </main>
</body>
</html>