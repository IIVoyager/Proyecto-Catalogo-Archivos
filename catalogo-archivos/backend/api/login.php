<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CatalogoArchivos\Auth\Login;

$login = new Login();
$login->autenticar();
?>