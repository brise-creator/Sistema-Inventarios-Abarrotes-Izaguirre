<?php
require 'db.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $stmt = $pdo->query('SELECT id, ticket, producto_id, producto_nombre, cantidad, precio_unitario, total, metodo_pago, DATE_FORMAT(fecha, "%d/%m/%Y") AS fecha FROM ventas ORDER BY id DESC');
    json_response(['ok' => true, 'ventas' => $stmt->fetchAll()]);
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = read_json_body();
    $cliente = trim($data['cliente'] ?? 'Cliente general');
    $productoId = (int)($data['producto_id'] ?? 0);
    $cantidad = max(1, (int)($data['cantidad'] ?? 1));
    $metodoPago = trim($data['metodo_pago'] ?? 'Efectivo');

    if ($cliente === '') {
        $cliente = 'Cliente general';
    }

    if (!$productoId) {
        json_response(['ok' => false, 'error' => 'Selecciona un producto.'], 400);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id, nombre, precio, existencias FROM productos WHERE id = ? FOR UPDATE');
        $stmt->execute([$productoId]);
        $producto = $stmt->fetch();

        if (!$producto) {
            throw new Exception('Producto no encontrado.');
        }

        if ($cantidad > (int)$producto['existencias']) {
            throw new Exception('No hay suficientes existencias.');
        }

        $stmt = $pdo->query('SELECT COALESCE(MAX(id), 0) + 1 AS siguiente FROM ventas');
        $ticket = 'TK-' . str_pad((string)$stmt->fetch()['siguiente'], 3, '0', STR_PAD_LEFT);
        $total = (float)$producto['precio'] * $cantidad;

        $stmt = $pdo->prepare('INSERT INTO ventas (ticket, cliente, producto_id, producto_nombre, cantidad, precio_unitario, total, metodo_pago, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())');
        $stmt->execute([$ticket, $cliente, $productoId, $producto['nombre'], $cantidad, $producto['precio'], $total, $metodoPago]);

        $stmt = $pdo->prepare('UPDATE productos SET existencias = existencias - ? WHERE id = ?');
        $stmt->execute([$cantidad, $productoId]);

        $stmt = $pdo->prepare('INSERT INTO movimientos (producto_id, producto_nombre, tipo, cantidad, fecha) VALUES (?, ?, "Venta", ?, CURDATE())');
        $stmt->execute([$productoId, $producto['nombre'], $cantidad]);

        $pdo->commit();
        json_response(['ok' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['ok' => false, 'error' => $e->getMessage()], 400);
    }
}

json_response(['ok' => false, 'error' => 'Acción no válida.'], 404);
?>
