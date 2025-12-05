<?php
namespace CatalogoArchivos\Estadisticas;

use CatalogoArchivos\Database;

class Estadisticas extends Database {
    private $data = array();
    
    public function __construct() {
        parent::__construct();
    }
    
    protected function ejecutar() {
        $this->obtenerEstadisticas();
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function obtenerEstadisticas() {
        $conexion = $this->getConexion();
        
        $this->data['tipos_archivo'] = $this->obtenerTiposArchivo($conexion);
        $this->data['dias_semana'] = $this->obtenerDiasSemana($conexion);
        $this->data['horas_dia'] = $this->obtenerHorasDia($conexion);
    }
    
    private function obtenerTiposArchivo($conexion) {
        $tipos = array();
        $sql = "SELECT c.extension, COUNT(*) as total 
                FROM bitacora_descargas bd 
                JOIN catalogo c ON bd.archivo_id = c.id 
                GROUP BY c.extension";
        $result = $conexion->query($sql);
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $tipos[] = $row;
            }
            $result->free();
        }
        return $tipos;
    }
    
    private function obtenerDiasSemana($conexion) {
        $dias = array();
        $sql = "SELECT DAYNAME(fecha_descarga) as dia, COUNT(*) as total 
                FROM bitacora_descargas 
                GROUP BY DAYNAME(fecha_descarga) 
                ORDER BY FIELD(dia, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
        $result = $conexion->query($sql);
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $dias[] = $row;
            }
            $result->free();
        }
        return $dias;
    }
    
    private function obtenerHorasDia($conexion) {
        $horas = array();
        $sql = "SELECT HOUR(fecha_descarga) as hora, COUNT(*) as total 
                FROM bitacora_descargas 
                GROUP BY HOUR(fecha_descarga) 
                ORDER BY hora";
        $result = $conexion->query($sql);
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $horas[] = $row;
            }
            $result->free();
        }
        return $horas;
    }
    
    public function generar() {
        $this->ejecutar();
    }
}
?>