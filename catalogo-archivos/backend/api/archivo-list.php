<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Archivos\ArchivoList;

$archivoList = new ArchivoList();
$archivoList->listar();
?>