<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Archivos\ArchivoDelete;

$archivoDelete = new ArchivoDelete();
$archivoDelete->eliminar();
?>