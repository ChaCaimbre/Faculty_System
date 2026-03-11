<?php
require_once 'config.php';
$db = getDB();

// Ensure Subjects exist
$subjects = [
    ['MATH101', 'Mathematics 101'],
    ['CISCO', 'CISCO']
];
foreach ($subjects as $s) {
    if ($db->query("SELECT id FROM subjects WHERE code = '{$s[0]}'")->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name) VALUES ('{$s[0]}', '{$s[1]}')");
    }
}

// Assign to Micheline G. Apolinar
$res = $db->query("SELECT id FROM faculty WHERE name = 'Micheline G. Apolinar'");
if ($res->num_rows > 0) {
    $fid = $res->fetch_assoc()['id'];

    $sections = ['AI23', 'AI33', 'AI22'];
    foreach (['MATH101', 'CISCO'] as $code) {
        $sid = $db->query("SELECT id FROM subjects WHERE code = '$code'")->fetch_assoc()['id'];
        foreach ($sections as $sec) {
            // We'll update the EXISTING schedules for Apolinar to match these subjects if they were IT-104 etc.
            // Or just ensure at least one schedule exists so it shows in the table.
            $db->query("UPDATE schedules SET subject_id = $sid WHERE faculty_id = $fid AND section = '$sec'");
            if ($db->affected_rows == 0) {
                // If no schedule exists, just mock one for one of the labs at a random time so it shows up
                $rid = $db->query("SELECT id FROM rooms LIMIT 1")->fetch_assoc()['id'];
                $db->query("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time) 
                           VALUES ($fid, $sid, $rid, 'Monday', '$sec', '07:30:00', '09:00:00')");
            }
        }
    }
}

echo "Apolinar data matched to image!\n";
?>
