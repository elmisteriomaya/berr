<?php
require __DIR__ . '/../config.php';

$stmt = $pdo->query("SELECT * FROM jornadas ORDER BY codigo");
echo json_encode($stmt->fetchAll());
