<?php
namespace CatalogoArchivos\Auth;

use CatalogoArchivos\Database;

class Logout extends Database {
    private $data;
    
    public function __construct() {
        parent::__construct();
        session_start();
        $this->data = array(
            'status'  => 'success',
            'message' => 'Sesión cerrada correctamente'
        );
    }
    
    protected function ejecutar() {
        $this->cerrarSesion();
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function cerrarSesion() {
        session_unset();
        session_destroy();
    }
    
    public function salir() {
        $this->ejecutar();
    }
}
?>