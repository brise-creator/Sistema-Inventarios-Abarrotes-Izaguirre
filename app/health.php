<?php
require __DIR__ . '/db.php';
$pdo->query('SELECT 1');
json_response(['ok' => true, 'servidor' => 'PHP + MySQL', 'base_de_datos' => 'conectada']);
