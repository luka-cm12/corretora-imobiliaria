<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!isset($_GET['cidade']) || empty($_GET['cidade'])) {
    echo json_encode([]);
    exit;
}

$cidade = $conn->real_escape_string($_GET['cidade']);
$sql = "SELECT DISTINCT bairro FROM imoveis WHERE cidade = '$cidade' ORDER BY bairro";
$result = $conn->query($sql);

$bairros = [];
while ($row = $result->fetch_assoc()) {
    $bairros[] = $row['bairro'];
}

echo json_encode($bairros);