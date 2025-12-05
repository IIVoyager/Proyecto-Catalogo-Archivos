<?php
namespace CatalogoArchivos\Archivos;

use CatalogoArchivos\Database;

class ArchivoAdd extends Database {
    private $data;
    
    public function __construct() {
        parent::__construct();
        $this->data = array(
            'status'  => 'error',
            'message' => 'Error al agregar el archivo'
        );
    }
    
    protected function ejecutar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarArchivo();
        }
        
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function procesarArchivo() {
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $nombre = $_POST['nombre'] ?? '';
            $autor = $_POST['autor'] ?? '';
            $departamento = $_POST['departamento'] ?? '';
            $empresa = $_POST['empresa'] ?? '';
            $fecha_creacion = $_POST['fecha_creacion'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (empty($nombre)) {
                $this->data['message'] = 'El nombre del recurso es obligatorio';
                return;
            }
            
            $archivo_nombre = $_FILES['archivo']['name'];
            $archivo_temporal = $_FILES['archivo']['tmp_name'];
            $extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));
            
            $extensiones_permitidas = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
            if (!in_array($extension, $extensiones_permitidas)) {
                $this->data['message'] = 'Tipo de archivo no permitido';
                return;
            }
            
            $nuevo_nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
            $ruta_destino = __DIR__ . '/../../../backend/uploads/' . $nuevo_nombre_archivo;
            
            if (move_uploaded_file($archivo_temporal, $ruta_destino)) {
                $this->insertarEnBD($nombre, $autor, $departamento, $empresa, $fecha_creacion, $descripcion, $archivo_nombre, $nuevo_nombre_archivo, $extension, $ruta_destino);
            } else {
                $this->data['message'] = "Error al subir el archivo";
            }
        } else {
            $this->data['message'] = "No se ha seleccionado un archivo válido";
        }
    }
    
    private function insertarEnBD($nombre, $autor, $departamento, $empresa, $fecha_creacion, $descripcion, $archivo_nombre, $nuevo_nombre_archivo, $extension, $ruta_destino) {
        $conexion = $this->getConexion();
        $conexion->set_charset("utf8");
        $sql = "INSERT INTO catalogo (nombre, autor, departamento, empresa_institucion, fecha_creacion, descripcion, archivo_nombre, archivo_ruta, extension) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssssss", $nombre, $autor, $departamento, $empresa, $fecha_creacion, $descripcion, $archivo_nombre, $nuevo_nombre_archivo, $extension);
        
        if ($stmt->execute()) {
            $this->data['status'] = "success";
            $this->data['message'] = "Archivo agregado correctamente";
        } else {
            $this->data['message'] = "Error al guardar en la base de datos: " . $conexion->error;
            unlink($ruta_destino);
        }
        
        $stmt->close();
    }
    
    public function agregar() {
        $this->ejecutar();
    }
}
?>