<?php
require __DIR__ . '/../config.php';

$ec = $_GET['empleado'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

if (!$ec || !$desde || !$hasta) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros: empleado, desde, hasta']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.*, j.nombre AS jn, j.hora_entrada, j.hora_salida, d.nombre AS dn
    FROM empleados e
    JOIN jornadas j ON j.codigo = e.jornada_codigo
    JOIN departamentos d ON d.codigo = e.departamento_codigo
    WHERE e.codigo = ?
");
$stmt->execute([$ec]);
$emp = $stmt->fetch();

if (!$emp) {
    echo json_encode(null);
    exit;
}

function toMin($hhmm) {
    [$h, $m] = explode(':', substr($hhmm, 0, 5));
    return ((int)$h) * 60 + (int)$m;
}
function fmtHM($m) {
    $m = max(0, $m);
    return floor($m / 60) . ':' . str_pad($m % 60, 2, '0', STR_PAD_LEFT);
}

$dias = [];
$tT = 0;
$tE = 0;

$cur = new DateTime($desde);
$fin = new DateTime($hasta);

$stmtEntrada = $pdo->prepare("SELECT hora FROM marcas WHERE empleado_codigo=? AND fecha=? AND tipo_marca='entrada'");
$stmtSalida  = $pdo->prepare("SELECT hora FROM marcas WHERE empleado_codigo=? AND fecha=? AND tipo_marca='salida'");
$stmtPermiso = $pdo->prepare("SELECT motivo FROM permisos WHERE empleado_codigo=? AND fecha=?");

while ($cur <= $fin) {
    $fecha = $cur->format('Y-m-d');

    $stmtEntrada->execute([$ec, $fecha]);
    $en = $stmtEntrada->fetch();

    $stmtSalida->execute([$ec, $fecha]);
    $sa = $stmtSalida->fetch();

    $stmtPermiso->execute([$ec, $fecha]);
    $pe = $stmtPermiso->fetch();

    $mt = 0;
    $me = 0;
    $hs = '*';

    if ($en) {
        $d = toMin($en['hora']) - toMin($emp['hora_entrada']);
        if ($d > 0) $mt = $d;
    }
    if ($sa) {
        $d = toMin($emp['hora_salida']) - toMin($sa['hora']);
        if ($d > 0) $me = $d;
    }
    if ($en && $sa) {
        $hs = fmtHM(toMin($sa['hora']) - toMin($en['hora']) - 60); // resta 1h de almuerzo
    }

    $tT += $mt;
    $tE += $me;

    $dias[] = [
        'fecha' => $fecha,
        'entrada' => $en ? substr($en['hora'], 0, 5) : '*',
        'salida' => $sa ? substr($sa['hora'], 0, 5) : '*',
        'mt' => $mt,
        'me' => $me,
        'hs' => $hs,
        'obs' => $pe ? $pe['motivo'] : '',
    ];

    $cur->modify('+1 day');
}

echo json_encode(['e' => $emp, 'dias' => $dias, 'tT' => $tT, 'tE' => $tE]);
