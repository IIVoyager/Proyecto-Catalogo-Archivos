<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Estadisticas\Estadisticas;

$estadisticas = new Estadisticas();
$estadisticas->generar();
?>