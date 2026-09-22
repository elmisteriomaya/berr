<?php
require __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT p.*, e.nombre AS en
        FROM permisos p
        JOIN empleados e ON e.codigo = p.empleado_codigo
        ORDER BY p.fecha DESC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ec = trim($data['empleado_codigo'] ?? '');
    $fecha = trim($data['fecha'] ?? '');
    $tipo = trim($data['tipo'] ?? '');
    $motivo = trim($data['motivo'] ?? '');

    if (!$ec || !$fecha || !$motivo) {
        http_response_code(400);
        echo json_encode(['error' => 'Completa empleado, fecha y motivo']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO permisos (empleado_codigo, fecha, tipo, motivo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ec, $fecha, $tipo, $motivo]);

    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
