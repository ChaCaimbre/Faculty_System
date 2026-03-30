<?php
header("Content-Type: application/json");
session_start();
require_once '../config.php';
require_once 'auth_helper.php';

$db = getDB();
$uid = requireUserId();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $term_id = isset($_GET['term_id']) ? intval($_GET['term_id']) : 1;
        $query = "SELECT s.*, f.name as faculty_name, sub.code as subject_code, sub.name as subject_name, r.name as room_name 
                  FROM schedules s
                  JOIN faculty f ON s.faculty_id = f.id
                  JOIN subjects sub ON s.subject_id = sub.id
                  JOIN rooms r ON s.room_id = r.id
                  WHERE s.user_id = $uid AND s.term_id = $term_id
                  ORDER BY s.day, s.start_time";
        $result = $db->query($query);
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        $faculty_id = isset($data['faculty_id']) ? intval($data['faculty_id']) : 0;
        $subject_id = isset($data['subject_id']) ? intval($data['subject_id']) : 0;
        $room_id    = isset($data['room_id'])    ? intval($data['room_id'])    : 0;
        $day        = $db->real_escape_string($data['day']);
        $section    = $db->real_escape_string($data['section'] ?? '');
        $start_time = $db->real_escape_string($data['start_time']);
        $end_time   = $db->real_escape_string($data['end_time']);
        $term_id    = isset($data['term_id'])    ? intval($data['term_id'])    : 1;

        // Handle typed room (find or create scoped to this user)
        if ($room_id === 0 && !empty($data['room_name'])) {
            $r_name = $db->real_escape_string($data['room_name']);
            $check  = $db->query("SELECT id FROM rooms WHERE name = '$r_name' AND user_id = $uid");
            if ($check->num_rows > 0) {
                $room_id = $check->fetch_assoc()['id'];
            } else {
                $db->query("INSERT INTO rooms (name, capacity, type, user_id) VALUES ('$r_name', 40, 'Laboratory', $uid)");
                $room_id = $db->insert_id;
            }
        }

        // Handle typed faculty (find or create scoped to this user)
        if ($faculty_id === 0 && !empty($data['faculty_name'])) {
            $f_name = $db->real_escape_string($data['faculty_name']);
            $check  = $db->query("SELECT id FROM faculty WHERE name = '$f_name' AND user_id = $uid");
            if ($check->num_rows > 0) {
                $faculty_id = $check->fetch_assoc()['id'];
            } else {
                $db->query("INSERT INTO faculty (name, user_id) VALUES ('$f_name', $uid)");
                $faculty_id = $db->insert_id;
            }
        }

        // Handle typed subject (find or create scoped to this user)
        if ($subject_id === 0 && !empty($data['subject_code'])) {
            $s_code = $db->real_escape_string($data['subject_code']);
            $s_name = $db->real_escape_string($data['subject_name'] ?? $s_code);
            $check  = $db->query("SELECT id FROM subjects WHERE code = '$s_code' AND user_id = $uid AND term_id = $term_id");
            if ($check->num_rows > 0) {
                $subject_id = $check->fetch_assoc()['id'];
            } else {
                $db->query("INSERT INTO subjects (code, name, user_id, term_id) VALUES ('$s_code', '$s_name', $uid, $term_id)");
                $subject_id = $db->insert_id;
            }
        }

        if ($faculty_id === 0 || $subject_id === 0 || $room_id === 0) {
            echo json_encode(["success" => false, "error" => "Please select or type a valid Faculty, Subject and Room"]);
            break;
        }

        $query = "INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time, user_id, term_id) 
                  VALUES ($faculty_id, $subject_id, $room_id, '$day', '$section', '$start_time', '$end_time', $uid, $term_id)";

        if ($db->query($query)) {
            echo json_encode(["success" => true, "id" => $db->insert_id]);
        } else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id'] ?? 0);
        $term_id = isset($_GET['term_id']) ? intval($_GET['term_id']) : 1;
        $data = json_decode(file_get_contents("php://input"), true);

        if ($id <= 0) {
            echo json_encode(["success" => false, "error" => "Invalid schedule id"]);
            break;
        }

        // Ownership check
        $own = $db->query("SELECT id FROM schedules WHERE id = $id AND user_id = $uid");
        if ($own->num_rows === 0) {
            echo json_encode(["success" => false, "error" => "Not found or no permission"]);
            break;
        }

        $updates = [];

        if (isset($data['day']))        { $updates[] = "day = '"      . $db->real_escape_string($data['day'])        . "'"; }
        if (isset($data['section']))    { $updates[] = "section = '"  . $db->real_escape_string($data['section'])    . "'"; }
        if (isset($data['start_time'])) { $updates[] = "start_time = '". $db->real_escape_string($data['start_time'])."'"; }
        if (isset($data['end_time']))   { $updates[] = "end_time = '"  . $db->real_escape_string($data['end_time'])  . "'"; }

        // Handle subject change
        if (!empty($data['subject_code'])) {
            $s_code = $db->real_escape_string($data['subject_code']);
            $s_name = $db->real_escape_string($data['subject_name'] ?? $s_code);
            $check  = $db->query("SELECT id FROM subjects WHERE code = '$s_code' AND user_id = $uid AND term_id = $term_id");
            if ($check->num_rows > 0) {
                $subject_id = intval($check->fetch_assoc()['id']);
                if (!empty($data['subject_name'])) {
                    $db->query("UPDATE subjects SET name = '$s_name' WHERE id = $subject_id AND user_id = $uid");
                }
            } else {
                $db->query("INSERT INTO subjects (code, name, user_id, term_id) VALUES ('$s_code', '$s_name', $uid, $term_id)");
                $subject_id = $db->insert_id;
            }
            $updates[] = "subject_id = $subject_id";
        }

        // Handle room change
        if (!empty($data['room_name'])) {
            $r_name = $db->real_escape_string($data['room_name']);
            $rcheck = $db->query("SELECT id FROM rooms WHERE name = '$r_name' AND user_id = $uid");
            if ($rcheck->num_rows > 0) {
                $rid = intval($rcheck->fetch_assoc()['id']);
            } else {
                $db->query("INSERT INTO rooms (name, user_id) VALUES ('$r_name', $uid)");
                $rid = $db->insert_id;
            }
            $updates[] = "room_id = $rid";
        }

        if (!empty($data['faculty_id']) && intval($data['faculty_id']) > 0) {
            $updates[] = "faculty_id = " . intval($data['faculty_id']);
        } elseif (!empty($data['faculty_name'])) {
            $f_name = $db->real_escape_string($data['faculty_name']);
            $fcheck = $db->query("SELECT id FROM faculty WHERE name = '$f_name' AND user_id = $uid");
            if ($fcheck->num_rows > 0) {
                $fid = intval($fcheck->fetch_assoc()['id']);
            } else {
                $db->query("INSERT INTO faculty (name, employment_status, user_id) VALUES ('$f_name', 'Full-time', $uid)");
                $fid = $db->insert_id;
            }
            $updates[] = "faculty_id = $fid";
        }

        if (empty($updates)) {
            echo json_encode(["success" => true, "message" => "No changes made."]);
            break;
        }

        $updateSql = implode(', ', $updates);
        if ($db->query("UPDATE schedules SET $updateSql WHERE id = $id AND user_id = $uid")) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        if ($db->query("DELETE FROM schedules WHERE id = $id AND user_id = $uid")) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }
        break;

    default:
        echo json_encode(["error" => "Method not allowed"]);
}
?>
