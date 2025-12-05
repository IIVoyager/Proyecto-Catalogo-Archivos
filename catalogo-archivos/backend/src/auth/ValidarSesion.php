<?php
namespace CatalogoArchivos\Auth;

class ValidarSesion {
    
    public function __construct() {
        session_start();
    }
    
    public function validar() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            http_response_code(401);
            echo json_encode(array('status' => 'error', 'message' => 'No autorizado'));
            exit();
        }
        
        return array(
            'user_id' => $_SESSION['user_id'],
            'user_type' => $_SESSION['user_type']
        );
    }
    
    public function validarAdmin() {
        $usuario = $this->validar();
        
        if ($usuario['user_type'] !== 'administrador') {
            http_response_code(403);
            echo json_encode(array('status' => 'error', 'message' => 'Acceso denegado. Se requiere rol de administrador'));
            exit();
        }
        
        return $usuario;
    }
}
?>