<?php
require __DIR__ . '/../config.php';

$stmt = $pdo->query("
    SELECT e.*, j.nombre AS jn, d.nombre AS dn
    FROM empleados e
    JOIN jornadas j ON j.codigo = e.jornada_codigo
    JOIN departamentos d ON d.codigo = e.departamento_codigo
    ORDER BY e.codigo
");
echo json_encode($stmt->fetchAll());
