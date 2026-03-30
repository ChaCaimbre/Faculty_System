<?php
require_once 'config.php';
$db = getDB();

$term_id = 1; // 1st Semester 2024-2025

// 1. Delete all schedules for Term 1
$db->query("DELETE FROM schedules WHERE term_id = $term_id");
$deleted_schedules = $db->affected_rows;

// 2. Delete subjects for Term 1 (as they are isolated now)
$db->query("DELETE FROM subjects WHERE term_id = $term_id");
$deleted_subjects = $db->affected_rows;

echo "--- CLEANUP REPORT (TERM ID $term_id) ---\n";
echo "Deleted Schedules: $deleted_schedules\n";
echo "Deleted Subjects: $deleted_subjects\n";

// 3. Simple verification of Term 2 (should still have entries)
$rem2 = $db->query("SELECT COUNT(*) as c FROM schedules WHERE term_id=2")->fetch_assoc()['c'];
echo "Checking 2nd Semester (Term 2): $rem2 schedules remaining (Untouched).\n";

// 4. Verification for Term 3
$rem3 = $db->query("SELECT COUNT(*) as c FROM schedules WHERE term_id=3")->fetch_assoc()['c'];
echo "Checking Year 2026-2027 (Term 3): $rem3 schedules remaining (Untouched).\n";

?>
