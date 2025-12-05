<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Auth\Register;

$register = new Register();
$register->registrar();
?>