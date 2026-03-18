<?php
header("Content-Type: application/json");
require_once '../config.php';
$db = getDB();

// ─── Helper: get or create records ───────────────────────────────────────────

function getRoomId($db, $name) {
    $n = $db->real_escape_string($name);
    $r = $db->query("SELECT id FROM rooms WHERE name='$n'");
    if ($r->num_rows > 0) return $r->fetch_assoc()['id'];
    $db->query("INSERT INTO rooms (name, capacity, type) VALUES ('$n', 40, 'Laboratory')");
    return $db->insert_id;
}

function getFacultyId($db, $name) {
    $n = $db->real_escape_string(strtoupper(trim($name)));
    $r = $db->query("SELECT id FROM faculty WHERE UPPER(name)='$n'");
    if ($r->num_rows > 0) return $r->fetch_assoc()['id'];
    $db->query("INSERT INTO faculty (name, employment_status) VALUES ('$n', 'Full-time')");
    return $db->insert_id;
}

function getSubjectId($db, $code, $name = '') {
    $c = $db->real_escape_string(strtoupper(trim($code)));
    $r = $db->query("SELECT id FROM subjects WHERE code='$c'");
    if ($r->num_rows > 0) return $r->fetch_assoc()['id'];
    $nm = $db->real_escape_string($name ?: $code);
    $db->query("INSERT INTO subjects (code, name, units) VALUES ('$c', '$nm', 3)");
    return $db->insert_id;
}

