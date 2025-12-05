<?php
namespace CatalogoArchivos\Archivos;

use CatalogoArchivos\Database;

class ArchivoDelete extends Database {
    private $data;
    
    public function __construct() {
        parent::__construct();
        $this->data = array(
            'status'  => 'error',
            'message' => 'Error al eliminar el archivo'
        );
    }
    
    protected function ejecutar() {
        if(isset($_GET['id'])) {
            $this->eliminarRegistro(intval($_GET['id']));
        } else {
            $this->data['message'] = "ID no proporcionado";
        }
        
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function eliminarRegistro($id) {
        $archivo = $this->obtenerArchivo($id);
        $this->eliminarLogico($id, $archivo);
    }
    
    private function obtenerArchivo($id) {
        $conexion = $this->getConexion();
        $sql_select = "SELECT archivo_ruta FROM catalogo WHERE id = ? AND eliminado = 0";
        $stmt_select = $conexion->prepare($sql_select);
        $stmt_select->bind_param("i", $id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $archivo = $result->fetch_assoc();
        $stmt_select->close();
        
        return $archivo;
    }
    
    private function eliminarLogico($id, $archivo) {
        $conexion = $this->getConexion();
        $sql = "UPDATE catalogo SET eliminado = 1 WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            $this->eliminarArchivoFisico($archivo);
            $this->data['status'] = "success";
            $this->data['message'] = "Archivo eliminado";
        } else {
            $this->data['message'] = "ERROR: No se ejecutó $sql. " . mysqli_error($conexion);
        }
        
        $stmt->close();
        $this->cerrar();
    }
    
    private function eliminarArchivoFisico($archivo) {
        if($archivo && file_exists(__DIR__ . '/../../../backend/uploads/' . $archivo['archivo_ruta'])) {
            unlink(__DIR__ . '/../../../backend/uploads/' . $archivo['archivo_ruta']);
        }
    }
    
    public function eliminar() {
        $this->ejecutar();
    }
}
?>