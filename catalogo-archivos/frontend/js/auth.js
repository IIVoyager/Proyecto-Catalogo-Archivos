$(document).ready(function() {
    // Verificar si ya hay una sesión activa
    verificarSesion();
    
    // Manejar formulario de login
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        iniciarSesion();
    });
    
    // Manejar formulario de registro
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        registrarse();
    });
});

function verificarSesion() {
    const token = localStorage.getItem('auth_token');
    const userType = localStorage.getItem('user_type');
    
    if (token && userType) {
        // Redirigir según el tipo de usuario
        if (userType === 'admin') {
            window.location.href = 'dashboard-admin.html';
        } else {
            window.location.href = 'catalogo-cliente.html';
        }
    }
}

function iniciarSesion() {
    const email = $('#login-email').val();
    const password = $('#login-password').val();
    
    // Validación básica
    if (!email || !password) {
        mostrarMensaje('error', 'Por favor completa todos los campos');
        return;
    }
    
    $.ajax({
        url: '../backend/api/login.php',
        type: 'POST',
        data: {
            email: email,
            password: password
        },
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.status === 'success') {
                // Guardar token y datos de usuario
                localStorage.setItem('auth_token', respuesta.token);
                localStorage.setItem('user_id', respuesta.user_id);
                localStorage.setItem('user_name', respuesta.user_name);
                localStorage.setItem('user_type', respuesta.user_type);
                
                // Redirigir según el tipo de usuario
                if (respuesta.user_type === 'administrador') {
                    window.location.href = 'dashboard-admin.html';
                } else {
                    window.location.href = 'catalogo-cliente.html';
                }
            } else {
                mostrarMensaje('error', respuesta.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al iniciar sesión:', error);
            mostrarMensaje('error', 'Error al conectar con el servidor');
        }
    });
}

function registrarse() {
    const nombre = $('#register-nombre').val();
    const email = $('#register-email').val();
    const tipo = $('#register-tipo').val();
    const password = $('#register-password').val();
    const confirmPassword = $('#register-confirm-password').val();
    
    // Validaciones
    if (!nombre || !email || !tipo || !password || !confirmPassword) {
        mostrarMensaje('error', 'Todos los campos son requeridos');
        return;
    }
    
    if (password !== confirmPassword) {
        mostrarMensaje('error', 'Las contraseñas no coinciden');
        return;
    }
    
    if (password.length < 6) {
        mostrarMensaje('error', 'La contraseña debe tener al menos 6 caracteres');
        return;
    }
    
    $.ajax({
        url: '../backend/api/register.php',
        type: 'POST',
        data: {
            nombre: nombre,
            email: email,
            tipo: tipo,
            password: password
        },
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.status === 'success') {
                mostrarMensaje('success', respuesta.message);
                // Cambiar a pestaña de login
                $('#login-tab').tab('show');
                // Limpiar formulario
                $('#register-form')[0].reset();
            } else {
                mostrarMensaje('error', respuesta.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al registrarse:', error);
            mostrarMensaje('error', 'Error al conectar con el servidor');
        }
    });
}

function mostrarMensaje(tipo, mensaje) {
    const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
    $('#auth-result').html(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
}