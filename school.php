<?php
require_once "pdo.php";
header("Content-Type: application/json; charset=utf-8");



$term = $_REQUEST['term'] . "%"; // Prepare the search term

$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
$stmt->execute(array(':prefix' => $term));

$retval = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $retval[] = $row['name'];
}

// Output JSON response
echo json_encode($retval, JSON_PRETTY_PRINT);
?>
