<?php
/**
 * Restore deleted teachers: Amago and Almenario
 * Run once via: php restore_deleted_teachers.php
 * Or open in browser: http://localhost/Faculty_System/restore_deleted_teachers.php
 */
require_once 'config.php';

$db = getDB();

$teachersToRestore = ['Amago', 'Almenario'];
$restored = [];
$skipped = [];

foreach ($teachersToRestore as $name) {
    $safeName = $db->real_escape_string($name);
    $res = $db->query("SELECT id FROM faculty WHERE name = '$safeName'");
    if ($res && $res->num_rows > 0) {
        $skipped[] = $name . ' (already exists)';
        continue;
    }
    if ($db->query("INSERT INTO faculty (name) VALUES ('$safeName')")) {
        $restored[] = $name . ' (id: ' . $db->insert_id . ')';
    } else {
        $restored[] = $name . ' â€“ ERROR: ' . $db->error;
    }
}

// Output
if (php_sapi_name() === 'cli') {
    echo "Restored: " . implode(", ", $restored) . "\n";
    if (!empty($skipped)) {
        echo "Skipped: " . implode(", ", $skipped) . "\n";
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'restored' => $restored,
        'skipped'  => $skipped,
    ], JSON_PRETTY_PRINT);
}