function addSched($db, $room, $day, $start, $end, $subCode, $section, $faculty) {
    $room_id    = getRoomId($db, $room);
    $subject_id = getSubjectId($db, $subCode);
    $faculty_id = getFacultyId($db, $faculty);
    $day_esc    = $db->real_escape_string($day);
    $sec        = $db->real_escape_string($section);
    $s          = $db->real_escape_string($start);
    $e          = $db->real_escape_string($end);
    $db->query("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time)
                VALUES ($faculty_id, $subject_id, $room_id, '$day_esc', '$sec', '$s', '$e')");
}

// ─── Clear existing schedules ─────────────────────────────────────────────────
$db->query("DELETE FROM schedules");
$db->query("ALTER TABLE schedules AUTO_INCREMENT = 1");

// ─── COMPLAB1 ─────────────────────────────────────────────────────────────────
// MTH = Monday + Thursday | TFRI = Tuesday + Friday
foreach (['Monday','Thursday'] as $d) {
    addSched($db,$d=='Monday'?'COMPLAB1':'COMPLAB1',$d,'07:30','09:00','IT-128','AI32','AMAGO');
    addSched($db,'COMPLAB1',$d,'09:00','10:30','IT-128L','AI32','AMAGO');
    addSched($db,'COMPLAB1',$d,'10:30','12:00','IT-130L','AI11','DALAN');
    addSched($db,'COMPLAB1',$d,'13:00','14:30','IT-115L','AI25','DURANGO');
    addSched($db,'COMPLAB1',$d,'14:30','16:00','IT-115L','AI25','DURANGO');
    addSched($db,'COMPLAB1',$d,'16:00','17:30','IT-105L','AI34','SAMPAYAN');
    addSched($db,'COMPLAB1',$d,'17:30','19:00','IT-112L','AI24','COMBINIDO');
}
foreach (['Tuesday','Friday'] as $d) {
    addSched($db,'COMPLAB1',$d,'07:30','09:00','IT-128','AI31','FUNCION');
    addSched($db,'COMPLAB1',$d,'09:00','10:30','IT-128L','AI31','FUNCION');
    addSched($db,'COMPLAB1',$d,'10:30','12:00','IT-115L','AI21','FERNANDEZ');
    addSched($db,'COMPLAB1',$d,'13:00','14:30','IT-112L','AI21','AMAGO');
    addSched($db,'COMPLAB1',$d,'14:30','16:00','IT-112L','AI21','AMAGO');
    addSched($db,'COMPLAB1',$d,'16:00','17:30','IT-105L','AI35','CABANGON');
    addSched($db,'COMPLAB1',$d,'17:30','19:00','IT-127L','AI31','CELESTIAL');
}
// WED
addSched($db,'COMPLAB1','Wednesday','08:00','10:00','IT-115','AI25','DURANGO');
addSched($db,'COMPLAB1','Wednesday','10:00','12:00','IT-108','AI11','DALAN');
addSched($db,'COMPLAB1','Wednesday','13:00','15:00','IT-107','AI11','TIQUEN');
addSched($db,'COMPLAB1','Wednesday','15:00','17:00','IT-107','AI11','TIQUEN');
addSched($db,'COMPLAB1','Wednesday','17:00','19:00','IT-112','AI24','COMBINIDO');

// SAT
addSched($db,'COMPLAB1','Saturday','07:30','09:30','IT-127','AI35','MURILLO');

// ─── COMPLAB2 ─────────────────────────────────────────────────────────────────
foreach (['Monday','Thursday'] as $d) {
    addSched($db,'COMPLAB2',$d,'07:30','09:00','IT-107','AI13','GALBAN');
    addSched($db,'COMPLAB2',$d,'09:00','10:30','IT-107L','AI13','GALBAN');
    addSched($db,'COMPLAB2',$d,'10:30','12:00','IT-108L','AI13','CINCO');
    addSched($db,'COMPLAB2',$d,'13:00','14:30','IT-109L','AI31','CABANGON');
    addSched($db,'COMPLAB2',$d,'14:30','16:00','IT-107L','AI14','GALBAN');
    addSched($db,'COMPLAB2',$d,'16:00','17:30','IT-107','AI14','GALBAN');
    addSched($db,'COMPLAB2',$d,'17:30','19:00','TOUR-101L','MT13','MEMORACION');
}
foreach (['Tuesday','Friday'] as $d) {
    addSched($db,'COMPLAB2',$d,'07:30','09:00','IT-127L','AI04','MURILLO');
    addSched($db,'COMPLAB2',$d,'09:00','10:30','IT-107L','AI15','GALBAN');
    addSched($db,'COMPLAB2',$d,'10:30','12:00','IT-108L','AI14','DALAN');
    addSched($db,'COMPLAB2',$d,'13:00','14:30','GE-113','SM31','QUISUMBING');
    addSched($db,'COMPLAB2',$d,'14:30','16:00','IT-115L','AI24','FERNANDEZ');
    addSched($db,'COMPLAB2',$d,'16:00','17:30','IT-127L','AI32','LAGONOY');
    addSched($db,'COMPLAB2',$d,'17:30','19:00','TOUR-101L','MT12','ALMENARIO');
}
// WED
addSched($db,'COMPLAB2','Wednesday','08:00','10:00','IT-112','AI22','ORMENETA');
addSched($db,'COMPLAB2','Wednesday','10:00','12:00','IT-112','AI23','ORMENETA');
addSched($db,'COMPLAB2','Wednesday','13:00','15:00','IT-107','AI15','GALBAN');
addSched($db,'COMPLAB2','Wednesday','15:00','17:00','IT-127','AI03','LAGONOY');
addSched($db,'COMPLAB2','Wednesday','17:00','19:00','IT-127','AI31','CELESTIAL');
// SAT
addSched($db,'COMPLAB2','Saturday','09:00','11:00','IT-129','AI35','DIAZ');

// ─── COMPLAB3 ─────────────────────────────────────────────────────────────────
foreach (['Monday','Thursday'] as $d) {
    addSched($db,'COMPLAB3',$d,'07:30','09:00','IT-128','AI31','AMAGO');
    addSched($db,'COMPLAB3',$d,'09:00','10:30','IT-128L','AI34','FUNCION');
    addSched($db,'COMPLAB3',$d,'10:30','12:00','IT-109L','AI32','CABANGON');
    addSched($db,'COMPLAB3',$d,'13:00','14:30','IT-112L','AI25','AMAGO');
    addSched($db,'COMPLAB3',$d,'14:30','16:00','IT-105','AI32','CABANGON');
    addSched($db,'COMPLAB3',$d,'16:00','17:30','IT-127L','AI03','LAGONOY');
    addSched($db,'COMPLAB3',$d,'17:30','19:00','IT-127L','AI35','MURILLO');
}
foreach (['Tuesday','Friday'] as $d) {
    addSched($db,'COMPLAB3',$d,'07:30','09:00','IT-107L','AI11','TIQUEN');
    addSched($db,'COMPLAB3',$d,'09:00','10:30','IT-107L','AI12','TIQUEN');
    addSched($db,'COMPLAB3',$d,'10:30','12:00','IT-108L','AI33','FUNCION');
    addSched($db,'COMPLAB3',$d,'13:00','14:30','IT-108L','AI15','ORMENETA');
    addSched($db,'COMPLAB3',$d,'14:30','16:00','IT-109','AI15','ORMENETA');
    addSched($db,'COMPLAB3',$d,'16:00','17:30','IT-115L','AI23','FERNANDEZ');
    addSched($db,'COMPLAB3',$d,'17:30','19:00','IT-127L','AI35','MURILLO');
}
// WED
addSched($db,'COMPLAB3','Wednesday','08:00','10:00','IT-108','AI13','CINCO');
addSched($db,'COMPLAB3','Wednesday','10:00','12:00','IT-107','AI12','TIQUEN');
addSched($db,'COMPLAB3','Wednesday','13:00','15:00','IT-115','AI25','FERNANDEZ');
addSched($db,'COMPLAB3','Wednesday','15:00','17:00','TOUR-101','AI31','ALMENARIO');
addSched($db,'COMPLAB3','Wednesday','17:00','19:00','TOUR-101','AI35','MEMORACION');

// ─── COMPLAB4 ─────────────────────────────────────────────────────────────────
foreach (['Monday','Thursday'] as $d) {
    addSched($db,'COMPLAB4',$d,'07:30','09:00','IT-105L','AI33','CABANGON');
    addSched($db,'COMPLAB4',$d,'09:00','10:30','IT-112L','AI22','ORMENETA');
    addSched($db,'COMPLAB4',$d,'10:30','12:00','IT-113L','AI23','ORMENETA');
    addSched($db,'COMPLAB4',$d,'13:00','14:30','IT-108L','AI12','ORMENETA');
    addSched($db,'COMPLAB4',$d,'14:30','16:00','IT-120L','AI21','DALAN');
    addSched($db,'COMPLAB4',$d,'16:00','17:30','IT-120L','AI21','DALAN');
}
foreach (['Tuesday','Friday'] as $d) {
    addSched($db,'COMPLAB4',$d,'07:30','09:00','IT-120','AI24','TIBE');
    addSched($db,'COMPLAB4',$d,'09:00','10:30','IT-120L','AI24','TIBE');
    addSched($db,'COMPLAB4',$d,'10:30','12:00','IT-112L','AI22','GALBAN');
    addSched($db,'COMPLAB4',$d,'13:00','14:30','IT-120L','AI22','TIBE');
    addSched($db,'COMPLAB4',$d,'14:30','16:00','IT-120','AI22','TIBE');
    addSched($db,'COMPLAB4',$d,'16:00','17:30','IT-108','AI12','ORMENETA');
}
// WED
addSched($db,'COMPLAB4','Wednesday','08:00','10:00','IT-112','AI21','AMAGO');
addSched($db,'COMPLAB4','Wednesday','10:00','12:00','IT-112','AI25','AMAGO');
addSched($db,'COMPLAB4','Wednesday','13:00','15:00','IT-127','AI34','SAMPAYAN');
addSched($db,'COMPLAB4','Wednesday','15:00','17:00','IT-127','AI34','SAMPAYAN');

// ─── COMPLAB5 (CON 103) ───────────────────────────────────────────────────────
foreach (['Monday','Thursday'] as $d) {
    addSched($db,'COMPLAB5 (CON 103)',$d,'07:30','09:00','GE-113','AS21','VERECIO');
    addSched($db,'COMPLAB5 (CON 103)',$d,'09:00','10:30','GE-113','MH21','TIQUEN');
    addSched($db,'COMPLAB5 (CON 103)',$d,'10:30','12:00','GE-113','AI34','APOLINAR');
    addSched($db,'COMPLAB5 (CON 103)',$d,'13:00','14:30','SPT-104','AL21','NAVARRO');
    addSched($db,'COMPLAB5 (CON 103)',$d,'14:30','16:00','IT-129L','AI31','TIBE');
    addSched($db,'COMPLAB5 (CON 103)',$d,'16:00','17:30','IT-126','AI32','VERECIO');
    addSched($db,'COMPLAB5 (CON 103)',$d,'17:30','19:00','IT-129L','AI33','DABLEO');
}
foreach (['Tuesday','Friday'] as $d) {
    addSched($db,'COMPLAB5 (CON 103)',$d,'07:30','09:00','IT-128L','AI36','AMAGO');
    addSched($db,'COMPLAB5 (CON 103)',$d,'09:00','10:30','IT-128L','AI35','AMAGO');
    addSched($db,'COMPLAB5 (CON 103)',$d,'10:30','12:00','SPT-111','AI31','NAVARRO');
    addSched($db,'COMPLAB5 (CON 103)',$d,'13:00','14:30','LIS-106','AL21','NAVARRO');
    addSched($db,'COMPLAB5 (CON 103)',$d,'14:30','16:00','SPT-110','AL21','NAVARRO');
    addSched($db,'COMPLAB5 (CON 103)',$d,'17:30','19:00','IT-124','AI35','MEMORACION');
}
// WED
addSched($db,'COMPLAB5 (CON 103)','Wednesday','08:00','10:00','LIS-112','AL21','NAVARRO');
addSched($db,'COMPLAB5 (CON 103)','Wednesday','10:00','12:00','LIS-104','AL21','SASI');
addSched($db,'COMPLAB5 (CON 103)','Saturday','09:00','11:00','LIS-104','AL21','SASI');

echo json_encode(['success' => true, 'message' => 'Schedule seeded successfully for 2nd Semester SY 2025-2026.']);
?>
