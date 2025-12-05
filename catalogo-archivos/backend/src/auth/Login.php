<?php
namespace CatalogoArchivos\Auth;

use CatalogoArchivos\Database;

class Login extends Database {
    private $data;
    
    public function __construct() {
        parent::__construct();
        session_start();
        $this->data = array(
            'status'  => 'error',
            'message' => 'Error en la autenticación'
        );
    }
    
    protected function ejecutar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarLogin();
        }
        
        $this->cerrar();
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
    
    private function procesarLogin() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($email) && !empty($password)) {
            $this->autenticarUsuario($email, $password);
        } else {
            $this->data['message'] = 'Email y contraseña son requeridos';
        }
    }
    
    private function autenticarUsuario($email, $password) {
        $conexion = $this->getConexion();
        $sql = "SELECT * FROM usuarios WHERE email = '{$email}'";
        
        if ($result = $conexion->query($sql)) {
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                $this->verificarCredenciales($user, $password, $conexion);
            } else {
                $this->data['message'] = 'Usuario no encontrado';
            }
            $result->free();
        } else {
            $this->data['message'] = "Error en la consulta: " . mysqli_error($conexion);
        }
    }
    
    private function verificarCredenciales($user, $password, $conexion) {
        if (password_verify($password, $user['password'])) {
            $this->iniciarSesion($user, $conexion);
        } else {
            $this->data['message'] = 'Contraseña incorrecta';
        }
    }
    
    private function iniciarSesion($user, $conexion) {
        $this->data['status'] = 'success';
        $this->data['message'] = 'Autenticación exitosa';
        $this->data['user_id'] = $user['id'];
        $this->data['user_name'] = $user['nombre'];
        $this->data['user_type'] = $user['tipo'];
        
        $token = bin2hex(random_bytes(32));
        $this->data['token'] = $token;
        
        $this->registrarBitacoraAcceso($user['id'], $conexion);
        $this->establecerSesion($user, $token);
    }
    
    private function registrarBitacoraAcceso($user_id, $conexion) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $sql_bitacora = "INSERT INTO bitacora_acceso (usuario_id, ip) VALUES ({$user_id}, '{$ip}')";
        $conexion->query($sql_bitacora);
    }
    
    private function establecerSesion($user, $token) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_type'] = $user['tipo'];
        $_SESSION['token'] = $token;
    }
    
    public function autenticar() {
        $this->ejecutar();
    }
}
?>