<?php
/**
 * SQLite to MySQL Export Script
 * Exports dev SQLite data to MySQL-compatible SQL file
 */

$sqlitePath = __DIR__ . '/database/database.sqlite';
$outputPath = __DIR__ . '/database/production_import.sql';

if (!file_exists($sqlitePath)) {
    die("SQLite database not found at: $sqlitePath\n");
}

$pdo = new PDO("sqlite:$sqlitePath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tables to export (in order - respects foreign keys)
$tables = [
    'payment_methods',
    'institutions',
    'qr_codes',
    'featured_institutions',
    'submissions',
];

// Tables to skip
$skip = ['users', 'sessions', 'jobs', 'failed_jobs', 'job_batches',
         'cache', 'cache_locks', 'password_reset_tokens', 'migrations',
         'personal_access_tokens', 'audit_logs'];

$output = fopen($outputPath, 'w');

fwrite($output, "-- Sedekah.info Production Import\n");
fwrite($output, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
fwrite($output, "-- Source: SQLite Development Database\n\n");
fwrite($output, "SET FOREIGN_KEY_CHECKS=0;\n\n");

function escapeValue($value) {
    if ($value === null) return 'NULL';
    if (is_int($value) || is_float($value)) return $value;
    $value = str_replace("\\", "\\\\", $value);
    $value = str_replace("'", "\\'", $value);
    $value = str_replace("\n", "\\n", $value);
    $value = str_replace("\r", "\\r", $value);
    return "'" . $value . "'";
}

foreach ($tables as $table) {
    // Check table exists
    $exists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'")->fetch();
    if (!$exists) {
        echo "Skipping (not found): $table\n";
        continue;
    }

    $rows = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "Skipping (empty): $table\n";
        continue;
    }

    $count = count($rows);
    echo "Exporting: $table ($count rows)\n";

    fwrite($output, "-- Table: $table ($count rows)\n");
    fwrite($output, "TRUNCATE TABLE `$table`;\n");

    $columns = array_keys($rows[0]);
    $columnList = '`' . implode('`, `', $columns) . '`';

    // Write in batches of 100
    $chunks = array_chunk($rows, 100);
    foreach ($chunks as $chunk) {
        fwrite($output, "INSERT INTO `$table` ($columnList) VALUES\n");
        $valueRows = [];
        foreach ($chunk as $row) {
            $values = array_map('escapeValue', array_values($row));
            $valueRows[] = '(' . implode(', ', $values) . ')';
        }
        fwrite($output, implode(",\n", $valueRows) . ";\n");
    }

    fwrite($output, "\n");
}

fwrite($output, "SET FOREIGN_KEY_CHECKS=1;\n");
fwrite($output, "\n-- Import complete\n");

fclose($output);

echo "\nDone! SQL file saved to: database/production_import.sql\n";
echo "File size: " . number_format(filesize($outputPath) / 1024, 2) . " KB\n";
