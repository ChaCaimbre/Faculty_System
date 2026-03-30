<?php
require_once 'config.php';
$db = getDB();
$db->query("DELETE FROM academic_terms WHERE name = '2nd Semester 2025-2026'");
echo "Deleted 2nd Semester 2025-2026 if it existed.\n";
?>
