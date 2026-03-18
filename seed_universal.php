<?php
require_once 'config.php';
$db = getDB();

// 1. Get all users and terms
$users = [];
$res = $db->query("SELECT id FROM users");
while($row = $res->fetch_assoc()) $users[] = $row['id'];

$terms = [];
$res = $db->query("SELECT id FROM academic_terms");
while($row = $res->fetch_assoc()) $terms[] = $row['id'];

if (empty($terms)) $terms = [1];

// 2. Clean EVERYTHING
$db->query("SET FOREIGN_KEY_CHECKS = 0");
$db->query("TRUNCATE TABLE schedules");
$db->query("TRUNCATE TABLE faculty");
$db->query("TRUNCATE TABLE subjects");
$db->query("TRUNCATE TABLE rooms");
$db->query("SET FOREIGN_KEY_CHECKS = 1");

function addSched($db, $roomName, $day, $start, $end, $subCode, $section, $facName, $uid, $tid) {
    // Room (Global)
    $res = $db->query("SELECT id FROM rooms WHERE name = '" . $db->real_escape_string($roomName) . "'");
    if ($res->num_rows > 0) {
        $rid = $res->fetch_assoc()['id'];
    } else {
        $db->query("INSERT INTO rooms (name) VALUES ('" . $db->real_escape_string($roomName) . "')");
        $rid = $db->insert_id;
    }

    // Subject (Global-ish)
    $res = $db->query("SELECT id FROM subjects WHERE code = '" . $db->real_escape_string($subCode) . "'");
    if ($res->num_rows > 0) {
        $sid = $res->fetch_assoc()['id'];
    } else {
        $db->query("INSERT INTO subjects (code, name) VALUES ('" . $db->real_escape_string($subCode) . "', '" . $db->real_escape_string($subCode) . "')");
        $sid = $db->insert_id;
    }

    // Faculty (Per user)
    $res = $db->query("SELECT id FROM faculty WHERE name = '" . $db->real_escape_string($facName) . "' AND user_id = $uid");
    if ($res->num_rows > 0) {
        $fid = $res->fetch_assoc()['id'];
    } else {
        $db->query("INSERT INTO faculty (name, user_id) VALUES ('" . $db->real_escape_string($facName) . "', $uid)");
        $fid = $db->insert_id;
    }

    $st = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time, user_id, term_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $st->bind_param("iiissssii", $fid, $sid, $rid, $day, $section, $start, $end, $uid, $tid);
    $st->execute();
}

$times = ['08:00:00','10:00:00','12:00:00','13:00:00','15:00:00','17:00:00','19:00:00','21:00:00'];

