<?php
namespace CatalogoArchivos\Archivos;

use CatalogoArchivos\Database;

class Descargar extends Database {
    
    public function __construct() {
        parent::__construct();
        session_start();
    }
    
    protected function ejecutar() {
        if(isset($_GET['id']) && isset($_SESSION['user_id'])) {
            $this->procesarDescarga();
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'ID de archivo no proporcionado o usuario no autenticado'));
        }
        
        $this->cerrar();
    }
    
    private function procesarDescarga() {
        $archivo_id = $_GET['id'];
        $usuario_id = $_SESSION['user_id'];
        
        $archivo = $this->obtenerArchivo($archivo_id);
        
        if($archivo) {
            $this->descargarArchivo($archivo, $usuario_id, $archivo_id);
        } else {
            http_response_code(404);
            echo json_encode(array('status' => 'error', 'message' => 'Archivo no encontrado'));
        }
    }
    
    private function obtenerArchivo($archivo_id) {
        $conexion = $this->getConexion();
        $sql = "SELECT * FROM catalogo WHERE id = {$archivo_id} AND eliminado = 0";
        $result = $conexion->query($sql);
        
        if($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    private function descargarArchivo($archivo, $usuario_id, $archivo_id) {
        $ruta_archivo = __DIR__ . '/../../../backend/uploads/' . $archivo['archivo_ruta'];
        
        if(file_exists($ruta_archivo)) {
            $this->registrarBitacoraDescarga($usuario_id, $archivo_id);
            $this->enviarArchivo($archivo, $ruta_archivo);
        } else {
            http_response_code(404);
            echo json_encode(array('status' => 'error', 'message' => 'Archivo no encontrado en el servidor'));
        }
    }
    
    private function registrarBitacoraDescarga($usuario_id, $archivo_id) {
        $conexion = $this->getConexion();
        $ip = $_SERVER['REMOTE_ADDR'];
        $sql_bitacora = "INSERT INTO bitacora_descargas (usuario_id, archivo_id, ip) VALUES ({$usuario_id}, {$archivo_id}, '{$ip}')";
        $conexion->query($sql_bitacora);
    }
    
    private function enviarArchivo($archivo, $ruta_archivo) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $archivo['archivo_nombre'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($ruta_archivo));
        
        flush();
        readfile($ruta_archivo);
        exit;
    }
    
    public function descargar() {
        $this->ejecutar();
    }
}
?>