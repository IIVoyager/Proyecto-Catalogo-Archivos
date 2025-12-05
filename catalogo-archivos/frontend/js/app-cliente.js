let timeoutBusqueda = null;

$(document).ready(function() {
    // Verificar autenticación
    verificarAutenticacion();
    
    // Cargar archivos
    cargarArchivos();
    
    // Manejar búsqueda
    // En el evento keyup del campo de búsqueda
    $('#search').on('keyup', function(e) {
        clearTimeout(timeoutBusqueda);
        // Si se presiona Enter, buscar inmediatamente
        if (e.key === 'Enter') {
            buscarArchivos();
        } else {
            // Para búsqueda en tiempo real, esperar 100ms después de la última tecla
            timeoutBusqueda = setTimeout(buscarArchivos, 300);
        }
    });

    // Manejar logout
    $('#logout-btn').on('click', function(e) {
        e.preventDefault();
        cerrarSesion();
    });
    
    // Mostrar nombre de usuario
    $('#user-name').text(localStorage.getItem('user_name'));
});

function verificarAutenticacion() {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
        window.location.href = 'index.html';
    }
}

function cargarArchivos() {
    $.ajax({
        url: '../backend/api/archivo-list.php',
        type: 'GET',
        dataType: 'json',
        success: function(archivos) {
            mostrarArchivos(archivos);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar archivos:', error);
            mostrarMensaje('error', 'Error al cargar archivos');
        }
    });
}

