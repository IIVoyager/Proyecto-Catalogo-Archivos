let archivoEditando = null;
let timeoutBusqueda = null;

$(document).ready(function() {
    // Verificar autenticación
    verificarAutenticacion();
    
    // Cargar datos iniciales
    cargarArchivos();
    cargarBitacoras();
    cargarEstadisticas();
    
    // Configurar navegación
    $('#nav-archivos').on('click', function(e) {
        e.preventDefault();
        mostrarSeccion('archivos');
    });
    
    $('#nav-estadisticas').on('click', function(e) {
        e.preventDefault();
        mostrarSeccion('estadisticas');
    });
    
    $('#nav-bitacoras').on('click', function(e) {
        e.preventDefault();
        mostrarSeccion('bitacoras');
    });
    
    // Manejar formulario de archivo
    $('#archivo-form').on('submit', function(e) {
        e.preventDefault();
        if (archivoEditando) {
            guardarArchivoEditado();
        } else {
            agregarArchivo();
        }
    });
    
    // Manejar búsqueda
    // En el evento keyup del campo de búsqueda
    $('#search').on('keyup', function(e) {
        clearTimeout(timeoutBusqueda);
        // Si se presiona Enter, buscar inmediatamente
        if (e.key === 'Enter') {
            buscarArchivos();
        } else {
            // Para búsqueda en tiempo real, esperar 300ms después de la última tecla
            timeoutBusqueda = setTimeout(buscarArchivos, 300);
        }
    });
    
    // Manejar cancelar edición
    $('#cancelar-edicion').on('click', cancelarEdicion);
    
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
    const userType = localStorage.getItem('user_type');
    
    if (!token || userType !== 'administrador') {
        window.location.href = 'index.html';
    }
}

function mostrarSeccion(seccion) {
    // Ocultar todas las secciones
    $('#seccion-archivos, #seccion-estadisticas, #seccion-bitacoras').addClass('d-none');
    
    // Remover clase active de todas las pestañas
    $('#nav-archivos, #nav-estadisticas, #nav-bitacoras').removeClass('active');
    
    // Mostrar la sección seleccionada y activar su pestaña
    $(`#seccion-${seccion}`).removeClass('d-none');
    $(`#nav-${seccion}`).addClass('active');
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
            mostrarToast('error', 'Error al cargar archivos');
        }
    });
}

function mostrarArchivos(archivos) {
    let template = '';
    
    if (archivos.length > 0) {
        archivos.forEach(archivo => {
            const icono = obtenerIconoArchivo(archivo.extension);
            
            template += `
                <tr>
                    <td>${archivo.id}</td>
                    <td>${archivo.nombre}</td>
                    <td>${archivo.autor || 'N/A'}</td>
                    <td>${archivo.departamento || 'N/A'}</td>
                    <td>
                        <i class="${icono} me-2"></i>
                        ${archivo.archivo_nombre}
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm me-1 archivo-editar" data-id="${archivo.id}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-danger btn-sm me-1 archivo-eliminar" data-id="${archivo.id}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                        <button class="btn btn-info btn-sm archivo-descargar" data-id="${archivo.id}">
                            <i class="fas fa-download"></i> Descargar
                        </button>
                    </td>
                </tr>
            `;
        });
    } else {
        template = '<tr><td colspan="6" class="text-center">No hay archivos registrados</td></tr>';
    }
    
    $('#archivos-list').html(template);
    
    // Asignar eventos a los botones
    $('.archivo-editar').on('click', function() {
        const id = $(this).data('id');
        editarArchivo(id);
    });
    
    $('.archivo-eliminar').on('click', function() {
        const id = $(this).data('id');
        eliminarArchivo(id);
    });
    
    $('.archivo-descargar').on('click', function() {
        const id = $(this).data('id');
        descargarArchivo(id);
    });
}

