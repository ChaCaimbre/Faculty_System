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

function addSched($db, $roomName, $day, $start, $end, $subCode, $section, $facName, $uid, $tid, $titles) {
    // 1. Ensure Room (Per user as per api/rooms.php)
    $res = $db->query("SELECT id FROM rooms WHERE name = '" . $db->real_escape_string($roomName) . "' AND user_id = $uid");
    if ($res->num_rows > 0) {
        $rid = $res->fetch_assoc()['id'];
    } else {
        $db->query("INSERT INTO rooms (name, user_id) VALUES ('" . $db->real_escape_string($roomName) . "', $uid)");
        $rid = $db->insert_id;
    }

    // 2. Ensure Subject (Per user and term as per api/subjects.php)
    $res = $db->query("SELECT id FROM subjects WHERE code = '" . $db->real_escape_string($subCode) . "' AND user_id = $uid AND term_id = $tid");
    if ($res->num_rows > 0) {
        $sid = $res->fetch_assoc()['id'];
    } else {
        $subName = isset($titles[$subCode]) ? $titles[$subCode] : $subCode;
        $db->query("INSERT INTO subjects (code, name, user_id, term_id, curriculum_id) VALUES ('" . $db->real_escape_string($subCode) . "', '" . $db->real_escape_string($subName) . "', $uid, $tid, 1)");
        $sid = $db->insert_id;
    }

    // 3. Ensure Faculty (Per user)
    $res = $db->query("SELECT id FROM faculty WHERE name = '" . $db->real_escape_string($facName) . "' AND user_id = $uid");
    if ($res->num_rows > 0) {
        $fid = $res->fetch_assoc()['id'];
    } else {
        $db->query("INSERT INTO faculty (name, user_id) VALUES ('" . $db->real_escape_string($facName) . "', $uid)");
        $fid = $db->insert_id;
    }

    // 4. Insert Schedule
    $st = $db->prepare("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time, user_id, term_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $st->bind_param("iiissssii", $fid, $sid, $rid, $day, $section, $start, $end, $uid, $tid);
    $st->execute();
}

$titles = [
    'IT-101' => 'Information Technology Fundamentals with Software Application',
    'IT-101L' => 'Information Technology Fundamentals with Software Application (Laboratory)',
    'IT-102' => 'Accounting Principle',
    'IT-103' => 'Computer Programming I - Java',
    'IT-103L' => 'Computer Programming I - Java (Laboratory)',
    'IT-104' => 'Discrete Mathematics',
    'IT-105' => 'Mobile Development',
    'IT-105L' => 'Mobile Development (Laboratory)',
    'IT-106' => 'Introduction to Computing',
    'IT-107' => 'Multimedia System',
    'IT-107L' => 'Multimedia System (Laboratory)',
    'IT-108' => 'Programming II - Python',
    'IT-108L' => 'Programming II - Python (Laboratory)',
    'IT-109' => 'IT Elective I - Platform Technologies',
    'IT-109L' => 'IT Elective I - Platform Technologies (Laboratory)',
    'IT-110' => 'Data Structure and Algorithms',
    'IT-110L' => 'Data Structure and Algorithms (Laboratory)',
    'IT-111' => 'Fundamentals of Database System',
    'IT-111L' => 'Fundamentals of Database System (Laboratory)',
    'IT-112' => 'Integrative Programming and Technologies',
    'IT-112L' => 'Integrative Programming and Technologies (Laboratory)',
    'IT-113' => 'CISCO I - Networking Fundamentals',
    'IT-113L' => 'CISCO I - Networking Fundamentals (Laboratory)',
    'IT-114' => 'IT ELECTIVE II - Objective Oriented Programming',
    'IT-114L' => 'IT ELECTIVE II - Objective Oriented Programming (Laboratory)',
    'IT-115' => 'Introduction to Human Computer Interaction',
    'IT-115L' => 'Introduction to Human Computer Interaction (Laboratory)',
    'IT-116' => 'CISCO II - Routing and Switching Essential',
    'IT-116L' => 'CISCO II - Routing and Switching Essential (Laboratory)',
    'IT-117' => 'System Integration and Architecture I',
    'IT-117L' => 'System Integration and Architecture I (Laboratory)',
    'IT-119' => 'IT Elective IV - Web Systems and Technologies',
    'IT-119L' => 'IT Elective IV - Web Systems and Technologies (Laboratory)',
    'IT-120' => 'Geographic Information Systems',
    'IT-120L' => 'Geographic Information Systems (Laboratory)',
    'IT-121' => 'Information Management I',
    'IT-121L' => 'Information Management I (Laboratory)',
    'IT-122' => 'System Analysis and Design',
    'IT-122L' => 'System Analysis and Design (Laboratory)',
    'IT-123' => 'IT Elective V - System Integration and Architecture II',
    'IT-123L' => 'IT Elective V - System Integration and Architecture II (Laboratory)',
    'IT-124' => 'Quantitative Methods with Simulations and Modeling',
    'IT-125' => 'Information Assurance & Security I',
    'IT-125L' => 'Information Assurance & Security I (Laboratory)',
    'IT-126' => 'Social and Professional Issues',
    'IT-127' => 'Application Development and Emerging Technologies',
    'IT-127L' => 'Application Development and Emerging Technologies (Laboratory)',
    'IT-128' => 'Capstone Project I',
    'IT-128L' => 'Capstone Project I (Laboratory)',
    'IT-129' => 'System Administration and Maintenance',
    'IT-129L' => 'System Administration and Maintenance (Laboratory)',
    'IT-130' => 'Computer Hardware Repair and Maintenance',
    'IT-130L' => 'Computer Hardware Repair and Maintenance (Laboratory)',
    'IT-131' => 'Seminars and Fieldtrip',
    'IT-132' => 'Information Assurance and Security II',
    'IT-132L' => 'Information Assurance and Security II (Laboratory)',
    'IT-133' => 'Capstone Project 2',
    'IT-133L' => 'Capstone Project 2 (Laboratory)',
    'IT-134' => 'Practicum - 486 hours',
    'GE-101' => 'Understanding the Self',
    'GE-102' => 'Reading in Philippine History',
    'GE-103' => 'The Contemporary World',
    'GE-104' => 'Mathematics in the Modern World',
    'GE-105' => 'Purposive Communication',
    'GE-106' => 'Art Appreciation',
    'GE-107' => 'Science, Technology & Society',
    'GE-108' => 'Ethics',
    'GE-109' => 'Reading in Rizal\'s Life and Works',
    'GE-113' => 'Living in the IT Era (Elective)',
    'GE-117' => 'The Entrepreneurial Mind (Elective)',
    'GE-119' => 'Philippine Popular Culture (Elective)',
    'GE-129' => 'Filipino sa Antas Tersyarya sa Iba’t-Ibang Disiplina',
    'NSTP-101' => 'National Service Training Program 1',
    'NSTP-102' => 'National Service Training Program 2',
    'PATHFit-1' => 'Movement Competency Training',
    'PATHFit-2' => 'Exercise-based Fitness Activities',
    'PATHFit-3' => 'Physical Activities towards Health & Fitness 3 (Sports)',
    'PATHFit-4' => 'Physical Activities towards Health & Fitness 4 (Dance)',
    'IT 130L' => 'Computer Hardware Repair and Maintenance (Laboratory)'
];

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
    echo "Processing user $uid...\n";
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
                        addSched($db, $roomName, trim($day), $start, $end, $parts[0], $parts[1], $parts[2], $uid, $tid, $titles);
                    }
                }
            }
        }
        // Extra entries
        addSched($db, "CISCO (CON 102)", "Tuesday", "17:00:00", "19:00:00", "IT-126", "AI34", "TIQUEN", $uid, $tid, $titles);
        addSched($db, "CISCO (CON 102)", "Friday", "17:00:00", "19:00:00", "IT-126", "AI34", "TIQUEN", $uid, $tid, $titles);
        addSched($db, "CISCO (CON 102)", "Tuesday", "19:00:00", "21:00:00", "IT-126", "AI33", "TIQUEN", $uid, $tid, $titles);
        addSched($db, "CISCO (CON 102)", "Friday", "19:00:00", "21:00:00", "IT-126", "AI33", "TIQUEN", $uid, $tid, $titles);
    }
}

echo "SUCCESS: Seeding completed with full user isolation!";
?>
