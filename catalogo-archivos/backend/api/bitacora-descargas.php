<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Bitacora\BitacoraDescargas;

$bitacoraDescargas = new BitacoraDescargas();
$bitacoraDescargas->listar();
?>