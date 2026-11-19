<?php
require 'db.php';

$ventas = $pdo->query('
    SELECT
        DATE_FORMAT(fecha, "%d/%m/%Y") AS fecha,
        COUNT(*) AS total_ventas,
        COALESCE(SUM(cantidad), 0) AS productos_vendidos,
        COALESCE(SUM(total), 0) AS ingresos
    FROM ventas
    GROUP BY fecha
    ORDER BY fecha DESC
')->fetchAll();

$inventario = $pdo->query('
    SELECT
        p.nombre,
        p.existencias,
        CASE
            WHEN p.existencias <= 0 THEN "Agotado"
            WHEN p.existencias <= 10 THEN "Bajo stock"
            ELSE "Disponible"
        END AS estado,
        COALESCE((
            SELECT m.tipo
            FROM movimientos m
            WHERE m.producto_id = p.id
            ORDER BY m.id DESC
            LIMIT 1
        ), "Sin movimientos") AS ultimo_movimiento
    FROM productos p
    ORDER BY p.nombre
')->fetchAll();

$actividad = $pdo->query('
    SELECT
        "Sistema" AS usuario,
        CONCAT(tipo, " - ", producto_nombre) AS accion,
        DATE_FORMAT(fecha, "%d/%m/%Y") AS fecha,
        DATE_FORMAT(creado_en, "%h:%i %p") AS hora
    FROM movimientos
    ORDER BY id DESC
    LIMIT 20
')->fetchAll();

$masVendidos = $pdo->query('
    SELECT producto_nombre, SUM(cantidad) AS total
    FROM ventas
    GROUP BY producto_id, producto_nombre
    ORDER BY total DESC
    LIMIT 5
')->fetchAll();

json_response([
    'ok' => true,
    'ventas' => $ventas,
    'inventario' => $inventario,
    'actividad' => $actividad,
    'mas_vendidos' => $masVendidos
]);
?>