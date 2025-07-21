<?php
require_once __DIR__ . '../../app/models/Maquinas.php';

header('Content-Type: application/json');
echo json_encode(Maquinas::todas());
