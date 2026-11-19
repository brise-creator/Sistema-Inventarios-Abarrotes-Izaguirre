<?php
$id_venta = $_POST['id_venta'];
$monto = $_POST['monto'];

$insert = "INSERT INTO abonos (id_venta, monto) VALUES ($id_venta, $monto)";
$update = "UPDATE ventas SET saldo_pendiente = saldo_pendiente - $monto WHERE id_venta = $id_venta";
?>