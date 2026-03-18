<?php
// One-time migration: add user_id to all data tables for per-user isolation
require_once '../config.php';
$db = getDB();

$steps = [];

// 1. Add user_id to faculty
$c = $db->query("SHOW COLUMNS FROM faculty LIKE 'user_id'");
if ($c->num_rows === 0) {
    $db->query("ALTER TABLE faculty ADD COLUMN user_id INT DEFAULT NULL");
    $steps[] = "Added user_id to faculty";
} else {
    $steps[] = "faculty.user_id already exists";
}

// 2. Add user_id to subjects
$c = $db->query("SHOW COLUMNS FROM subjects LIKE 'user_id'");
if ($c->num_rows === 0) {
    // Remove UNIQUE on code so different users can have same subject code
    $db->query("ALTER TABLE subjects DROP INDEX code");
    $db->query("ALTER TABLE subjects ADD COLUMN user_id INT DEFAULT NULL");
    $steps[] = "Added user_id to subjects (removed global UNIQUE on code)";
} else {
    $steps[] = "subjects.user_id already exists";
}

// 3. Add user_id to rooms
$c = $db->query("SHOW COLUMNS FROM rooms LIKE 'user_id'");
if ($c->num_rows === 0) {
    // Remove UNIQUE on name so different users can have same room name
    $db->query("ALTER TABLE rooms DROP INDEX name");
    $db->query("ALTER TABLE rooms ADD COLUMN user_id INT DEFAULT NULL");
    $steps[] = "Added user_id to rooms (removed global UNIQUE on name)";
} else {
    $steps[] = "rooms.user_id already exists";
}

// 4. Add user_id to schedules
$c = $db->query("SHOW COLUMNS FROM schedules LIKE 'user_id'");
if ($c->num_rows === 0) {
    $db->query("ALTER TABLE schedules ADD COLUMN user_id INT DEFAULT NULL");
    $steps[] = "Added user_id to schedules";
} else {
    $steps[] = "schedules.user_id already exists";
}

// 5. Assign existing data to first admin user (id=1) so nothing is lost
$db->query("UPDATE faculty SET user_id = 1 WHERE user_id IS NULL");
$db->query("UPDATE subjects SET user_id = 1 WHERE user_id IS NULL");
$db->query("UPDATE rooms SET user_id = 1 WHERE user_id IS NULL");
$db->query("UPDATE schedules SET user_id = 1 WHERE user_id IS NULL");
$steps[] = "Existing data assigned to user id=1 (admin)";

echo "<pre>Migration complete:\n" . implode("\n", $steps) . "\n</pre>";
?>
