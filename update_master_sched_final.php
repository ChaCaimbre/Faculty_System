<?php
require_once 'config.php';
$db = getDB();

// 1. Clear existing schedules and rooms
$db->query("SET FOREIGN_KEY_CHECKS = 0");
$db->query("TRUNCATE TABLE schedules");
$db->query("TRUNCATE TABLE rooms");
$db->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Cleared all existing schedules and rooms." . PHP_EOL;

// 2. Ensure Rooms exist
$rooms = [
    'COMLAB1',
    'COMLAB2',
    'COMLAB3',
    'COMLAB4',
    'COMLAB5 (CON 103)',
    'COMLAB6 (CON 104)',
    'COMLAB7 (CON 105)',
    'CHS (CON 101)',
    'CISCO (CON 102)',
    'OTHER ROOMS'
];

$roomMap = [];
foreach ($rooms as $roomName) {
    $res = $db->query("SELECT id FROM rooms WHERE name = '" . $db->real_escape_string($roomName) . "'");
    if ($res->num_rows == 0) {
        $db->query("INSERT INTO rooms (name) VALUES ('" . $db->real_escape_string($roomName) . "')");
        $roomMap[$roomName] = $db->insert_id;
    }
    else {
        $roomMap[$roomName] = $res->fetch_assoc()['id'];
    }
}

// 3. Helper for adding schedule
function addSched($db, $roomMap, $roomName, $dayArr, $start, $end, $subCode, $section, $facName)
{
    if (!isset($roomMap[$roomName]))
        return;
    $rid = $roomMap[$roomName];

    // Ensure Faculty
    $f_res = $db->query("SELECT id FROM faculty WHERE name = '" . $db->real_escape_string($facName) . "'");
    if ($f_res->num_rows == 0) {
        if ($facName == '') $facName = 'TBA';
        $db->query("INSERT INTO faculty (name) VALUES ('" . $db->real_escape_string($facName) . "')");
        $fid = $db->insert_id;
    }
    else {
        $fid = $f_res->fetch_assoc()['id'];
    }

    // Ensure Subject
    $s_res = $db->query("SELECT id FROM subjects WHERE code = '" . $db->real_escape_string($subCode) . "'");
    if ($s_res->num_rows == 0) {
        $db->query("INSERT INTO subjects (code, name) VALUES ('" . $db->real_escape_string($subCode) . "', '" . $db->real_escape_string($subCode) . "')");
        $sid = $db->insert_id;
    }
    else {
        $sid = $s_res->fetch_assoc()['id'];
    }

    $stmt = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($dayArr as $day) {
        $stmt->bind_param("iiissss", $fid, $sid, $rid, $day, $section, $start, $end);
        $stmt->execute();
    }
    
    // Format for display as requested
    $displayStart = date("g:i A", strtotime($start));
    $displayEnd = date("g:i A", strtotime($end));
    echo "Added: " . implode("/", $dayArr) . " $displayStart - $displayEnd | $subCode | $section | $facName | $roomName" . PHP_EOL;
}

// 4. Populate Data
$monThu = ['Monday', 'Thursday'];
$tueFri = ['Tuesday', 'Friday'];
$wedOnly = ['Wednesday'];
$satOnly = ['Saturday'];

// --- COMLAB1 ---
addSched($db, $roomMap, 'COMLAB1', $monThu, '07:30:00', '09:00:00', 'IT-128', 'AI32', 'AMAGO');
addSched($db, $roomMap, 'COMLAB1', $monThu, '09:00:00', '10:30:00', 'IT-128L', 'AI32', 'AMAGO');
addSched($db, $roomMap, 'COMLAB1', $monThu, '10:30:00', '12:00:00', 'IT-130L', 'AI11', 'DALAN');
addSched($db, $roomMap, 'COMLAB1', $monThu, '13:00:00', '14:30:00', 'IT-115', 'AI22', 'DURANGO');
addSched($db, $roomMap, 'COMLAB1', $monThu, '14:30:00', '16:00:00', 'IT-115L', 'AI25', 'DURANGO');
addSched($db, $roomMap, 'COMLAB1', $monThu, '16:00:00', '17:30:00', 'IT-105L', 'AI34', 'SAMPAYAN');
addSched($db, $roomMap, 'COMLAB1', $monThu, '17:30:00', '19:00:00', 'IT-112L', 'AI24', 'COMBINIDO');

