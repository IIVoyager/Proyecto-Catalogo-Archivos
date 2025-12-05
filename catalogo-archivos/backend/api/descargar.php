<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Archivos\Descargar;

$descargar = new Descargar();
$descargar->descargar();
?>