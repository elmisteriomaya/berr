<?php
// config.php — conexión a MySQL y headers compartidos por todos los endpoints

header('Access-Control-Allow-Origin: *'); // en producción: pon la URL exacta de tu Static Web App
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Preflight de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==== EDITA ESTOS 4 VALORES CON LOS DE TU AZURE DATABASE FOR MYSQL ====
$DB_HOST = 'tu-servidor.mysql.database.azure.com';
$DB_NAME = 'asistencia';
$DB_USER = 'tu_usuario';
$DB_PASS = 'tu_password';
// =======================================================================

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Conexión fallida: ' . $e->getMessage()]);
    exit;
}