function obtenerIconoArchivo(extension) {
    switch (extension.toLowerCase()) {
        case 'pdf':
            return 'fas fa-file-pdf file-pdf';
        case 'doc':
        case 'docx':
            return 'fas fa-file-word file-doc';
        case 'xls':
        case 'xlsx':
            return 'fas fa-file-excel file-xls';
        case 'ppt':
        case 'pptx':
            return 'fas fa-file-powerpoint file-ppt';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'fas fa-file-image file-img';
        case 'zip':
        case 'rar':
            return 'fas fa-file-archive file-zip';
        case 'txt':
            return 'fas fa-file-alt file-txt';
        default:
            return 'fas fa-file file-default';
    }
}

function agregarArchivo() {
    const formData = new FormData($('#archivo-form')[0]);

    $.ajax({
        url: '../backend/api/archivo-add.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.status === 'success') {
                mostrarToast('success', respuesta.message);
                limpiarFormulario();
                cargarArchivos();
            } else {
                mostrarToast('error', respuesta.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al agregar archivo:', error);
            mostrarToast('error', 'Error al agregar archivo');
        }
    });
}

function editarArchivo(id) {
    // Obtener los datos del archivo para llenar el formulario
    $.ajax({
        url: '../backend/api/archivo-list.php',
        type: 'GET',
        dataType: 'json',
        success: function(archivos) {
            const archivo = archivos.find(a => a.id == id);
            if (archivo) {
                // Llenar el formulario con los datos del archivo
                $('#archivo-id').val(archivo.id);
                $('#nombre').val(archivo.nombre);
                $('#autor').val(archivo.autor);
                $('#departamento').val(archivo.departamento);
                $('#empresa').val(archivo.empresa_institucion);
                $('#fecha_creacion').val(archivo.fecha_creacion);
                $('#descripcion').val(archivo.descripcion);
                // Nota: No podemos pre-cargar el archivo, pero podríamos mostrar el nombre actual
                $('#archivo').removeAttr('required'); // En edición, el archivo no es obligatorio

                // Cambiar el texto del botón
                $('#btn-submit').text('Actualizar Archivo');
                $('#cancelar-edicion').removeClass('d-none');

                archivoEditando = id;
                mostrarToast('info', 'Editando archivo: ' + archivo.nombre);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar archivo:', error);
            mostrarToast('error', 'Error al cargar archivo');
        }
    });
}

function guardarArchivoEditado() {
    const formData = new FormData($('#archivo-form')[0]);
    formData.append('id', archivoEditando);

    $.ajax({
        url: '../backend/api/archivo-edit.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.status === 'success') {
                mostrarToast('success', respuesta.message);
                cancelarEdicion();
                cargarArchivos();
            } else {
                mostrarToast('error', respuesta.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar archivo:', error);
            mostrarToast('error', 'Error al actualizar archivo');
        }
    });
}

function cancelarEdicion() {
    archivoEditando = null;
    limpiarFormulario();
    $('#btn-submit').text('Agregar Archivo');
    $('#cancelar-edicion').addClass('d-none');
    $('#archivo').attr('required', true);
    mostrarToast('info', 'Edición cancelada');
}

function limpiarFormulario() {
    $('#archivo-form')[0].reset();
    $('#archivo-id').val('');
}

function eliminarArchivo(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este archivo?')) {
        $.ajax({
            url: '../backend/api/archivo-delete.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(respuesta) {
                if (respuesta.status === 'success') {
                    mostrarToast('success', respuesta.message);
                    cargarArchivos();
                } else {
                    mostrarToast('error', respuesta.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al eliminar archivo:', error);
                mostrarToast('error', 'Error al eliminar archivo');
            }
        });
    }
}

function descargarArchivo(id) {
    // Abrir en una nueva pestaña para descargar
    window.open('../backend/api/descargar.php?id=' + id, '_blank');
}

function cargarBitacoras() {
    // Cargar bitácora de accesos
    $.ajax({
        url: '../backend/api/bitacora-acceso.php',
        type: 'GET',
        dataType: 'json',
        success: function(accesos) {
            let template = '';
            accesos.forEach(acceso => {
                template += `
                    <tr>
                        <td>${acceso.usuario_nombre} (${acceso.usuario_tipo})</td>
                        <td>${acceso.fecha_acceso}</td>
                        <td>${acceso.ip}</td>
                    </tr>
                `;
            });
            $('#bitacora-accesos').html(template);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar bitácora de accesos:', error);
        }
    });

    // Cargar bitácora de descargas
    $.ajax({
        url: '../backend/api/bitacora-descargas.php',
        type: 'GET',
        dataType: 'json',
        success: function(descargas) {
            let template = '';
            descargas.forEach(descarga => {
                template += `
                    <tr>
                        <td>${descarga.usuario_nombre} (${descarga.usuario_tipo})</td>
                        <td>${descarga.archivo_nombre}</td>
                        <td>${descarga.fecha_descarga}</td>
                        <td>${descarga.ip}</td>
                    </tr>
                `;
            });
            $('#bitacora-descargas').html(template);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar bitácora de descargas:', error);
        }
    });
}

function cargarEstadisticas() {
    $.ajax({
        url: '../backend/api/estadisticas.php',
        type: 'GET',
        dataType: 'json',
        success: function(estadisticas) {
            generarGraficas(estadisticas);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar estadísticas:', error);
        }
    });
}

function generarGraficas(estadisticas) {
    // Gráfica de tipos de archivo
    if (estadisticas.tipos_archivo && estadisticas.tipos_archivo.length > 0) {
        const ctxTipos = document.getElementById('chart-tipos-archivo').getContext('2d');
        const labelsTipos = estadisticas.tipos_archivo.map(item => item.extension.toUpperCase());
        const dataTipos = estadisticas.tipos_archivo.map(item => item.total);
        
        new Chart(ctxTipos, {
            type: 'pie',
            data: {
                labels: labelsTipos,
                datasets: [{
                    data: dataTipos,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#8AC926', '#1982C4'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Descargas por Tipo de Archivo'
                    }
                }
            }
        });
    }

    // Gráfica de días de la semana
    if (estadisticas.dias_semana && estadisticas.dias_semana.length > 0) {
        const ctxDias = document.getElementById('chart-dias-semana').getContext('2d');
        const labelsDias = estadisticas.dias_semana.map(item => traducirDia(item.dia));
        const dataDias = estadisticas.dias_semana.map(item => item.total);
        
        new Chart(ctxDias, {
            type: 'bar',
            data: {
                labels: labelsDias,
                datasets: [{
                    label: 'Descargas',
                    data: dataDias,
                    backgroundColor: '#3498db'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Descargas por Día de la Semana'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Gráfica de horas del día
    if (estadisticas.horas_dia && estadisticas.horas_dia.length > 0) {
        const ctxHoras = document.getElementById('chart-horas-dia').getContext('2d');
        const labelsHoras = estadisticas.horas_dia.map(item => `${item.hora}:00`);
        const dataHoras = estadisticas.horas_dia.map(item => item.total);
        
        new Chart(ctxHoras, {
            type: 'line',
            data: {
                labels: labelsHoras,
                datasets: [{
                    label: 'Descargas',
                    data: dataHoras,
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Descargas por Hora del Día'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

function traducirDia(diaIngles) {
    const dias = {
        'Monday': 'Lunes',
        'Tuesday': 'Martes',
        'Wednesday': 'Miércoles',
        'Thursday': 'Jueves',
        'Friday': 'Viernes',
        'Saturday': 'Sábado',
        'Sunday': 'Domingo'
    };
    return dias[diaIngles] || diaIngles;
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
                mostrarToast('success', respuesta.message);
            } else {
                mostrarToast('error', respuesta.message);
                // Mostrar archivos vacíos si hay error
                mostrarArchivos([]);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en búsqueda:', error, xhr.responseText);
            mostrarToast('error', 'Error en la búsqueda: ' + xhr.statusText);
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

function mostrarToast(tipo, mensaje) {
    const toastEl = document.getElementById('liveToast');
    const toastTitle = document.getElementById('toast-title');
    const toastMessage = document.getElementById('toast-message');
    
    // Configurar colores según el tipo
    if (tipo === 'success') {
        toastTitle.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Éxito';
    } else if (tipo === 'error') {
        toastTitle.innerHTML = '<i class="fas fa-exclamation-circle text-danger me-2"></i>Error';
    } else {
        toastTitle.innerHTML = '<i class="fas fa-info-circle text-info me-2"></i>Información';
    }
    
    toastMessage.textContent = mensaje;
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}