<?php
$json = file_get_contents('http://localhost/api/lab_schedule.php');
$data = json_decode($json, true);
$first_lab = array_key_first($data);
$first_sched = $data[$first_lab][0] ?? null;
echo "First Lab: [$first_lab]\n";
if ($first_sched) {
    echo "Keys in schedule:\n";
    print_r(array_keys($first_sched));
}
?>