$masterData = [
    "COMPLAB1" => [
        ['Monday/Thursday', 'IT-128/AI32/AMAGO', 'IT-128L/AI32/AMAGO', 'IT 130L/AI11/DALAN', 'IT-115/AI22/DURANGO', 'IT-115L/AI25/DURANGO', 'IT-105L/AI34/SAMPAYAN', 'IT-112L/AI24/COMBINIDO'],
        ['Tuesday/Friday', 'IT-128/AI31/FUNCION', 'IT-128L/AI31/FUNCION', 'IT-115L/AI21/FERNANDEZ', 'vacant/vacant/vacant', 'IT-112L/AI21/AMAGO', 'IT-105L/AI35/CABANGON', 'IT-127L/AI31/CELESTIAL'],
        ['Wednesday', 'IT-115/AI25/DURANGO', 'IT-108/AI11/DALAN', 'vacant/vacant/vacant', 'IT-107/AI11/TIQUEN', 'IT-112/AI24/COMBINIDO'],
        ['Saturday', 'IT-127/AI35/MURILLO']
    ],
    "COMPLAB2" => [
        ['Monday/Thursday', 'IT-107/AI31/GALBAN', 'IT-107L/AI13/GALBAN', 'IT-108L/AI13/CINCO', 'IT-105L/AI31/CABANGON', 'IT-107L/AI14/GALBAN', 'IT-107/AI14/GALBAN', 'TOUR-101L/MT13/MEMORACION'],
        ['Tuesday/Friday', 'IT-127L/AI34/MURILLO', 'IT-107L/AI15/GALBAN', 'IT-108L/AI14/DALAN', 'GE-113/SM31/QUISUMBING', 'IT-115L/AI24/FERNANDEZ', 'IT-127L/AI32/LAGONOY', 'TOUR-101L/MT12/ALMENARIO'],
        ['Wednesday', 'IT-112/AI22/ORMENETA', 'IT-112/AI23/ORMENETA', 'IT-107/AI15/GALBAN', 'IT-127/AI33/LAGONOY', 'IT-127/AI31/CELESTIAL'],
        ['Saturday', 'vacant/vacant/vacant', 'IT-129/AI35/DIAZ']
    ],
    "COMPLAB3" => [
        ['Monday/Thursday', 'IT-128/AI31/FUNCION', 'IT-128L/AI34/FUNCION', 'IT-105L/AI32/CABANGON', 'IT-112L/AI25/AMAGO', 'IT-105/AI32/CABANGON', 'IT-127L/AI33/LAGONOY', 'IT-127L/AI35/MURILLO'],
        ['Tuesday/Friday', 'IT-107L/AI11/TIQUEN', 'IT-107L/AI12/TIQUEN', 'IT-124/AI33/FUNCION', 'IT-108/AI15/ORMENETA', 'IT-108/AI15/ORMENETA', 'IT-115L/AI23/FERNANDEZ'],
        ['Wednesday', 'IT-108/AI31/CINCO', 'IT-107/AI12/TIQUEN', 'IT-108/AI14/DALAN', 'IT-129/AI33/DABLEO']
    ],
    "COMPLAB4" => [
        ['Monday/Thursday', 'IT-105L/AI33/CABANGON', 'IT-112L/AI22/ORMENETA', 'IT-112L/AI23/ORMENETA', 'IT-108L/AI12/ORMENETA', 'IT-120L/AI21/DALAN', 'IT-120L/AI21/DALAN'],
        ['Tuesday/Friday', 'IT-120/AI24/TIBE', 'IT-120L/AI24/TIBE', 'GE-113/AS22/GALBAN', 'IT-120L/AI22/TIBE', 'IT-120/AI22/TIBE', 'IT-108/AI12/ORMENETA'],
        ['Wednesday', 'IT-112/AI21/AMAGO', 'IT-112/AI25/AMAGO', 'IT-127/AI32/LAGONOY', 'IT-105/AI34/SAMPAYAN', 'IT-127/AI34/MURILLO']
    ],
    "COMPLAB5 (CON 103)" => [
        ['Monday/Thursday', 'GE-113/AS21/VERECIO', 'GE-113/MH21/TIQUEN', 'IT-124/AI34/APOLINAR', 'SPT-104/AL21/NAVARRO', 'IT-129L/AI31/TIBE', 'IT-126/AI32/TIBE', 'IT-129L/AI33/DABLEO'],
        ['Tuesday/Friday', 'IT-128L/AI36/AMAGO', 'IT-128L/AI35/AMAGO', 'SPT-111/AL31/NAVARRO', 'LIS-106/AL21/NAVARRO', 'SPT-110/AL21/NAVARRO', 'vacant/vacant/vacant', 'IT-124/AI35/MEMORACION'],
        ['Wednesday', 'LIS-112/AL31/NAVARRO', 'vacant/vacant/vacant', 'vacant/vacant/vacant', 'GE-107/AL21/NAVARRO', 'TOUR-101/MT12/ALMENARIO'],
        ['Saturday', 'LIS-104/AL21/SASI']
    ],
    "COMPLAB6 (CON 104)" => [
        ['Monday/Thursday', 'IT-104/AI11/APOLINAR', 'IT-104/AI12/APOLINAR', 'IT-104/AI14/LAURENTE', 'IT-104/AI13/SAMPAYAN', 'IT-104/AI15/SAMPAYAN', 'vacant/vacant/vacant', 'GE-113/MH22/NICOLAS'],
        ['Tuesday/Friday', 'IT-134/AI41/LAURENTE', 'vacant/vacant/vacant', 'vacant/vacant/vacant', 'IT-120L/AI23/DALAN', 'IT-120/AI23/DALAN', 'vacant/vacant/vacant', 'GE-113/MH23/NICOLAS'],
        ['Wednesday', 'IT-115/AI24/FERNANDEZ', 'IT-128/AI33/CALUZA', 'IT-115/AI23/FERNANDEZ', 'IT-115/AI21/FERNANDEZ', 'TOUR-101/MT13/MEMORACION']
    ],
    "COMPLAB7 (CON 105)" => [
        ['Monday/Thursday', 'IT-124/AI31/CALUZA', 'IT-120L/AI25/TIBE', 'IT-120/AI25/TIBE', 'IT-134/AI42/CINCO'],
        ['Tuesday/Friday', 'IT-124/AI32/CALUZA', 'IT-128L/AI33/CALUZA', 'LIS ICT-104A/L/AI/TIBE', 'vacant/vacant/vacant', 'TOUR-101L/MT11/SAMPAYAN', 'TOUR-101/MT11/SAMPAYAN', 'GE-113/SM32/MORETO'],
        ['Wednesday', 'IT-105/AI31/CABANGON', 'IT-129/AI31/TIBE', 'IT-105/AI33/CABANGON', 'IT-115/AI22/DURANGO']
    ],
    "CHS (CON 101)" => [
        ['Monday/Thursday', 'GE-113/MH24/TIQUEN', 'IT-130L/AI11/TURCO', 'IT-130L/AI15/TURCO', 'IT-129/AI34/TURCO', 'IT-129L/AI34/TURCO', 'vacant/vacant/vacant', 'IT-130L/AI14/VILLAFUERTE'],
        ['Tuesday/Friday', 'IT-130/AI12/DURANGO', 'IT-129L/AI32/TURCO', 'IT-130L/AI12/DURANGO', 'vacant/vacant/vacant', 'IT-130L/AI13/DURANGO'],
        ['Wednesday', 'IT-130/AI11/TURCO', 'IT-129/AI32/TURCO', 'IT-130/AI13/DURANGO', 'IT-130/AI15/TURCO', 'IT-130/AI14/VILLAFUERTE']
    ],
    "CISCO (CON 102)" => [
        ['Monday/Thursday', 'IT-116L/AI21/QUISUMBING', 'IT-116/AI21/QUISUMBING', 'IT-126/AI35/TIQUEN'],
        ['Tuesday/Friday', 'IT-116L/AI23/QUISUMBING', 'IT-116/AI23/QUISUMBING', 'IT-116L/AI22/CINCO', 'IT-116L/AI24/CABANGON', 'IT-116L/AI25/CINCO', 'IT-116/AI22/CINCO'],
        ['Wednesday', 'vacant/vacant/vacant', 'IT-105/AI35/CABANGON', 'IT-116/AI25/CINCO', 'IT-116/AI24/CABANGON']
    ]
];