addSched($db, $roomMap, 'COMLAB1', $tueFri, '07:30:00', '09:00:00', 'IT-128', 'AI31', 'FUNCION');
addSched($db, $roomMap, 'COMLAB1', $tueFri, '09:00:00', '10:30:00', 'IT-128L', 'AI31', 'FUNCION');
addSched($db, $roomMap, 'COMLAB1', $tueFri, '10:30:00', '12:00:00', 'IT-115L', 'AI21', 'FERNANDEZ');
addSched($db, $roomMap, 'COMLAB1', $tueFri, '14:30:00', '16:00:00', 'IT-112L', 'AI21', 'AMAGO');
addSched($db, $roomMap, 'COMLAB1', $tueFri, '16:00:00', '17:30:00', 'IT-105L', 'AI35', 'CABANGON');
addSched($db, $roomMap, 'COMLAB1', $tueFri, '17:30:00', '19:00:00', 'IT-127L', 'AI31', 'CELESTIAL');

addSched($db, $roomMap, 'COMLAB1', $wedOnly, '08:00:00', '10:00:00', 'IT-115', 'AI25', 'DURANGO');
addSched($db, $roomMap, 'COMLAB1', $wedOnly, '10:00:00', '12:00:00', 'IT-108', 'AI11', 'DALAN');
addSched($db, $roomMap, 'COMLAB1', $wedOnly, '15:00:00', '17:00:00', 'IT-107', 'AI11', 'TIQUEN');
addSched($db, $roomMap, 'COMLAB1', $wedOnly, '17:00:00', '19:00:00', 'IT-112', 'AI24', 'COMBINIDO');

addSched($db, $roomMap, 'COMLAB1', $satOnly, '08:00:00', '10:00:00', 'IT-127', 'AI35', 'MURILLO');

// --- COMLAB2 ---
addSched($db, $roomMap, 'COMLAB2', $monThu, '07:30:00', '09:00:00', 'IT-107', 'AI13', 'GALBAN');
addSched($db, $roomMap, 'COMLAB2', $monThu, '09:00:00', '10:30:00', 'IT-107L', 'AI13', 'GALBAN');
addSched($db, $roomMap, 'COMLAB2', $monThu, '10:30:00', '12:00:00', 'IT-108L', 'AI13', 'CINCO');
addSched($db, $roomMap, 'COMLAB2', $monThu, '13:00:00', '14:30:00', 'IT-105L', 'AI31', 'CABANGON');
addSched($db, $roomMap, 'COMLAB2', $monThu, '14:30:00', '16:00:00', 'IT-107L', 'AI14', 'GALBAN');
addSched($db, $roomMap, 'COMLAB2', $monThu, '16:00:00', '17:00:00', 'IT-107', 'AI14', 'GALBAN');
addSched($db, $roomMap, 'COMLAB2', $monThu, '17:30:00', '19:00:00', 'TOUR-101L', 'MT13', 'MEMORACION');

addSched($db, $roomMap, 'COMLAB2', $tueFri, '07:30:00', '09:00:00', 'IT-127L', 'AI34', 'MURILLO');
addSched($db, $roomMap, 'COMLAB2', $tueFri, '09:00:00', '10:30:00', 'IT-107L', 'AI15', 'GALBAN');
addSched($db, $roomMap, 'COMLAB2', $tueFri, '10:30:00', '12:00:00', 'IT-108L', 'AI14', 'DALAN');
addSched($db, $roomMap, 'COMLAB2', $tueFri, '13:00:00', '14:30:00', 'GE-113', 'SM31', 'QUISUMBING');
addSched($db, $roomMap, 'COMLAB2', $tueFri, '14:30:00', '16:00:00', 'IT-115L', 'AI24', 'FERNANDEZ');
addSched($db, $roomMap, 'COMLAB2', $tueFri, '16:00:00', '17:30:00', 'IT-127L', 'AI32', 'LAGONOY');
addSched($db, $roomMap, 'COMLAB2', $tueFri, '17:30:00', '19:00:00', 'TOUR-101L', 'MT12', 'ALMENARIO');

