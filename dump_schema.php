<?php
include 'config.php';
$out = fopen('db_schema_dump.txt', 'w');
try {
    fwrite($out, "DB: " . ($pdo->query("SELECT DATABASE()")->fetchColumn()) . "\n\n");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        fwrite($out, "TABLE: $table\n");
        $stmt = $pdo->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fwrite($out, "  " . $row['Field'] . " (" . $row['Type'] . ")\n");
        }
        fwrite($out, "\n");
    }
} catch (Exception $e) {
    fwrite($out, "ERROR: " . $e->getMessage());
}
fclose($out);
?>
