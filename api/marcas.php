<?php
require __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $n = isset($_GET['n']) ? (int)$_GET['n'] : 10;
    $stmt = $pdo->prepare("
        SELECT m.*, e.nombre AS en
        FROM marcas m
        JOIN empleados e ON e.codigo = m.empleado_codigo
        ORDER BY m.id DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $n, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ec = trim($data['empleado_codigo'] ?? '');
    $tipo = trim($data['tipo_marca'] ?? ''); // 'entrada' | 'salida'

    $stmt = $pdo->prepare("SELECT * FROM empleados WHERE codigo = ?");
    $stmt->execute([$ec]);
    $emp = $stmt->fetch();

    if (!$emp) {
        echo json_encode(['ok' => false, 'reason' => 'Código de empleado no existe']);
        exit;
    }
    if ($emp['estado'] !== 'activo') {
        echo json_encode(['ok' => false, 'reason' => 'Empleado inactivo']);
        exit;
    }

    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $stmt = $pdo->prepare("SELECT id FROM marcas WHERE empleado_codigo = ? AND fecha = ? AND tipo_marca = ?");
    $stmt->execute([$ec, $fecha, $tipo]);
    if ($stmt->fetch()) {
        echo json_encode(['ok' => false, 'reason' => "Ya existe una marca de $tipo hoy"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO marcas (empleado_codigo, tipo_marca, fecha, hora) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ec, $tipo, $fecha, $hora]);

    echo json_encode(['ok' => true, 'hora' => substr($hora, 0, 5)]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
