<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Auth\Logout;

$logout = new Logout();
$logout->salir();
?>