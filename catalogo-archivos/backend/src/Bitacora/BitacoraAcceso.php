<?php
namespace CatalogoArchivos\Bitacora;

use CatalogoArchivos\Database;

class BitacoraAcceso extends Database {
    private $data = array();
    
    public function __construct() {
        parent::__construct();
    }
    
    protected function ejecutar() {
        $this->obtenerBitacora();
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function obtenerBitacora() {
        $conexion = $this->getConexion();
        $sql = "SELECT ba.*, u.nombre as usuario_nombre, u.tipo as usuario_tipo 
                FROM bitacora_acceso ba 
                JOIN usuarios u ON ba.usuario_id = u.id 
                ORDER BY ba.fecha_acceso DESC 
                LIMIT 100";
        
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