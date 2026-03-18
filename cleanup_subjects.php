<?php
require_once 'config.php';
$db = getDB();

// 1. Remove duplicate codes
$res = $db->query("SELECT code, MIN(id) as first_id FROM subjects GROUP BY code HAVING COUNT(*) > 1");
while($row = $res->fetch_assoc()){
    echo "Found duplicates for {$row['code']}. Leaving ID: {$row['first_id']}\n";
    $code = $db->real_escape_string($row['code']);
    $first_id = $row['first_id'];
    
    // Update schedules belonging to duplicate IDs
    $db->query("UPDATE schedules SET subject_id = $first_id WHERE subject_id IN (SELECT id FROM subjects WHERE code = '$code' AND id != $first_id)");
    
    // Delete duplicate subjects
    $db->query("DELETE FROM subjects WHERE code = '$code' AND id != $first_id");
}

// 2. Add UNIQUE index to prevents future duplicates if possible?
// We might not want to if we want same code in different curricula, but curriculum is also a column here!
// It seems user intended to have it per user.
// $db->query("ALTER TABLE subjects ADD UNIQUE (code, user_id, curriculum_id, term_id)");?
// For now, let's just clean up.

echo "Cleanup finished!";
?>