function mostrarArchivos(archivos) {
    let template = '';
    
    if (archivos && archivos.length > 0) {
        archivos.forEach(archivo => {
            // Obtener icono según la extensión
            const iconoClases = obtenerIconoArchivo(archivo.extension);
            const fecha = archivo.fecha_creacion ? new Date(archivo.fecha_creacion).toLocaleDateString('es-ES') : 'N/A';
            const nombreArchivo = archivo.archivo_nombre || 'archivo';
            
            template += `
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card archivo-card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="icono-contenedor text-center mb-3">
                                <i class="${iconoClases} archivo-icono-descarga fa-4x"
                                   data-id="${archivo.id}"
                                   title="Haz clic para descargar: ${nombreArchivo}"
                                   data-nombre="${archivo.nombre}"
                                   style="cursor: pointer; transition: transform 0.3s;">
                                </i>
                            </div>
                            
                            <div class="archivo-info">
                                <h5 class="card-title text-truncate" title="${archivo.nombre}">${archivo.nombre}</h5>
                                
                                <div class="card-text small text-muted mb-2">
                                    <div><i class="fas fa-user me-1"></i><strong>Autor:</strong> ${archivo.autor || 'No especificado'}</div>
                                    <div><i class="fas fa-building me-1"></i><strong>Departamento:</strong> ${archivo.departamento || 'No especificado'}</div>
                                    <div><i class="fas fa-briefcase me-1"></i><strong>Empresa/Institución:</strong> ${archivo.empresa_institucion || 'No especificado'}</div>
                                    <div><i class="fas fa-calendar me-1"></i><strong>Fecha:</strong> ${fecha}</div>
                                    <div><i class="fas fa-file me-1"></i><strong>Tipo:</strong> ${archivo.extension.toUpperCase()}</div>
                                </div>
                                
                                <p class="card-text archivo-descripcion" style="font-size: 0.9rem;">
                                    ${archivo.descripcion ? (archivo.descripcion.length > 100 ? archivo.descripcion.substring(0, 100) + '...' : archivo.descripcion) : 'Sin descripción'}
                                </p>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="archivo-tipo text-center small text-muted">
                                    <i class="fas fa-download me-1"></i>Click en el icono para descargar
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    } else {
        template = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay archivos disponibles</h4>
                <p class="text-muted">Intenta con otra búsqueda o vuelve más tarde</p>
            </div>`;
    }
    
    $('#archivos-container').html(template);
    
    // Asignar eventos a los iconos de descarga
    $('.archivo-icono-descarga').on('click', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        // Efecto visual al hacer clic
        $(this).css('transform', 'scale(0.95)');
        setTimeout(() => {
            $(this).css('transform', 'scale(1)');
        }, 200);
        
        mostrarMensaje('info', `Descargando: ${nombre}`);
        descargarArchivo(id);
    });
    
    // Efecto hover para los iconos
    $('.archivo-icono-descarga').hover(
        function() {
            $(this).css({
                'transform': 'scale(1.1)',
                'opacity': '0.9'
            });
        },
        function() {
            $(this).css({
                'transform': 'scale(1)',
                'opacity': '1'
            });
        }
    );
}

function descargarArchivo(id) { 
    // Abrir en una nueva pestaña para descargar
    window.open('../backend/api/descargar.php?id=' + id, '_blank');
}

function obtenerIconoArchivo(extension) {
    const extensionLower = extension.toLowerCase();
    
    // Configurar icono y color según la extensión
    let icono = '';
    let color = '';
    
    switch (extensionLower) {
        case 'pdf':
            icono = 'fa-file-pdf';
            color = 'text-danger'; // Rojo para PDF
            break;
        case 'doc':
        case 'docx':
            icono = 'fa-file-word';
            color = 'text-primary'; // Azul para Word
            break;
        case 'xls':
        case 'xlsx':
            icono = 'fa-file-excel';
            color = 'text-success'; // Verde para Excel
            break;
        case 'ppt':
        case 'pptx':
            icono = 'fa-file-powerpoint';
            color = 'text-warning'; // Naranja/Amarillo para PowerPoint
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'bmp':
        case 'svg':
            icono = 'fa-file-image';
            color = 'text-info'; // Azul claro para imágenes
            break;
        case 'zip':
        case 'rar':
        case '7z':
        case 'tar':
        case 'gz':
            icono = 'fa-file-archive';
            color = 'text-secondary'; // Gris para archivos comprimidos
            break;
        case 'txt':
        case 'csv':
        case 'log':
            icono = 'fa-file-alt';
            color = 'text-dark'; // Negro para texto
            break;
        case 'mp3':
        case 'wav':
        case 'flac':
            icono = 'fa-file-audio';
            color = 'text-purple'; // Morado para audio
            break;
        case 'mp4':
        case 'avi':
        case 'mov':
        case 'mkv':
            icono = 'fa-file-video';
            color = 'text-danger'; // Rojo para video
            break;
        default:
            icono = 'fa-file';
            color = 'text-muted'; // Gris para otros
            break;
    }
    
    return `fas ${icono} ${color}`;
}

function buscarArchivos() {
    const search = $('#search').val().trim();
    const searchBtn = $('#search-btn');
    
    // Mostrar indicador de carga
    searchBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
    
    // Si está vacío, cargar todos los archivos
    if (search.length === 0) {
        cargarArchivos();
        searchBtn.prop('disabled', false).html('Buscar');
        return;
    }
    
    $.ajax({
        url: '../backend/api/archivo-search.php',
        type: 'GET',
        data: { search: search },
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.status === 'success') {
                mostrarArchivos(respuesta.data);
            } else {
                // Mostrar archivos vacíos si hay error
                mostrarArchivos([]);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en búsqueda:', error, xhr.responseText);
            // Mostrar archivos vacíos
            mostrarArchivos([]);
        },
        complete: function() {
            // Restaurar botón
            searchBtn.prop('disabled', false).html('Buscar');
        }
    });
}

function cerrarSesion() {
    $.ajax({
        url: '../backend/api/logout.php',
        type: 'POST',
        dataType: 'json',
        success: function() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_id');
            localStorage.removeItem('user_name');
            localStorage.removeItem('user_type');
            window.location.href = 'index.html';
        },
        error: function() {
            // Limpiar localStorage incluso si hay error
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_id');
            localStorage.removeItem('user_name');
            localStorage.removeItem('user_type');
            window.location.href = 'index.html';
        }
    });
}

function mostrarMensaje(tipo, mensaje) {
    // Crear un toast simple para el cliente
    const toastHTML = `
        <div class="toast align-items-center text-bg-${tipo === 'success' ? 'success' : 'danger'} border-0 position-fixed top-0 end-0 m-3" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${mensaje}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('body').append(toastHTML);
    const toast = new bootstrap.Toast($('.toast').last()[0]);
    toast.show();
    
    // Remover el toast después de que se oculta
    $('.toast').last().on('hidden.bs.toast', function() {
        $(this).remove();
    });
}