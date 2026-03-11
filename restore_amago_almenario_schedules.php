<?php
/**
 * Restore schedule entries for Amago and Almenario (from course/room table).
 * Run after restore_deleted_teachers.php.
 * Browser: http://localhost/Faculty_System/restore_amago_almenario_schedules.php
 */
require_once 'config.php';

$db = getDB();

// Teacher => [ subject_code => [ room_names ] ] (from your table)
$assignments = [
    'Amago' => [
        'IT-128'   => ['AI32', 'AI36', 'AI35'],
        'IT-128L'  => ['AI32', 'AI36', 'AI35'],
        'IT-112'   => ['AI21', 'AI25'],
        'IT-112L'  => ['AI21', 'AI25'],
    ],
    'Almenario' => [
        'TOUR-101'  => ['MT12'],
        'TOUR-101L' => ['MT12'],
    ],
];

// Default slot (you can edit times in the app later)
$defaultDay = 'Monday';
$slotIndex = 0;
$slotTimes = [
    '08:00:00' => '09:00:00',
    '09:00:00' => '10:00:00',
    '10:00:00' => '11:00:00',
    '11:00:00' => '12:00:00',
    '13:00:00' => '14:00:00',
    '14:00:00' => '15:00:00',
    '15:00:00' => '16:00:00',
    '16:00:00' => '17:00:00',
];
$slotKeys = array_keys($slotTimes);

function getOrCreateFacultyId($db, $name) {
    $safe = $db->real_escape_string($name);
    $r = $db->query("SELECT id FROM faculty WHERE name = '$safe'");
    if ($r && $r->num_rows > 0) {
        return (int) $r->fetch_assoc()['id'];
    }
    $db->query("INSERT INTO faculty (name) VALUES ('$safe')");
    return $db->insert_id ? (int) $db->insert_id : null;
}

function getOrCreateSubjectId($db, $code) {
    $safe = $db->real_escape_string($code);
    $r = $db->query("SELECT id FROM subjects WHERE code = '$safe'");
    if ($r && $r->num_rows > 0) {
        return (int) $r->fetch_assoc()['id'];
    }
    $db->query("INSERT INTO subjects (code, name) VALUES ('$safe', '$safe')");
    return $db->insert_id ? (int) $db->insert_id : null;
}

function getOrCreateRoomId($db, $name) {
    $safe = $db->real_escape_string($name);
    $r = $db->query("SELECT id FROM rooms WHERE name = '$safe'");
    if ($r && $r->num_rows > 0) {
        return (int) $r->fetch_assoc()['id'];
    }
    $db->query("INSERT INTO rooms (name, capacity, type) VALUES ('$safe', 40, 'Laboratory')");
    return $db->insert_id ? (int) $db->insert_id : null;
}

function scheduleExists($db, $facultyId, $subjectId, $roomId) {
    $r = $db->query("SELECT id FROM schedules WHERE faculty_id = $facultyId AND subject_id = $subjectId AND room_id = $roomId");
    return $r && $r->num_rows > 0;
}

$created = [];
$skipped = [];
$errors = [];
$slotIndex = 0;

foreach ($assignments as $teacherName => $subjects) {
    $facultyId = getOrCreateFacultyId($db, $teacherName);
    if (!$facultyId) {
        $errors[] = "Could not find or create faculty: $teacherName";
        continue;
    }

    foreach ($subjects as $subjectCode => $rooms) {
        $subjectId = getOrCreateSubjectId($db, $subjectCode);
        if (!$subjectId) {
            $errors[] = "Could not find or create subject: $subjectCode";
            continue;
        }

        foreach ($rooms as $roomName) {
            $roomId = getOrCreateRoomId($db, $roomName);
            if (!$roomId) {
                $errors[] = "Could not find or create room: $roomName";
                continue;
            }

            $idx = $slotIndex % count($slotKeys);
            $startTime = $slotKeys[$idx];
            $endTime = $slotTimes[$startTime];
            $slotIndex++;

            if (scheduleExists($db, $facultyId, $subjectId, $roomId)) {
                $skipped[] = "$teacherName / $subjectCode / $roomName (already exists)";
                continue;
            }

            $day = $db->real_escape_string($defaultDay);
            $start = $db->real_escape_string($startTime);
            $end = $db->real_escape_string($endTime);
            $section = $db->real_escape_string('');
            $q = "INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time) 
                  VALUES ($facultyId, $subjectId, $roomId, '$day', '$section', '$start', '$end')";
            if ($db->query($q)) {
                $created[] = "$teacherName | $subjectCode | $roomName (id: " . $db->insert_id . ")";
            } else {
                $errors[] = "$teacherName | $subjectCode | $roomName: " . $db->error;
            }
        }
    }
}

if (php_sapi_name() === 'cli') {
    echo "Created: " . count($created) . "\n";
    foreach ($created as $c) echo "  - $c\n";
    if (!empty($skipped)) {
        echo "Skipped: " . count($skipped) . "\n";
        foreach ($skipped as $s) echo "  - $s\n";
    }
    if (!empty($errors)) {
        echo "Errors:\n";
        foreach ($errors as $e) echo "  - $e\n";
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'created' => $created,
        'skipped' => $skipped,
        'errors'  => $errors,
    ], JSON_PRETTY_PRINT);
}
