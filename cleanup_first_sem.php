<?php
require_once 'config.php';
$db = getDB();

// We'll target Term ID 1 which is "1st Semester 2024-2025"
$term_id = 1;

// 1. Delete all schedules for this term.
$db->query("DELETE FROM schedules WHERE term_id = $term_id");
$deleted_schedules = $db->affected_rows;

// 2. Delete subjects for this term as well (since they are isolated now).
$db->query("DELETE FROM subjects WHERE term_id = $term_id");
$deleted_subjects = $db->affected_rows;

echo "CLEANUP SUCCESSFUL:\n";
echo "- Removed $deleted_schedules schedule entries from the First Semester.\n";
echo "- Removed $deleted_subjects assigned subjects from the First Semester.\n";
?>
