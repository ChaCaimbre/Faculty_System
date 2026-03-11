<?php
require_once 'config.php';
$db = getDB();

$names = [
    'APOLINAR' => 'Micheline G. Apolinar',
    'CINCO' => 'Jeffery C. Cinco',
    'AMAGO' => 'Amago',
    'ALMENARIO' => 'Almenario',
    'CABANGON' => 'Cabangon',
    'CALUZA' => 'Caluza',
    'CELESTIAL' => 'Celestial',
    'COMBINIDO' => 'Combinido',
    'DABLEO' => 'Dableo',
    'DALAN' => 'Dalan',
    'DIAZ' => 'Diaz',
    'DURANGO' => 'Durango',
    'FERNANDEZ' => 'Fernandez',
    'FUNCION' => 'Funcion',
    'GALBAN' => 'Galban',
    'LAGONOY' => 'Lagonoy',
    'LAURENTE' => 'Laurente',
    'MEMORACION' => 'Memoracion',
    'MURILLO' => 'Murillo',
    'NICOLAS' => 'Nicolas',
    'NAVARRO' => 'Navarro',
    'ORMENETA' => 'Ormeneta',
    'QUISUMBING' => 'Quisumbing',
    'TIBE' => 'Tibe'
];

foreach ($names as $old => $new) {
    $db->query("UPDATE faculty SET name = '$new' WHERE name = '$old'");
}

echo "Faculty names updated to match display requirements!\n";
?>
