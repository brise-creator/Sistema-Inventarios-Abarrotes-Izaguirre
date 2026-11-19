<?php
$fecha = date('Y-m-d');
$query = "SELECT SUM(total) as total_ventas, tipo_pago FROM ventas WHERE DATE(fecha) = '$fecha' GROUP BY tipo_pago";
?>