<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin(); // Pastikan user sudah login

header("Content-Type: application/sql");
header("Content-Disposition: attachment; filename=backup_" . date('Ymd_His') . ".sql");

$tables = ['users', 'items', 'claims'];

foreach ($tables as $table) {
    echo "-- Dump of table `$table`\n";
    echo "-- --------------------------------------------------------\n\n";

    echo "DROP TABLE IF EXISTS `$table`;\n";

    $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
    $create = $stmt->fetch();
    echo $create['Create Table'] . ";\n\n";

    $rows = $pdo->query("SELECT * FROM `$table`");
    foreach ($rows as $row) {
        $columns = array_map(fn($col) => "`$col`", array_keys($row));
        $values = array_map(function($val) use ($pdo) {
            return $val === null ? 'NULL' : $pdo->quote($val);
        }, array_values($row));

        echo "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
    }

    echo "\n\n";
}

exit;
