<?php
require_once 'config.php';

// Direktori untuk menyimpan backup
$backup_dir = 'backups/';
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Nama file backup
$backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// Dapatkan semua tabel
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

// Simpan ke file
$handle = fopen($backup_file, 'w+');
fwrite($handle, $return);
fclose($handle);

// Hapus backup lama (simpan hanya 7 backup terbaru)
$backups = glob($backup_dir . '*.sql');
if (count($backups) > 7) {
    usort($backups, function($a, $b) {
        return filemtime($a) < filemtime($b);
    });
    
    for ($i = 7; $i < count($backups); $i++) {
        unlink($backups[$i]);
    }
}

echo "Backup berhasil dibuat: " . $backup_file;
?>