addSched($db, $roomMap, 'COMLAB2', $wedOnly, '08:00:00', '10:00:00', 'IT-112', 'AI22', 'ORMENETA');
addSched($db, $roomMap, 'COMLAB2', $wedOnly, '10:00:00', '12:00:00', 'IT-112', 'AI23', 'ORMENETA');
addSched($db, $roomMap, 'COMLAB2', $wedOnly, '13:00:00', '15:00:00', 'IT-107', 'AI15', 'GALBAN');
addSched($db, $roomMap, 'COMLAB2', $wedOnly, '15:00:00', '17:00:00', 'IT-127', 'AI33', 'DABLEO');
addSched($db, $roomMap, 'COMLAB2', $wedOnly, '17:00:00', '19:00:00', 'IT-127', 'AI31', 'CELESTIAL');

addSched($db, $roomMap, 'COMLAB2', $satOnly, '13:00:00', '16:00:00', 'IT-129', 'AI35', 'DIAZ');

// --- COMLAB3 ---
addSched($db, $roomMap, 'COMLAB3', $monThu, '08:00:00', '09:00:00', 'IT-128', 'AI31', 'FUNCION');
addSched($db, $roomMap, 'COMLAB3', $monThu, '09:00:00', '10:30:00', 'IT-128L', 'AI34', 'FUNCION');
addSched($db, $roomMap, 'COMLAB3', $monThu, '10:30:00', '12:00:00', 'IT-105L', 'AI32', 'CABANGON');
addSched($db, $roomMap, 'COMLAB3', $monThu, '13:00:00', '14:30:00', 'IT-112L', 'AI25', 'AMAGO');
addSched($db, $roomMap, 'COMLAB3', $monThu, '14:30:00', '15:30:00', 'IT-105', 'AI32', 'CABANGON');
addSched($db, $roomMap, 'COMLAB3', $monThu, '16:00:00', '17:30:00', 'IT-127L', 'AI33', 'LAGONOY');
addSched($db, $roomMap, 'COMLAB3', $monThu, '17:30:00', '19:00:00', 'IT-127L', 'AI35', 'MURILLO');

addSched($db, $roomMap, 'COMLAB3', $tueFri, '07:30:00', '09:00:00', 'IT-107L', 'AI11', 'TIQUEN');
addSched($db, $roomMap, 'COMLAB3', $tueFri, '09:00:00', '10:30:00', 'IT-107L', 'AI12', 'TIQUEN');
addSched($db, $roomMap, 'COMLAB3', $tueFri, '10:30:00', '12:00:00', 'IT-124', 'AI33', 'FUNCION');
addSched($db, $roomMap, 'COMLAB3', $tueFri, '13:00:00', '14:30:00', 'IT-108L', 'AI15', 'ORMENETA');
addSched($db, $roomMap, 'COMLAB3', $tueFri, '14:30:00', '15:30:00', 'IT-108', 'AI15', 'ORMENETA');
addSched($db, $roomMap, 'COMLAB3', $tueFri, '16:00:00', '17:30:00', 'IT-115L', 'AI23', 'FERNANDEZ');

addSched($db, $roomMap, 'COMLAB3', $wedOnly, '08:00:00', '10:00:00', 'IT-108', 'AI13', 'CINCO');
addSched($db, $roomMap, 'COMLAB3', $wedOnly, '10:00:00', '12:00:00', 'IT-107', 'AI12', 'TIQUEN');
addSched($db, $roomMap, 'COMLAB3', $wedOnly, '13:00:00', '15:00:00', 'IT-108', 'AI14', 'DALAN');
addSched($db, $roomMap, 'COMLAB3', $wedOnly, '15:00:00', '17:00:00', 'IT-129', 'AI31', 'DABLEO');

