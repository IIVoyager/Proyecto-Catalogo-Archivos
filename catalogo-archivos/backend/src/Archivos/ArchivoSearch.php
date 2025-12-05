<?php
namespace CatalogoArchivos\Archivos;

use CatalogoArchivos\Database;

class ArchivoSearch extends Database {
    private $data;
    
    public function __construct() {
        parent::__construct();
        $this->data = array(
            'status' => 'success',
            'message' => '',
            'data' => array()
        );
    }
    
    protected function ejecutar() {
        if(isset($_GET['search'])) {
            $this->realizarBusqueda();
        } else {
            $this->data['status'] = 'error';
            $this->data['message'] = 'Término de búsqueda no proporcionado';
        }
        
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function realizarBusqueda() {
        $conexion = $this->getConexion();
        $search = $conexion->real_escape_string($_GET['search']);
        
        $sql = "SELECT * FROM catalogo 
                WHERE (nombre LIKE '%{$search}%' 
                    OR autor LIKE '%{$search}%' 
                    OR departamento LIKE '%{$search}%' 
                    OR empresa_institucion LIKE '%{$search}%' 
                    OR descripcion LIKE '%{$search}%') 
                AND eliminado = 0 
                ORDER BY fecha_creacion DESC";
        
        if($result = $conexion->query($sql)) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            
            if(!is_null($rows)) {
                $this->data['data'] = $rows;
                $this->data['message'] = count($rows) . ' resultado(s) encontrado(s)';
            } else {
                $this->data['message'] = 'No se encontraron resultados';
            }
            $result->free();
        } else {
            $this->data['status'] = 'error';
            $this->data['message'] = 'Error en la consulta: ' . mysqli_error($conexion);
        }
    }
    
    public function buscar() {
        $this->ejecutar();
    }
}
?>