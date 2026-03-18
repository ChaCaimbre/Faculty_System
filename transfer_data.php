<?php
require_once 'config.php';

function transferData($fromTermId, $toTermId) {
    $db = getDB();
    
    // 1. Copy Subjects
    $subjects = $db->query("SELECT * FROM subjects WHERE term_id = $fromTermId");
    $subjectMapping = []; // old_id => new_id
    
    while ($sub = $subjects->fetch_assoc()) {
        $code = $db->real_escape_string($sub['code']);
        $name = $db->real_escape_string($sub['name']);
        $units = intval($sub['units']);
        $userId = $sub['user_id'];
        $currId = $sub['curriculum_id'];
        
        // Check if subject already exists in target term (to avoid duplicates)
        $check = $db->query("SELECT id FROM subjects WHERE code = '$code' AND term_id = $toTermId AND user_id = $userId");
        if ($check->num_rows > 0) {
            $newSubId = $check->fetch_assoc()['id'];
        } else {
            $db->query("INSERT INTO subjects (code, name, units, user_id, term_id, curriculum_id) 
                        VALUES ('$code', '$name', $units, $userId, $toTermId, $currId)");
            $newSubId = $db->insert_id;
        }
        $subjectMapping[$sub['id']] = $newSubId;
    }
    
    // 2. Copy Schedules
    $schedules = $db->query("SELECT * FROM schedules WHERE term_id = $fromTermId");
    $count = 0;
    while ($sch = $schedules->fetch_assoc()) {
        $facultyId = $sch['faculty_id'] ?: 'NULL';
        $oldSubId = $sch['subject_id'];
        $newSubId = $subjectMapping[$oldSubId] ?? $oldSubId; // Fallback to old if not mapped (shouldn't happen)
        $roomId = $sch['room_id'] ?: 'NULL';
        $day = $db->real_escape_string($sch['day']);
        $section = $db->real_escape_string($sch['section']);
        $startTime = $db->real_escape_string($sch['start_time']);
        $endTime = $db->real_escape_string($sch['end_time']);
        $userId = $sch['user_id'];
        
        $db->query("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time, user_id, term_id) 
                    VALUES ($facultyId, $newSubId, $roomId, '$day', '$section', '$startTime', '$endTime', $userId, $toTermId)");
        $count++;
    }
    
    return $count;
}

$db = getDB();

// Ensure term 2 exists
$checkTerm = $db->query("SELECT id FROM academic_terms WHERE id = 2");
if ($checkTerm->num_rows === 0) {
    $db->query("INSERT INTO academic_terms (id, name, is_active, user_id) VALUES (2, '2nd Semester 2024-2025', 0, 1)");
}

$terms = $db->query("SELECT * FROM academic_terms ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
print_r($terms);

$source = 1;
$target = 2;

if ($source && $target) {
    // Clear target schedules first to avoid duplicate transfers if run twice
    $db->query("DELETE FROM schedules WHERE term_id = $target");
    
    $copied = transferData($source, $target);
    echo "\nSuccessfully transferred $copied schedule entries from Term $source to Term $target.\n";
}
