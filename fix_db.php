<?php
require_once 'config.php';
$db = getDB();

// Add employment_status column if it doesn't exist
$res = $db->query("SHOW COLUMNS FROM faculty LIKE 'employment_status'");
if ($res->num_rows === 0) {
    echo "Adding employment_status column to faculty table...\n";
    if ($db->query("ALTER TABLE faculty ADD COLUMN employment_status VARCHAR(50) DEFAULT 'Full-time'")) {
        echo "Column added successfully.\n";
    }
    else {
        echo "Error adding column: " . $db->error . "\n";
    }
}
else {
    echo "employment_status column already exists.\n";
}

// Also check schedules table for subject_code/subject_name?
// Actually api/schedules.php uses subject_id, but it seems to have logic for code/name.
// Wait, api/teacher_schedule.php uses JOIN subjects sub ON s.subject_id = sub.id.
// So schedules table should have subject_id.
?>
