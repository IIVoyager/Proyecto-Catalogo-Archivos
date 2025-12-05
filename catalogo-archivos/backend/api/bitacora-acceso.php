<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Bitacora\BitacoraAcceso;

$bitacoraAcceso = new BitacoraAcceso();
$bitacoraAcceso->listar();
?>