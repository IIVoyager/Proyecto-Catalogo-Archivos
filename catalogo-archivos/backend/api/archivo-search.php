<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Archivos\ArchivoSearch;

$archivoSearch = new ArchivoSearch();
$archivoSearch->buscar();
?>