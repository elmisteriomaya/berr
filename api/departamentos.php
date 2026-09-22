<?php
require __DIR__ . '/../config.php';

$stmt = $pdo->query("SELECT * FROM departamentos ORDER BY codigo");
echo json_encode($stmt->fetchAll());
