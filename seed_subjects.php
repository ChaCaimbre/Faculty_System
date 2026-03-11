<?php
require_once 'config.php';
$db = getDB();

$subjects = [
    // FIRST YEAR - 1st Sem
    ['GE-101', 'Understanding the Self', 3],
    ['GE-102', 'Reading in Philippine History', 3],
    ['NSTP-101', 'National Service Training Program 1', 3],
    ['PATHFit-1', 'Movement Competency Training', 2],
    ['IT-101', 'Information Technology Fundamentals with Software Application', 2],
    ['IT-101L', 'Information Technology Fundamentals with Software Application (Laboratory)', 1],
    ['IT-102', 'Accounting Principle', 3],
    ['IT-103', 'Computer Programming I – Java', 2],
    ['IT-103L', 'Computer Programming I – Java (Laboratory)', 1],
    ['IT-106', 'Introduction to Computing', 3],

    // FIRST YEAR - 2nd Sem
    ['GE-103', 'The Contemporary World', 3],
    ['GE-104', 'Mathematics in the Modern World', 3],
    ['NSTP-102', 'National Service Training Program 2', 3],
    ['PATHFit-2', 'Exercise-based Fitness Activities', 2],
    ['IT-104', 'Discrete Mathematics', 3],
    ['IT-107', 'Multimedia System', 2],
    ['IT-107L', 'Multimedia System (Laboratory)', 1],
    ['IT-108', 'Programming II – Python', 2],
    ['IT-108L', 'Programming II – Python (Laboratory)', 1],
    ['IT-130', 'Computer Hardware Repair and Maintenance', 2],
    ['IT-130L', 'Computer Hardware Repair and Maintenance (Laboratory)', 1],

    // SECOND YEAR - 1st Sem
    ['GE-105', 'Purposive Communication', 3],
    ['GE-113', 'Living in the IT Era (Elective)', 3],
    ['PATHFit-3', 'Physical Activities towards Health & Fitness 3 (Sports)', 2],
    ['IT-109', 'IT Elective I – Platform Technologies', 2],
    ['IT-109L', 'IT Elective I – Platform Technologies (Laboratory)', 1],
    ['IT-110', 'Data Structure and Algorithms', 2],
    ['IT-110L', 'Data Structure and Algorithms (Laboratory)', 1],
    ['IT-111', 'Fundamentals of Database System', 2],
    ['IT-111L', 'Fundamentals of Database System (Laboratory)', 1],
    ['IT-113', 'CISCO I – Networking Fundamentals', 2],
    ['IT-113L', 'CISCO I – Networking Fundamentals (Laboratory)', 1],
    ['IT-114', 'IT Elective II – Object Oriented Programming', 2],
    ['IT-114L', 'IT Elective II – Object Oriented Programming (Laboratory)', 1],

    // SECOND YEAR - 2nd Sem
    ['GE-106', 'Art Appreciation', 3],
    ['GE-107', 'Science, Technology & Society', 3],
    ['GE-109', 'Reading in Rizal\'s Life and Works', 3],
    ['PATHFit-4', 'Physical Activities towards Health & Fitness 4 (Dance)', 2],
    ['IT-112', 'Integrative Programming and Technologies', 2],
    ['IT-112L', 'Integrative Programming and Technologies (Laboratory)', 1],
    ['IT-115', 'Introduction to Human Computer Interaction', 2],
    ['IT-115L', 'Introduction to Human Computer Interaction (Laboratory)', 1],
    ['IT-116', 'CISCO II – Routing and Switching Essential', 2],
    ['IT-116L', 'CISCO II – Routing and Switching Essential (Laboratory)', 1],
    ['IT-120', 'Geographic Information Systems', 2],
    ['IT-120L', 'Geographic Information Systems (Laboratory)', 1],

    // THIRD YEAR - 1st Sem
    ['GE-117', 'The Entrepreneurial Mind (Elective)', 3],
    ['GE-119', 'Philippine Popular Culture (Elective)', 3],
    ['IT-117', 'System Integration and Architecture I', 2],
    ['IT-117L', 'System Integration and Architecture I (Laboratory)', 1],
    ['IT-119', 'IT Elective IV – Web Systems and Technologies', 2],
    ['IT-119L', 'IT Elective IV – Web Systems and Technologies (Laboratory)', 1],
    ['IT-121', 'Information Management I', 2],
    ['IT-121L', 'Information Management I (Laboratory)', 1],
    ['IT-122', 'System Analysis and Design', 2],
    ['IT-122L', 'System Analysis and Design (Laboratory)', 1],
    ['IT-125', 'Information Assurance & Security I', 2],
    ['IT-125L', 'Information Assurance & Security I (Laboratory)', 1],

    // THIRD YEAR - 2nd Sem
    ['GE-108', 'Ethics', 3],
    ['IT-105', 'Mobile Development', 2],
    ['IT-105L', 'Mobile Development (Laboratory)', 1],
    ['IT-124', 'Quantitative Methods with Simulations and Modeling', 3],
    ['IT-126', 'Social and Professional Issues', 3],
    ['IT-127', 'Application Development and Emerging Technologies', 2],
    ['IT-127L', 'Application Development and Emerging Technologies (Laboratory)', 1],
    ['IT-128', 'Capstone Project I', 2],
    ['IT-128L', 'Capstone Project I (Laboratory)', 1],
    ['IT-129', 'System Administration and Maintenance', 2],
    ['IT-129L', 'System Administration and Maintenance (Laboratory)', 1],

    // FOURTH YEAR - 1st Sem
    ['GE-129', 'Filipino sa Antas Tersyarya sa Iba\'t-Ibang Disiplina', 3],
    ['IT-123', 'IT Elective V – System Integration and Architecture II', 2],
    ['IT-123L', 'IT Elective V – System Integration and Architecture II (Laboratory)', 1],
    ['IT-131', 'Seminars and Fieldtrip', 3],
    ['IT-132', 'Information Assurance and Security II', 2],
    ['IT-132L', 'Information Assurance and Security II (Laboratory)', 1],
    ['IT-133', 'Capstone Project 2', 2],
    ['IT-133L', 'Capstone Project 2 (Laboratory)', 1],

    // FOURTH YEAR - 2nd Sem
    ['IT-134', 'Practicum (486 hours)', 6],

    // EXTRAS from previous requests
    ['TOUR-101L', 'TOUR-101L', 3]
];

$db->query("SET FOREIGN_KEY_CHECKS = 0");
$db->query("TRUNCATE TABLE subjects");
$db->query("SET FOREIGN_KEY_CHECKS = 1");

$stmt = $db->prepare("INSERT INTO subjects (code, name, units) VALUES (?, ?, ?)");
foreach ($subjects as $s) {
    $stmt->bind_param("ssi", $s[0], $s[1], $s[2]);
    if (!$stmt->execute()) {
        echo "Error inserting subject {$s[0]}: " . $stmt->error . PHP_EOL;
    }
}
echo "Subjects re-seeded successfully with full curriculum data!" . PHP_EOL;
?>
