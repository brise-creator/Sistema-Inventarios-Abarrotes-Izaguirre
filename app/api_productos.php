<?php
require 'db.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, nombre, categoria, precio, existencias FROM productos ORDER BY id DESC');
    json_response(['ok' => true, 'productos' => $stmt->fetchAll()]);
}

if ($action === 'movements') {
    $stmt = $pdo->query('SELECT DATE_FORMAT(fecha, "%d/%m/%Y") AS fecha, producto_nombre, tipo, cantidad FROM movimientos ORDER BY id DESC LIMIT 30');
    json_response(['ok' => true, 'movimientos' => $stmt->fetchAll()]);
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = read_json_body();
    $nombre = trim($data['nombre'] ?? '');
    $categoria = trim($data['categoria'] ?? '');
    $precio = (float)($data['precio'] ?? 0);
    $existencias = max(0, (int)($data['existencias'] ?? 0));

    if ($nombre === '' || $categoria === '') {
        json_response(['ok' => false, 'error' => 'Completa nombre y categoría.'], 400);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO productos (nombre, categoria, precio, existencias) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nombre, $categoria, $precio, $existencias]);
        $productoId = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO movimientos (producto_id, producto_nombre, tipo, cantidad, fecha) VALUES (?, ?, "Registro", ?, CURDATE())');
        $stmt->execute([$productoId, $nombre, $existencias]);

        $pdo->commit();
        json_response(['ok' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        json_response(['ok' => false, 'error' => 'No se pudo registrar el producto. Puede que ya exista.'], 400);
    }
}

if ($action === 'movement' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = read_json_body();
    $productoId = (int)($data['producto_id'] ?? 0);
    $tipo = $data['tipo'] ?? '';
    $cantidad = max(1, (int)($data['cantidad'] ?? 1));
    $tiposValidos = ['Entrada', 'Salida', 'Merma'];

    if (!$productoId || !in_array($tipo, $tiposValidos, true)) {
        json_response(['ok' => false, 'error' => 'Movimiento inválido.'], 400);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id, nombre, existencias FROM productos WHERE id = ? FOR UPDATE');
        $stmt->execute([$productoId]);
        $producto = $stmt->fetch();

        if (!$producto) {
            throw new Exception('Producto no encontrado.');
        }

        if (($tipo === 'Salida' || $tipo === 'Merma') && $cantidad > (int)$producto['existencias']) {
            throw new Exception('No hay suficientes existencias.');
        }

        $nuevaExistencia = $tipo === 'Entrada'
            ? (int)$producto['existencias'] + $cantidad
            : (int)$producto['existencias'] - $cantidad;

        $stmt = $pdo->prepare('UPDATE productos SET existencias = ? WHERE id = ?');
        $stmt->execute([$nuevaExistencia, $productoId]);

        $stmt = $pdo->prepare('INSERT INTO movimientos (producto_id, producto_nombre, tipo, cantidad, fecha) VALUES (?, ?, ?, ?, CURDATE())');
        $stmt->execute([$productoId, $producto['nombre'], $tipo, $cantidad]);

        $pdo->commit();
        json_response(['ok' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['ok' => false, 'error' => $e->getMessage()], 400);
    }
}

json_response(['ok' => false, 'error' => 'Acción no válida.'], 404);
?>
