<?php
require_once 'config.php';
$db = getDB();

// 1. Get all subject IDs
$subjects_res = $db->query("SELECT id, code FROM subjects");
$subjects = $subjects_res->fetch_all(MYSQLI_ASSOC);

// 2. Get all room IDs
$rooms_res = $db->query("SELECT id, name FROM rooms");
$rooms = $rooms_res->fetch_all(MYSQLI_ASSOC);

// 3. Get all faculty IDs
$faculty_res = $db->query("SELECT id, name FROM faculty");
$faculty = $faculty_res->fetch_all(MYSQLI_ASSOC);

if (empty($subjects) || empty($rooms) || empty($faculty)) {
    die("Error: Subjects, Rooms, or Faculty table is empty. Please seed them first.");
}

$db->query("TRUNCATE TABLE schedules");

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$times = [
    ['07:30:00', '09:00:00'],
    ['09:00:00', '10:30:00'],
    ['10:30:00', '12:00:00'],
    ['13:00:00', '14:30:00'],
    ['14:30:00', '16:00:00'],
    ['16:00:00', '17:30:00']
];

$stmt = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");

$count = 0;
foreach ($rooms as $room) {
    foreach ($days as $day) {
        // Assign 3 random subjects to each room per day
        $daily_slots = array_rand($times, 3);
        foreach ($daily_slots as $slot_idx) {
            $slot = $times[$slot_idx];
            $subj = $subjects[array_rand($subjects)];
            $fac = $faculty[array_rand($faculty)];

            $stmt->bind_param("iiisss", $fac['id'], $subj['id'], $room['id'], $day, $slot[0], $slot[1]);
            $stmt->execute();
            $count++;
        }
    }
}

echo "Schedules re-seeded successfully: $count entries added using the new IT curriculum.";
?>
