<?php
namespace CatalogoArchivos\Auth;

use CatalogoArchivos\Database;

class Register extends Database {
    private $data;
    
    public function __construct() {
        parent::__construct();
        $this->data = array(
            'status'  => 'error',
            'message' => 'Error en el registro'
        );
    }
    
    protected function ejecutar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarRegistro();
        }
        
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function procesarRegistro() {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($nombre) && !empty($email) && !empty($tipo) && !empty($password)) {
            $this->validarYRegistrar($nombre, $email, $tipo, $password);
        } else {
            $this->data['message'] = 'Todos los campos son requeridos';
        }
    }
    
    private function validarYRegistrar($nombre, $email, $tipo, $password) {
        $tipos_permitidos = ['cliente', 'administrador'];
        if (!in_array($tipo, $tipos_permitidos)) {
            $this->data['message'] = 'Tipo de usuario no válido';
            return;
        }

        if ($this->emailExiste($email)) {
            $this->data['message'] = 'El email ya está registrado';
            return;
        }

        $this->crearUsuario($nombre, $email, $password, $tipo);
    }
    
    private function emailExiste($email) {
        $conexion = $this->getConexion();
        $sql = "SELECT id FROM usuarios WHERE email = '{$email}'";
        $result = $conexion->query($sql);
        $existe = $result->num_rows > 0;
        $result->free();
        return $existe;
    }
    
    private function crearUsuario($nombre, $email, $password, $tipo) {
        $conexion = $this->getConexion();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nombre, email, password, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $email, $password_hash, $tipo);
        
        if ($stmt->execute()) {
            $this->data['status'] = 'success';
            $this->data['message'] = 'Usuario registrado correctamente';
            $this->registrarPrimerAcceso($stmt->insert_id);
        } else {
            $this->data['message'] = "Error al registrar usuario: " . $conexion->error;
        }
        $stmt->close();
    }
    
    private function registrarPrimerAcceso($user_id) {
        $conexion = $this->getConexion();
        $ip = $_SERVER['REMOTE_ADDR'];
        $sql_bitacora = "INSERT INTO bitacora_acceso (usuario_id, ip) VALUES ({$user_id}, '{$ip}')";
        $conexion->query($sql_bitacora);
    }
    
    public function registrar() {
        $this->ejecutar();
    }
}
?>