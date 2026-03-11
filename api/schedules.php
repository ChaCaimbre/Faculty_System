<?php
header("Content-Type: application/json");
require_once '../config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $query = "SELECT s.*, f.name as faculty_name, sub.name as subject_name, r.name as room_name 
                  FROM schedules s
                  JOIN faculty f ON s.faculty_id = f.id
                  JOIN subjects sub ON s.subject_id = sub.id
                  JOIN rooms r ON s.room_id = r.id
                  ORDER BY s.day, s.start_time";
        $result = $db->query($query);
        $schedules = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($schedules);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        $faculty_id = isset($data['faculty_id']) ? intval($data['faculty_id']) : 0;
        $subject_id = isset($data['subject_id']) ? intval($data['subject_id']) : 0;
        $room_id = isset($data['room_id']) ? intval($data['room_id']) : 0;
        $day = $db->real_escape_string($data['day']);
        $section = $db->real_escape_string($data['section'] ?? '');
        $start_time = $db->real_escape_string($data['start_time']);
        $end_time = $db->real_escape_string($data['end_time']);

        // Handle Typed Room
        if ($room_id === 0 && !empty($data['room_name'])) {
            $r_name = $db->real_escape_string($data['room_name']);
            // Find or Create
            $check = $db->query("SELECT id FROM rooms WHERE name = '$r_name'");
            if ($check->num_rows > 0) {
                $room_id = $check->fetch_assoc()['id'];
            }
            else {
                $db->query("INSERT INTO rooms (name, capacity, type) VALUES ('$r_name', 40, 'Laboratory')");
                $room_id = $db->insert_id;
            }
        }

        // Handle Typed Faculty
        if ($faculty_id === 0 && !empty($data['faculty_name'])) {
            $f_name = $db->real_escape_string($data['faculty_name']);
            // Find or Create
            $check = $db->query("SELECT id FROM faculty WHERE name = '$f_name'");
            if ($check->num_rows > 0) {
                $faculty_id = $check->fetch_assoc()['id'];
            }
            else {
                $db->query("INSERT INTO faculty (name) VALUES ('$f_name')");
                $faculty_id = $db->insert_id;
            }
        }

        // Handle Typed Subject
        if ($subject_id === 0 && !empty($data['subject_code'])) {
            $s_code = $db->real_escape_string($data['subject_code']);
            $s_name = $db->real_escape_string($data['subject_name'] ?? $s_code);
            // Find or Create
            $check = $db->query("SELECT id FROM subjects WHERE code = '$s_code'");
            if ($check->num_rows > 0) {
                $subject_id = $check->fetch_assoc()['id'];
            }
            else {
                $db->query("INSERT INTO subjects (code, name) VALUES ('$s_code', '$s_name')");
                $subject_id = $db->insert_id;
            }
        }

        if ($faculty_id === 0 || $subject_id === 0 || $room_id === 0) {
            echo json_encode(["success" => false, "error" => "Please select or type a valid Faculty, Subject and Room"]);
            break;
        }

        $query = "INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time) 
                  VALUES ($faculty_id, $subject_id, $room_id, '$day', '$section', '$start_time', '$end_time')";

        if ($db->query($query)) {
            echo json_encode(["success" => true, "id" => $db->insert_id]);
        }
        else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents("php://input"), true);

        if ($id <= 0) {
            echo json_encode(["success" => false, "error" => "Invalid schedule id"]);
            break;
        }

        // Find the teacher + original subject for this schedule
        $baseRes = $db->query("SELECT faculty_id, subject_id FROM schedules WHERE id = $id");
        if (!$baseRes || $baseRes->num_rows === 0) {
            echo json_encode(["success" => false, "error" => "Schedule not found"]);
            break;
        }
        $baseRow = $baseRes->fetch_assoc();
        $faculty_id = intval($baseRow['faculty_id']);
        $original_subject_id = intval($baseRow['subject_id']);

        $updates = [];
        if (isset($data['section'])) {
            $section = $db->real_escape_string($data['section']);
            $updates[] = "section = '$section'";
        }

        // Optional subject change (applied to all schedules for this teacher+subject)
        if (!empty($data['subject_code'])) {
            $s_code = $db->real_escape_string($data['subject_code']);
            $s_name = $db->real_escape_string($data['subject_name'] ?? $s_code);

            // Find or create subject
            $check = $db->query("SELECT id FROM subjects WHERE code = '$s_code'");
            if ($check->num_rows > 0) {
                $subject_id = intval($check->fetch_assoc()['id']);
                // Update subject name if it's different and provided
                if (!empty($data['subject_name'])) {
                    $db->query("UPDATE subjects SET name = '$s_name' WHERE id = $subject_id");
                }
            }
            else {
                $db->query("INSERT INTO subjects (code, name) VALUES ('$s_code', '$s_name')");
                $subject_id = $db->insert_id;
            }
            $updates[] = "subject_id = $subject_id";
        }
        else {
            // Keep the same subject_id when only editing sections
            $subject_id = $original_subject_id;
            // Still update subject name if provided for original subject
            if (!empty($data['subject_name'])) {
                $s_name = $db->real_escape_string($data['subject_name']);
                $db->query("UPDATE subjects SET name = '$s_name' WHERE id = $subject_id");
            }
        }

        if (empty($updates)) {
            echo json_encode(["success" => true, "message" => "No specific schedule fields changed, but subject name or teacher info might have been updated."]);
            break;
        }

        $updateSql = implode(', ', $updates);

        // Apply changes to all rows belonging to this teacher + original subject
        $query = "UPDATE schedules SET $updateSql WHERE faculty_id = $faculty_id AND subject_id = $original_subject_id";

        if ($db->query($query)) {
            echo json_encode(["success" => true]);
        }
        else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        if ($db->query("DELETE FROM schedules WHERE id = $id")) {
            echo json_encode(["success" => true]);
        }
        else {
            echo json_encode(["success" => false]);
        }
        break;

    default:
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>