foreach ($users as $uid) {
    foreach ($terms as $tid) {
        foreach ($masterData as $roomName => $roomSched) {
            foreach ($roomSched as $dayGroup) {
                $days = explode('/', $dayGroup[0]);
                for ($i = 1; $i < count($dayGroup); $i++) {
                    $parts = explode('/', $dayGroup[$i]);
                    if (count($parts) < 3) continue;
                    foreach ($days as $day) {
                        $start = $times[$i-1];
                        $end = $times[$i];
                        if ($i >= 3) {
                            $start = $times[$i];
                            $end = isset($times[$i+1]) ? $times[$i+1] : date('H:i:s', strtotime($start . ' + 2 hours'));
                        }
                        addSched($db, $roomName, trim($day), $start, $end, $parts[0], $parts[1], $parts[2], $uid, $tid);
                    }
                }
            }
        }
        // Extra ones
        addSched($db, "CISCO (CON 102)", "Tuesday", "17:00:00", "19:00:00", "IT-126", "AI34", "TIQUEN", $uid, $tid);
        addSched($db, "CISCO (CON 102)", "Friday", "17:00:00", "19:00:00", "IT-126", "AI34", "TIQUEN", $uid, $tid);
        addSched($db, "CISCO (CON 102)", "Tuesday", "19:00:00", "21:00:00", "IT-126", "AI33", "TIQUEN", $uid, $tid);
        addSched($db, "CISCO (CON 102)", "Friday", "19:00:00", "21:00:00", "IT-126", "AI33", "TIQUEN", $uid, $tid);
    }
}

echo "SUCCESS: Seeding completed for all USERS and all TERMS!";
?>
