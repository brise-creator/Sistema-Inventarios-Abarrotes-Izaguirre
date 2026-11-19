<?php
require 'db.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $proveedores = $pdo->query('SELECT id, nombre, telefono, correo, estado FROM proveedores ORDER BY id DESC')->fetchAll();
    $compras = $pdo->query('
        SELECT
            DATE_FORMAT(c.fecha, "%d/%m/%Y") AS fecha,
            pr.nombre AS proveedor,
            p.nombre AS producto,
            c.cantidad,
            c.total
        FROM compras c
        INNER JOIN proveedores pr ON pr.id = c.proveedor_id
        INNER JOIN productos p ON p.id = c.producto_id
        ORDER BY c.id DESC
    ')->fetchAll();

    json_response(['ok' => true, 'proveedores' => $proveedores, 'compras' => $compras]);
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = read_json_body();
    $nombre = trim($data['nombre'] ?? '');
    $telefono = trim($data['telefono'] ?? '');
    $correo = trim($data['correo'] ?? '');
    $estado = trim($data['estado'] ?? 'Activo');

    if ($nombre === '' || $telefono === '' || $correo === '') {
        json_response(['ok' => false, 'error' => 'Completa todos los campos.'], 400);
    }

    $stmt = $pdo->prepare('INSERT INTO proveedores (nombre, telefono, correo, estado) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nombre, $telefono, $correo, $estado]);

    json_response(['ok' => true]);
}

json_response(['ok' => false, 'error' => 'Acción no válida.'], 404);
?>
