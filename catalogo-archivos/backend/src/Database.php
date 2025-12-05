<?php
namespace CatalogoArchivos;

abstract class Database {
    protected $conexion;
    
    public function __construct() {
        $this->conectar();
    }
    
    protected function conectar() {
        $this->conexion = @mysqli_connect(
            'localhost',
            'root',
            '',
            'catalogo_archivos'
        );

        if(!$this->conexion) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 'error',
                'message' => '¡Base de datos NO conectada!'
            ));
            exit();
        }

        // Configurar charset
        mysqli_set_charset($this->conexion, "utf8");
    }
    
    public function cerrar() {
        if($this->conexion) {
            mysqli_close($this->conexion);
        }
    }
    
    public function getConexion() {
        return $this->conexion;
    }
    
    abstract protected function ejecutar();
}
?>