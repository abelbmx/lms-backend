<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="container-fluid h-100 overflow-auto px-2">

    <!-- Título & Breadcrumb -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h5 class="fs-5 fw-semibold m-0"> <?php echo $pagetitle; ?></h5>
        </div>

        <div class="text-end">
            <?php echo $breadcrumb; ?>
        </div>
    </div>

    <section class="row">
        <div class="col">
            <div class="card d-block">
                <h5 class="card-header">Pedidos Recientes y Documentación Asociada</h5>
                <div class="card-body">

                    <!-- Mostrar mensajes de éxito o error -->
                    <?php if ($this->session->flashdata('message')): ?>
                        <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
                    <?php endif; ?>

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="archivosTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="unlinked-tab" data-bs-toggle="tab" data-bs-target="#unlinked" type="button" role="tab" aria-controls="unlinked" aria-selected="true">Archivos sin Enlazar</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="linked-tab" data-bs-toggle="tab" data-bs-target="#linked" type="button" role="tab" aria-controls="linked" aria-selected="false">Archivos Enlazados</button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content" id="archivosTabsContent">
                        <!-- Unlinked Files Tab -->
                        <div class="tab-pane fade show active" id="unlinked" role="tabpanel" aria-labelledby="unlinked-tab">
                            <div class="mt-3">
                                <!-- Tabla con archivos sin enlazar -->
                                <table id="archivosTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Archivo</th>
                                            <th>Fecha Subida</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($archivos as $archivo): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($archivo->file_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($archivo->uploaded_at)); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group" aria-label="Acciones">
                                                        <button class="btn btn-primary me-2" data-id="<?php echo $archivo->id; ?>" data-toggle="modal" data-target="#enlazarModal" onclick="openEnlazarModal(<?php echo $archivo->id; ?>)">
                                                            <span class="mdi mdi-link-variant-plus"></span> Enlazar
                                                        </button>
                                                        <button class="btn btn-info" onclick="previewDocument('<?php echo base_url($archivo->file_path); ?>', '<?php echo htmlspecialchars($archivo->file_name, ENT_QUOTES, 'UTF-8'); ?>')">
                                                            <span class="mdi mdi-eye-outline"></span> Ver Documento
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Linked Files Tab -->
                        <div class="tab-pane fade" id="linked" role="tabpanel" aria-labelledby="linked-tab">
                            <div class="mt-3">
                                <!-- Tabla con archivos enlazados -->
                                <table id="archivosEnlazadosTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Archivo</th>
                                            <th>Fecha Subida</th>
                                            <th>Fecha Enlazado</th>
                                            <th>Tiempo de Enlace</th>
                                            <th>Orden PO</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($archivos_enlazados as $archivo): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($archivo->file_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo date('d-m-Y H:i', strtotime($archivo->uploaded_at)); ?></td>
                                                <td><?php echo date('d-m-Y H:i', strtotime($archivo->enlazado_at)); ?></td>
                                                <td>
                                                    <?php
                                                    $uploaded_at = new DateTime($archivo->uploaded_at);
                                                    $enlazado_at = new DateTime($archivo->enlazado_at);
                                                    $interval = $uploaded_at->diff($enlazado_at);
                                                    echo $interval->days . ' días, ' . $interval->h . ' horas, ' . $interval->i . ' minutos';
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($archivo->PO, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group" aria-label="Acciones">
                                                        <button class="btn btn-info" onclick="previewDocument('<?php echo base_url($archivo->file_path); ?>', '<?php echo htmlspecialchars($archivo->file_name, ENT_QUOTES, 'UTF-8'); ?>')">
                                                            <span class="mdi mdi-eye-outline"></span> Ver Documento
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Enlazar Archivos -->
<div class="modal fade" id="enlazarModal" tabindex="-1" aria-labelledby="enlazarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enlazarModalLabel">Enlazar Archivo con Orden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalContent"></div> <!-- Aquí cargaremos la vista parcial con AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para previsualizar documento -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Ver Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="documentPreviewContent"></div> <!-- Aquí se carga el documento -->
            </div>
        </div>
    </div>
</div>

<!-- Scripts para DataTables y funcionalidad de las modales -->
<script type="text/javascript">
    $(document).ready(function() {
        // Inicializar DataTables para archivos sin enlazar
        $('#archivosTable').DataTable({
            "order": [
                [1, 'desc']
            ], // Ordenar por la segunda columna (fecha) de manera descendente
            "columnDefs": [{
                "targets": 1, // Aplicar la ordenación por fecha a la columna de fecha
                "render": function(data, type, row) {
                    if (type === 'display' || type === 'filter') {
                        // Formatear la fecha para mostrarla como día-mes-año
                        return data.split('-').reverse().join('-');
                    }
                    return data;
                },
                "type": "date"
            }],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<span class="mdi mdi-file-excel"></span> Exportar Excel',
                    className: 'btn btn-success'
                },
                {
                    extend: 'csvHtml5',
                    text: ' <span class="mdi mdi-file-delimited"></span> Exportar CSV',
                    className: 'btn btn-info'
                }
            ]
        });

        // Inicializar DataTables para archivos enlazados
        $('#archivosEnlazadosTable').DataTable({
            "order": [
                [2, 'desc']
            ], // Ordenar por la tercera columna (fecha enlazado) de manera descendente
            "columnDefs": [{
                "targets": [1, 2], // Aplicar la ordenación por fecha a las columnas de fecha
                "render": function(data, type, row) {
                    if (type === 'display' || type === 'filter') {
                        // Formatear la fecha para mostrarla como día-mes-año y hora
                        return data.split('-').reverse().join('-');
                    }
                    return data;
                },
                "type": "date"
            }],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<span class="mdi mdi-file-excel"></span> Exportar Excel',
                    className: 'btn btn-success'
                },
                {
                    extend: 'csvHtml5',
                    text: ' <span class="mdi mdi-file-delimited"></span> Exportar CSV',
                    className: 'btn btn-info'
                }
            ]
        });
    });

    function openEnlazarModal(file_id) {
        const url = '<?php echo site_url('admin/revisar_archivos/enlazar'); ?>/' + file_id;
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                $('#modalContent').html(response);
                $('#enlazarModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.log('Error:', error);
            }
        });
    }

    function previewDocument(filePath, fileName) {
        $('#previewModalLabel').text('Viendo: ' + fileName);
        let fileExtension = fileName.split('.').pop().toLowerCase();
        let previewContent = '';

        if (fileExtension === 'pdf') {
            previewContent = `<embed src="${filePath}" type="application/pdf" width="100%" height="500px" />`;
        } else if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
            previewContent = `<img src="${filePath}" alt="${fileName}" class="img-fluid" style="max-width: 100%; height: auto;" />`;
        } else {
            previewContent = '<p>Este tipo de archivo no se puede previsualizar.</p>';
        }

        $('#documentPreviewContent').html(previewContent);
        $('#previewModal').modal('show');
    }
</script>
