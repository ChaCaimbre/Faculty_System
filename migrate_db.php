<?php
require_once 'config.php';
$db = getDB();

// Check if designated_campus exists
$result = $db->query("SHOW COLUMNS FROM faculty LIKE 'designated_campus'");
if ($result->num_rows === 0) {
    echo "Adding designated_campus column...\n";
    $db->query("ALTER TABLE faculty ADD COLUMN designated_campus VARCHAR(100) DEFAULT 'Main Campus'");
    if ($db->error) {
        echo "Error adding column: " . $db->error . "\n";
    } else {
        echo "Column added successfully.\n";
    }
} else {
    echo "Column designated_campus already exists.\n";
}
?>
