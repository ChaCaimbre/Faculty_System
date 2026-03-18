<?php
require_once 'config.php';
$db = getDB();

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
    'IT 130L' => 'Computer Hardware Repair and Maintenance (Laboratory)',
    'IT-128 L' => 'Capstone Project I (Laboratory)',
    'LIS-112' => 'Reference Information Services (Wait, the images don\'t have LIS, but maybe I can guess or ignore. I\'ll leave LIS out as it\'s not in the provided images for curriculum)'
];

$stmt = $db->prepare("UPDATE subjects SET name = ? WHERE code = ?");
foreach ($titles as $code => $name) {
    if (!$code || trim($code) === '') continue;
    $stmt->bind_param("ss", $name, $code);
    $stmt->execute();
    if ($db->affected_rows > 0) {
        echo "Updated $code -> $name\n";
    }
}
echo "Done.";
?>
