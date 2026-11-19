<?php
require 'db.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $usuarios = $pdo->query('
        SELECT id, nombre, usuario, rol, estado
        FROM usuarios
        ORDER BY id DESC
    ')->fetchAll();

    json_response(['ok' => true, 'usuarios' => $usuarios]);
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = read_json_body();
    $nombre = trim($data['nombre'] ?? '');
    $usuario = trim($data['usuario'] ?? '');
    $password = trim($data['password'] ?? '');
    $rol = trim($data['rol'] ?? 'Cajero');

    if ($nombre === '' || $usuario === '' || $password === '') {
        json_response(['ok' => false, 'error' => 'Completa todos los campos.'], 400);
    }

    if (strlen($password) < 4) {
        json_response(['ok' => false, 'error' => 'La contraseña debe tener al menos 4 caracteres.'], 400);
    }

    if (!in_array($rol, ['Administrador', 'Cajero'], true)) {
        json_response(['ok' => false, 'error' => 'Rol no válido.'], 400);
    }

    try {
        $stmt = $pdo->prepare('
            INSERT INTO usuarios (nombre, usuario, password, rol, estado)
            VALUES (?, ?, ?, ?, "Activo")
        ');
        $stmt->execute([$nombre, $usuario, $password, $rol]);
    } catch (PDOException $e) {
        json_response(['ok' => false, 'error' => 'Ese nombre de usuario ya existe.'], 400);
    }

    json_response(['ok' => true]);
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = read_json_body();
    $usuario = trim($data['usuario'] ?? '');
    $password = trim($data['password'] ?? '');

    $stmt = $pdo->prepare('
        SELECT id, nombre, usuario, rol, estado
        FROM usuarios
        WHERE usuario = ? AND password = ? AND estado = "Activo"
        LIMIT 1
    ');
    $stmt->execute([$usuario, $password]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['ok' => false, 'error' => 'Usuario o contraseña incorrectos.'], 401);
    }

    json_response(['ok' => true, 'usuario' => $user]);
}

json_response(['ok' => false, 'error' => 'Acción no válida.'], 404);
?>
