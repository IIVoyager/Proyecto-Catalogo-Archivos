<?php
namespace CatalogoArchivos\Archivos;

use CatalogoArchivos\Database;

class ArchivoList extends Database {
    private $data = array();
    
    public function __construct() {
        parent::__construct();
    }
    
    protected function ejecutar() {
        $this->obtenerCatalogo();
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function obtenerCatalogo() {
        $conexion = $this->getConexion();
        $sql = "SELECT * FROM catalogo WHERE eliminado = 0 ORDER BY fecha_creacion DESC";
        
        if($result = $conexion->query($sql)) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            
            if(!is_null($rows)) {
                foreach($rows as $num => $row) {
                    foreach($row as $key => $value) {
                        $this->data[$num][$key] = utf8_encode($value);
                    }
                }
            }
            $result->free();
        } else {
            die('Query Error: '.mysqli_error($conexion));
        }
    }
    
    public function listar() {
        $this->ejecutar();
    }
}
?>