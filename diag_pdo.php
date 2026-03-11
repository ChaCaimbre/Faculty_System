<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=faculty_system', 'root', '');
    $res = $pdo->query('SHOW COLUMNS FROM faculty');
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . "\n";
    }
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
