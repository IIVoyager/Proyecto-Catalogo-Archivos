<?php
namespace CatalogoArchivos\Archivos;

use CatalogoArchivos\Database;

class ArchivoEdit extends Database {
    private $data;
    private $id;
    private $nombre;
    private $autor;
    private $departamento;
    private $empresa;
    private $fecha_creacion;
    private $descripcion;
    private $nuevo_nombre_archivo = null;
    private $archivo_anterior = null;
    private $ruta_destino = null;
    
    public function __construct() {
        parent::__construct();
        $this->data = array(
            'status'  => 'error',
            'message' => 'Error al actualizar el archivo'
        );
    }
    
    protected function ejecutar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->obtenerDatosFormulario();
            
            if ($this->validarCampos()) {
                $this->procesarArchivo();
            }
        } else {
            $this->data['message'] = "Método no permitido";
        }
        
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function obtenerDatosFormulario() {
        $this->id = $_POST['id'] ?? '';
        $this->nombre = $_POST['nombre'] ?? '';
        $this->autor = $_POST['autor'] ?? '';
        $this->departamento = $_POST['departamento'] ?? '';
        $this->empresa = $_POST['empresa'] ?? '';
        $this->fecha_creacion = $_POST['fecha_creacion'] ?? '';
        $this->descripcion = $_POST['descripcion'] ?? '';
    }
    
    private function validarCampos() {
        if (empty($this->id) || empty($this->nombre)) {
            $this->data['message'] = 'ID y nombre del recurso son obligatorios';
            return false;
        }
        return true;
    }
    
    private function procesarArchivo() {
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $this->subirNuevoArchivo();
        }
        
        $this->actualizarRegistro();
    }
    
    private function subirNuevoArchivo() {
        $archivo_nombre = $_FILES['archivo']['name'];
        $archivo_temporal = $_FILES['archivo']['tmp_name'];
        $extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));
        
        $extensiones_permitidas = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
        if (!in_array($extension, $extensiones_permitidas)) {
            $this->data['message'] = 'Tipo de archivo no permitido';
            exit;
        }
        
        $this->nuevo_nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
        $this->ruta_destino = __DIR__ . '/../../../backend/uploads/' . $this->nuevo_nombre_archivo;
        
        if (!move_uploaded_file($archivo_temporal, $this->ruta_destino)) {
            $this->data['message'] = "Error al subir el archivo";
            exit;
        }
        
        $this->obtenerArchivoAnterior();
    }
    
    private function obtenerArchivoAnterior() {
        $conexion = $this->getConexion();
        $sql_anterior = "SELECT archivo_ruta FROM catalogo WHERE id = ? AND eliminado = 0";
        $stmt_anterior = $conexion->prepare($sql_anterior);
        $stmt_anterior->bind_param("i", $this->id);
        $stmt_anterior->execute();
        $result_anterior = $stmt_anterior->get_result();
        $this->archivo_anterior = $result_anterior->fetch_assoc();
        $stmt_anterior->close();
    }
    
    private function actualizarRegistro() {
        $conexion = $this->getConexion();
        $conexion->set_charset("utf8");
        
        if ($this->nuevo_nombre_archivo) {
            $this->actualizarConArchivo($conexion);
        } else {
            $this->actualizarSinArchivo($conexion);
        }
    }
    
    private function actualizarConArchivo($conexion) {
        $archivo_nombre = $_FILES['archivo']['name'];
        $extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));
        
        $sql = "UPDATE catalogo SET 
                nombre = ?,
                autor = ?,
                departamento = ?,
                empresa_institucion = ?,
                fecha_creacion = ?,
                descripcion = ?,
                archivo_nombre = ?,
                archivo_ruta = ?,
                extension = ?
                WHERE id = ? AND eliminado = 0";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssssssi", $this->nombre, $this->autor, $this->departamento, $this->empresa, $this->fecha_creacion, $this->descripcion, $archivo_nombre, $this->nuevo_nombre_archivo, $extension, $this->id);
        
        $this->ejecutarActualizacion($stmt, $conexion);
    }
    
    private function actualizarSinArchivo($conexion) {
        $sql = "UPDATE catalogo SET 
                nombre = ?,
                autor = ?,
                departamento = ?,
                empresa_institucion = ?,
                fecha_creacion = ?,
                descripcion = ?
                WHERE id = ? AND eliminado = 0";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssi", $this->nombre, $this->autor, $this->departamento, $this->empresa, $this->fecha_creacion, $this->descripcion, $this->id);
        
        $this->ejecutarActualizacion($stmt, $conexion);
    }
    
    private function ejecutarActualizacion($stmt, $conexion) {
        if ($stmt->execute()) {
            $this->manejarExito();
        } else {
            $this->manejarError($conexion);
        }
        
        $stmt->close();
    }
    
    private function manejarExito() {
        if ($this->nuevo_nombre_archivo && isset($this->archivo_anterior)) {
            $ruta_anterior = __DIR__ . '/../../../backend/uploads/' . $this->archivo_anterior['archivo_ruta'];
            if (file_exists($ruta_anterior)) {
                unlink($ruta_anterior);
            }
        }
        
        $this->data['status'] = "success";
        $this->data['message'] = "Archivo actualizado correctamente";
    }
    
    private function manejarError($conexion) {
        $this->data['message'] = "Error al actualizar: " . $conexion->error;
        if ($this->nuevo_nombre_archivo) {
            unlink($this->ruta_destino);
        }
    }
    
    public function editar() {
        $this->ejecutar();
    }
}
?>