// --- COMLAB4 ---
addSched($db, $roomMap, 'COMLAB4', $monThu, '07:30:00', '09:00:00', 'IT-105L', 'AI33', 'CABANGON');
addSched($db, $roomMap, 'COMLAB4', $monThu, '09:00:00', '10:30:00', 'IT-112L', 'AI22', 'ORMENETA');
addSched($db, $roomMap, 'COMLAB4', $monThu, '10:30:00', '12:00:00', 'IT-112L', 'AI23', 'ORMENETA');
addSched($db, $roomMap, 'COMLAB4', $monThu, '13:00:00', '14:30:00', 'IT-108L', 'AI12', 'ORMENETA');
addSched($db, $roomMap, 'COMLAB4', $monThu, '14:30:00', '16:00:00', 'IT-120L', 'AI21', 'DALAN');
addSched($db, $roomMap, 'COMLAB4', $monThu, '16:00:00', '17:00:00', 'IT-120L', 'AI21', 'DALAN');

addSched($db, $roomMap, 'COMLAB4', $tueFri, '08:00:00', '09:00:00', 'IT-120', 'AI24', 'TIBE');
addSched($db, $roomMap, 'COMLAB4', $tueFri, '09:00:00', '10:30:00', 'IT-120L', 'AI24', 'TIBE');
addSched($db, $roomMap, 'COMLAB4', $tueFri, '10:30:00', '12:00:00', 'GE-113', 'AS22', 'GALBAN');
addSched($db, $roomMap, 'COMLAB4', $tueFri, '13:00:00', '14:30:00', 'IT-120L', 'AI22', 'TIBE');
addSched($db, $roomMap, 'COMLAB4', $tueFri, '14:30:00', '15:30:00', 'IT-120', 'AI22', 'TIBE');
addSched($db, $roomMap, 'COMLAB4', $tueFri, '16:00:00', '17:00:00', 'IT-108', 'AI12', 'ORMENETA');

addSched($db, $roomMap, 'COMLAB4', $wedOnly, '08:00:00', '10:00:00', 'IT-112', 'AI21', 'AMAGO');
addSched($db, $roomMap, 'COMLAB4', $wedOnly, '10:00:00', '12:00:00', 'IT-112', 'AI25', 'AMAGO');
addSched($db, $roomMap, 'COMLAB4', $wedOnly, '13:00:00', '15:00:00', 'IT-127', 'AI32', 'LAGONOY');
addSched($db, $roomMap, 'COMLAB4', $wedOnly, '15:00:00', '17:00:00', 'IT-105', 'AI34', 'SAMPAYAN');
addSched($db, $roomMap, 'COMLAB4', $wedOnly, '17:00:00', '19:00:00', 'IT-127', 'AI34', 'MURILLO');

// --- COMLAB5 (CON 103) ---
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '07:30:00', '09:00:00', 'GE-113', 'AS21', 'VERECIO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '09:00:00', '10:30:00', 'GE-113', 'MH21', 'TIQUEN');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '10:30:00', '12:00:00', 'IT-124', 'AI34', 'APOLINAR');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '13:00:00', '14:30:00', 'SPT-104', 'AL21', 'NAVARRO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '14:30:00', '16:00:00', 'IT-129L', 'AI31', 'TIBE');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '16:00:00', '17:30:00', 'IT-126', 'AI32', 'VERECIO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $monThu, '17:30:00', '19:00:00', 'IT-129L', 'AI33', 'DABLEO');

addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '07:30:00', '09:00:00', 'IT-128L', 'AI36', 'AMAGO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '09:00:00', '10:00:00', 'IT-128L', 'AI35', 'AMAGO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '10:30:00', '12:00:00', 'SPT-111', 'AL31', 'NAVARRO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '13:00:00', '14:30:00', 'LIS-106', 'AL21', 'NAVARRO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '14:30:00', '16:00:00', 'SPT-110', 'AL21', 'NAVARRO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $tueFri, '17:30:00', '19:00:00', 'IT-124', 'AI35', 'MEMORACION');

