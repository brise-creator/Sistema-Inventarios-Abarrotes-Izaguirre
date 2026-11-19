<?php
// Conexión PHP -> MySQL usando el nombre del servicio Docker "db".
// Las contraseñas se leen de variables de entorno: nunca del repositorio.
header('Content-Type: application/json; charset=utf-8');

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'sistema_gestion';
$user = getenv('DB_USER') ?: 'inventarios_user';
$password = getenv('DB_PASSWORD');

if ($password === false || $password === '') {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Falta configurar DB_PASSWORD en Docker.']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log('Error de conexión MySQL: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'No se pudo conectar a la base de datos.'
    ]);
    exit;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body() {
    $input = file_get_contents('php://input');
    return $input ? json_decode($input, true) : [];
}
