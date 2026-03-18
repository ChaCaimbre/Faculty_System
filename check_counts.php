<?php
require_once 'config.php';
$db = getDB();
echo "Subjects (Term 1): " . $db->query("SELECT count(*) FROM subjects WHERE term_id = 1")->fetch_row()[0] . "\n";
echo "Subjects (Term 2): " . $db->query("SELECT count(*) FROM subjects WHERE term_id = 2")->fetch_row()[0] . "\n";
echo "Schedules (Term 1): " . $db->query("SELECT count(*) FROM schedules WHERE term_id = 1")->fetch_row()[0] . "\n";
echo "Schedules (Term 2): " . $db->query("SELECT count(*) FROM schedules WHERE term_id = 2")->fetch_row()[0] . "\n";