addSched($db, $roomMap, 'COMLAB5 (CON 103)', $wedOnly, '09:00:00', '12:00:00', 'LIS-112', 'AL31', 'NAVARRO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $wedOnly, '13:00:00', '16:00:00', 'GE-107', 'AL21', 'NAVARRO');
addSched($db, $roomMap, 'COMLAB5 (CON 103)', $wedOnly, '17:00:00', '19:00:00', 'TOUR-101', 'MT12', 'ALMENARIO');

addSched($db, $roomMap, 'COMLAB5 (CON 103)', $satOnly, '09:00:00', '12:00:00', 'LIS-104', 'AL21', 'SASI');

// --- COMLAB6 ---
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '07:30:00', '09:00:00', 'IT-104', 'AI11', 'APOLINAR');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '09:00:00', '10:30:00', 'IT-104', 'AI12', 'APOLINAR');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '10:30:00', '12:00:00', 'IT-104', 'AI14', 'LAURENTE');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '13:00:00', '14:30:00', 'IT-104', 'AI13', 'SAMPAYAN');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '14:30:00', '16:00:00', 'IT-104', 'AI15', 'SAMPAYAN');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $monThu, '17:30:00', '19:00:00', 'GE-113', 'MH22', 'NICOLAS');

addSched($db, $roomMap, 'COMLAB6 (CON 104)', $tueFri, '07:30:00', '10:30:00', 'IT-134', 'AI41', 'LAURENTE');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $tueFri, '13:00:00', '14:30:00', 'IT-120L', 'AI23', 'DALAN');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $tueFri, '14:30:00', '15:30:00', 'IT-120', 'AI23', 'DALAN');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $tueFri, '17:30:00', '19:00:00', 'GE-113', 'MH23', 'NICOLAS');

addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '08:00:00', '10:00:00', 'IT-115', 'AI24', 'FERNANDEZ');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '10:00:00', '12:00:00', 'IT-128', 'AI33', 'CALUZA');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '13:00:00', '15:00:00', 'IT-115', 'AI23', 'FERNANDEZ');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '15:00:00', '17:00:00', 'IT-115', 'AI21', 'FERNANDEZ');
addSched($db, $roomMap, 'COMLAB6 (CON 104)', $wedOnly, '17:00:00', '19:00:00', 'TOUR-101', 'MT13', 'MEMORACION');

// --- COMLAB7 ---
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '07:30:00', '09:00:00', 'IT-124', 'AI31', 'CALUZA');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '09:00:00', '10:30:00', 'IT-120L', 'AI25', 'TIBE');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '10:30:00', '11:30:00', 'IT-120', 'AI25', 'TIBE');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '13:00:00', '16:00:00', 'IT-134', 'AI42', 'CINCO');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '16:00:00', '17:30:00', 'TOUR-101L', 'MT11', 'SAMPAYAN');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $monThu, '17:30:00', '19:00:00', 'GE-113', 'SM32', 'MORETO');

addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '07:30:00', '09:00:00', 'IT-124', 'AI32', 'CALUZA');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '09:00:00', '10:30:00', 'IT-128L', 'AI33', 'CALUZA');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '10:30:00', '12:00:00', 'LIS ICT-104A/L', 'AL11', 'AMAGO');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '14:30:00', '16:00:00', 'TOUR-101L', 'MT11', 'SAMPAYAN');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $tueFri, '16:00:00', '17:00:00', 'TOUR-101', 'MT11', 'SAMPAYAN');

addSched($db, $roomMap, 'COMLAB7 (CON 105)', $wedOnly, '08:00:00', '10:00:00', 'IT-105', 'AI31', 'CABANGON');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $wedOnly, '10:00:00', '12:00:00', 'IT-129', 'AI31', 'TIBE');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $wedOnly, '13:00:00', '15:00:00', 'IT-105', 'AI33', 'CABANGON');
addSched($db, $roomMap, 'COMLAB7 (CON 105)', $wedOnly, '15:00:00', '17:00:00', 'IT-115', 'AI22', 'DURANGO');

// --- CHS ---
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '07:30:00', '09:00:00', 'GE-113', 'MH24', 'TIQUEN');
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '09:00:00', '10:30:00', 'IT-130L', 'AI11', 'TURCO');
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '10:30:00', '12:00:00', 'IT-130L', 'AI15', 'TURCO');
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '13:30:00', '14:30:00', 'IT-129', 'AI34', 'TURCO');
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '14:30:00', '16:00:00', 'IT-129L', 'AI34', 'TURCO');
addSched($db, $roomMap, 'CHS (CON 101)', $monThu, '17:30:00', '19:00:00', 'IT-130L', 'AI14', 'VILLAFUERTE');

