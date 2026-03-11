<?php
require_once 'config.php';
$db = getDB();

$teacherData = [
    'Amago' => [
        'IT-128' => ['AI32', 'AI36', 'AI35'],
        'IT-128L' => ['AI32', 'AI36', 'AI35'],
        'IT-112' => ['AI21', 'AI25'],
        'IT-112L' => ['AI21', 'AI25']
    ],
    'Almenario' => [
        'TOUR-101' => ['MT12'],
        'TOUR-101L' => ['MT12']
    ],
    'Cabangon' => [
        'IT-105' => ['AI31', 'AI32', 'AI33', 'AI35'],
        'IT-105L' => ['AI31', 'AI32', 'AI33', 'AI35'],
        'IT-116' => ['AI24'],
        'IT-116L' => ['AI24']
    ],
    'Caluza' => [
        'IT-124' => ['AI31', 'AI32'],
        'IT-128' => ['AI33'],
        'IT-128L' => ['AI33']
    ],
    'Celestial' => [
        'IT-127' => ['AI31'],
        'IT-127L' => ['AI31']
    ],
    'Cinco' => [
        'IT-108' => ['AI31'],
        'IT-108L' => ['AI31'],
        'IT-116' => ['AI22', 'AI23'], // Text says AI22, but let's check image... image has AI22 in CISCO. Text list for Ormeneta also has AI22/AI23. 
        'IT-116L' => ['AI22', 'AI25'], // Text for Cinco has IT-116L AI22. IT-116L AI25 is usually Cinco too.
        'IT-134' => ['AI42']
    ],
    'Combinido' => [
        'IT-112' => ['AI24'],
        'IT-112L' => ['AI24']
    ],
    'Dableo' => [
        'IT-129' => ['AI33'],
        'IT-129L' => ['AI33']
    ],
    'Dalan' => [
        'IT-108' => ['AI11', 'AI14'],
        'IT-108L' => ['AI11', 'AI14'],
        'IT-120' => ['AI21', 'AI23'],
        'IT-120L' => ['AI21', 'AI23'],
        'IT-130' => ['AI11'],
        'IT-130L' => ['AI11']
    ],
    'Diaz' => [
        'IT-129' => ['AI35'],
        'IT-129L' => ['AI35']
    ],
    'Durango' => [
        'IT-115' => ['AI22', 'AI25'],
        'IT-115L' => ['AI22', 'AI25'],
        'IT-130' => ['AI12', 'AI13'],
        'IT-130L' => ['AI12', 'AI13']
    ],
    'Fernandez' => [
        'IT-115' => ['AI21', 'AI23', 'AI24'],
        'IT-115L' => ['AI21', 'AI23', 'AI24']
    ],
    'Funcion' => [
        'IT-124' => ['AI33'],
        'IT-128' => ['AI31', 'AI34'],
        'IT-128L' => ['AI31', 'AI34']
    ],
    'Galban' => [
        'IT-107' => ['AI13', 'AI14', 'AI15'],
        'IT-107L' => ['AI13', 'AI14', 'AI15'],
        'GE-113' => ['AS22']
    ],
    'Lagonoy' => [
        'IT-127' => ['AI32', 'AI33'],
        'IT-127L' => ['AI32', 'AI33']
    ],
    'Laurente' => [
        'IT-104' => ['AI14'],
        'IT-124' => ['AI41'],
        'IT-134' => ['AI41']
    ],
    'Memoracion' => [
        'IT-124' => ['AI35'],
        'TOUR-101' => ['MT13'],
        'TOUR-101L' => ['MT13']
    ],
    'Murillo' => [
        'IT-127' => ['AI34', 'AI35'],
        'IT-127L' => ['AI34', 'AI35']
    ],
    'Nicolas' => [
        'GE-113' => ['MH22', 'MH23']
    ],
    'Navarro' => [
        'SPT-104' => ['AL21'],
        'LIS-106' => ['AL21'],
        'GE-107' => ['AL21'],
        'SPT-110' => ['AL21'],
        'SPT-111' => ['AL31'],
        'SPT-112' => ['AL31']
    ],
    'Ormeneta' => [
        'IT-108' => ['AI12', 'AI15'],
        'IT-108L' => ['AI12', 'AI15'],
        'IT-112' => ['AI22', 'AI23'],
        'IT-112L' => ['AI22', 'AI23']
    ],
    'Quisumbing' => [
        'IT-116' => ['AI21', 'AI23'],
        'IT-116L' => ['AI21', 'AI23']
    ],
    'Tibe' => [
        'IT-120' => ['AI22', 'AI24', 'AI25'],
        'IT-120L' => ['AI22', 'AI24', 'AI25'],
        'IT-126' => ['AI32'],
        'IT-129' => ['AI31'],
        'IT-129L' => ['AI31']
    ]
];

// Helper to normalize name (Uppercase for DB matching)
function norm($n)
{
    return strtoupper($n);
}

foreach ($teacherData as $name => $subjects) {
    $uname = norm($name);
    // Ensure faculty exists
    $f_res = $db->query("SELECT id FROM faculty WHERE name = '$uname'");
    if ($f_res->num_rows == 0) {
        $db->query("INSERT INTO faculty (name) VALUES ('$uname')");
        $fid = $db->insert_id;
    }
    else {
        $fid = $f_res->fetch_assoc()['id'];
    }

    foreach ($subjects as $subCode => $sections) {
        // Find schedules where subject matches and section matches one of these
        // If we find a schedule with this subject and section, but different teacher, UPDATE IT.
        foreach ($sections as $sec) {
            // Find subject
            $s_res = $db->query("SELECT id FROM subjects WHERE code = '$subCode'");
            if ($s_res->num_rows > 0) {
                $sid = $s_res->fetch_assoc()['id'];

                // Update schedules that match subject and section
                $db->query("UPDATE schedules SET faculty_id = $fid WHERE subject_id = $sid AND section = '$sec'");
                if ($db->affected_rows > 0) {
                    echo "Updated $subCode ($sec) -> $name\n";
                }
            }
        }
    }
}

echo "Faculty assignments synchronized with provided list!\n";
?>