addSched($db, $roomMap, 'CHS (CON 101)', $tueFri, '08:00:00', '09:00:00', 'IT-130', 'AI12', 'DURANGO');
addSched($db, $roomMap, 'CHS (CON 101)', $tueFri, '09:00:00', '10:30:00', 'IT-129L', 'AI32', 'TURCO');
addSched($db, $roomMap, 'CHS (CON 101)', $tueFri, '10:30:00', '12:00:00', 'IT-130L', 'AI12', 'DURANGO');
addSched($db, $roomMap, 'CHS (CON 101)', $tueFri, '14:30:00', '16:00:00', 'IT-130L', 'AI13', 'DURANGO');

addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '08:00:00', '10:00:00', 'IT-130', 'AI11', 'TURCO');
addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '10:00:00', '12:00:00', 'IT-129', 'AI32', 'TURCO');
addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '13:00:00', '15:00:00', 'IT-130', 'AI13', 'DURANGO');
addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '15:00:00', '17:00:00', 'IT-130', 'AI15', 'TURCO');
addSched($db, $roomMap, 'CHS (CON 101)', $wedOnly, '17:00:00', '19:00:00', 'IT-130', 'AI14', 'VILLAFUERTE');

// --- CISCO ---
addSched($db, $roomMap, 'CISCO (CON 102)', $monThu, '07:30:00', '09:00:00', 'IT-116L', 'AI21', 'QUISUMBING');
addSched($db, $roomMap, 'CISCO (CON 102)', $monThu, '09:00:00', '10:00:00', 'IT-116', 'AI21', 'QUISUMBING');
addSched($db, $roomMap, 'CISCO (CON 102)', $monThu, '10:30:00', '12:00:00', 'IT-126', 'AI35', 'TIQUEN');
addSched($db, $roomMap, 'CISCO (CON 102)', $monThu, '16:00:00', '17:30:00', 'IT-116', 'AI22', 'CINCO');

addSched($db, $roomMap, 'CISCO (CON 102)', $tueFri, '07:30:00', '09:00:00', 'IT-116L', 'AI23', 'QUISUMBING');
addSched($db, $roomMap, 'CISCO (CON 102)', $tueFri, '09:00:00', '10:00:00', 'IT-116', 'AI23', 'QUISUMBING');
addSched($db, $roomMap, 'CISCO (CON 102)', $tueFri, '10:30:00', '12:00:00', 'IT-116L', 'AI22', 'CINCO');
addSched($db, $roomMap, 'CISCO (CON 102)', $tueFri, '13:00:00', '14:30:00', 'IT-116L', 'AI24', 'CABANGON');
addSched($db, $roomMap, 'CISCO (CON 102)', $tueFri, '14:30:00', '16:00:00', 'IT-116L', 'AI25', 'CINCO');
addSched($db, $roomMap, 'CISCO (CON 102)', $tueFri, '16:00:00', '17:00:00', 'IT-116', 'AI22', 'CINCO');

addSched($db, $roomMap, 'CISCO (CON 102)', $wedOnly, '10:00:00', '12:00:00', 'IT-105', 'AI35', 'CABANGON');
addSched($db, $roomMap, 'CISCO (CON 102)', $wedOnly, '13:00:00', '15:00:00', 'IT-116', 'AI25', 'CINCO');
addSched($db, $roomMap, 'CISCO (CON 102)', $wedOnly, '15:00:00', '17:00:00', 'IT-116', 'AI24', 'CABANGON');

// --- OTHER ROOMS ---
addSched($db, $roomMap, 'OTHER ROOMS', $tueFri, '13:00:00', '14:30:00', 'IT-126', 'AI34', 'TIQUEN');
addSched($db, $roomMap, 'OTHER ROOMS', $tueFri, '14:30:00', '16:00:00', 'IT-126', 'AI33', 'TIQUEN');
addSched($db, $roomMap, 'OTHER ROOMS', $tueFri, '16:00:00', '17:30:00', 'IT-126', 'AI31', 'VERECIO');

echo "Final master schedule updated with data from images!" . PHP_EOL;
?>
