<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="container-fluid h-100 overflow-auto px-2 position-relative">

    <!-- Overlay de carga full‑screen -->
    <div id="dtOverlay" class="d-none position-fixed top-0 start-0 vw-100 vh-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center" style="z-index: 1050;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- estilos para 3er tab vertical y botones extra -->
    <style>
        /* En el 3er tab (índice 3) forzar lista vertical */
        #detalleModal .tab-content .tab-pane:nth-of-type(3) .list-group {
            display: flex !important;
            flex-direction: column !important;
        }

        #detalleModal .tab-content .tab-pane:nth-of-type(3) .list-group-item {
            margin-bottom: .5rem;
        }
    </style>

    <!-- titulo & breadcrumb -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h5 class="fs-5 fw-semibold m-0"><?php echo $pagetitle; ?></h5>
        </div>
        <div class="text-end">
            <?php echo $breadcrumb; ?>
        </div>
    </div>
    <!-- Tabla de pedidos -->
    <div class="row">
        <div class="col">
            <div class="card d-block">
                <h5 class="card-header">Pedidos Recientes y Documentación Asociada</h5>
                <div class="card-body">
                    <table id="tablaPedidos" class="table table-striped nowrap w-100">
                        <thead>
                            <tr>
                                <th>Acciones</th>
                                <th>Estado</th>
                                <th>PO</th>
                                <th>Orden</th>
                                <th>Tp</th>
                                <th>Fecha OC</th>
                                <th>Cliente</th>
                                <th>Nombre</th>
                                <th>Guía</th>
                                <th>Factura</th>
                                <th>Fecha Factura</th>
                                <th>Ship</th>
                                <th>Nombre2</th>
                                <th>Dirección</th>
                                <th>Comuna</th>
                                <th>Región</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Modal para Ver Detalle -->
    <div class="modal fade bs-example-modal-center" id="detalleModal" tabindex="-1" role="dialog" aria-labelledby="detalleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-gray" style="height: 870px;">
                <form id="formDetalle" class="d-flex flex-column h-100">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="detalleModalLabel">Detalle del Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body py-2 overflow-auto">
                        <!-- Contenido del detalle se carga dinámicamente -->
                    </div>
                    <div class="modal-footer mt-auto">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <!-- Modal para mostrar imágenes en grande -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down modal-lg">
            <div class="modal-content" style="height: 80dvh;">
                <div class="modal-body p-0 border-2 border-white">
                    <div id="panzoom-container" class="d-flex justify-content-end align-items-center">
                        <img src="" id="imageModalSrc" class="img-fluid zoomable" alt="Documento" style="cursor: grab; user-select: none;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Enlazar Documentos -->
    <div class="modal fade" id="enlazarDocumentoModal" tabindex="-1" aria-labelledby="enlazarDocumentoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enlazarDocumentoModalLabel">Buscar y Enlazar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="searchTerm" class="form-label">Buscar Documentos</label>
                        <input type="text" class="form-control" id="searchTerm" placeholder="Ingrese el nombre del documento">
                    </div>
                    <button type="button" class="btn btn-primary mb-3" id="btnBuscarDocumentos">Buscar</button>
                    <table id="tablaBusquedaDocumentos" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nombre del Archivo</th>
                                <th>Fecha Subida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody><!-- Resultados se cargarán dinámicamente --></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div> <!-- End container-fluid -->

<script>
    $(document).ready(function() {
        // Mostrar overlay en cualquier petición AJAX
        $(document).ajaxStart(function() {
            $('#dtOverlay').removeClass('d-none');
        }).ajaxStop(function() {
            $('#dtOverlay').addClass('d-none');
        });

        // Inicializar DataTables con idioma en español y botón de exportar a Excel
        var table = $('#tablaPedidos').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('admin/pedidos/ajax_list') ?>',
                type: 'POST'
            },
            columns: [{
                    data: 'acciones',
                    orderable: false
                },
                {
                    data: 'Estado'
                },
                {
                    data: 'PO'
                },
                {
                    data: 'Orden'
                },
                {
                    data: 'Tp'
                },
                {
                    data: 'FechaOC'
                },
                {
                    data: 'Cliente'
                },
                {
                    data: 'Nombre'
                },
                {
                    data: 'Guia'
                },
                {
                    data: 'Factura'
                },
                {
                    data: 'FechaF'
                },
                {
                    data: 'Ship'
                },
                {
                    data: 'Nombre2'
                },
                {
                    data: 'Direccion'
                },
                {
                    data: 'Comuna'
                },
                {
                    data: 'Region'
                }
            ],
            language: {
                url: "https://cdn.datatables.net/plug‑ins/1.13.4/i18n/es-ES.json"
            },
            pageLength: 10,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                className: 'btn btn-success',
                text: 'Exportar a Excel'
            }]
        });

        // Overlay específico de DataTables
        table.on('processing.dt', function(e, settings, processing) {
            if (processing) {
                $('#dtOverlay').removeClass('d-none');
            } else {
                $('#dtOverlay').addClass('d-none');
            }
        });

        // Ajuste para que los botones de exportación se vean correctamente en pantallas pequeñas
        table.buttons().container()
            .appendTo('#tablaPedidos_wrapper .col-md-6:eq(0)');

        // Función genérica para mostrar PDF o imagen
        function showDocument(src) {
            var container = $('#panzoom-container').empty();
            if (src.toLowerCase().endsWith('.pdf')) {
                container.append(`
                    <object 
                        data="${src}#toolbar=0&navpanes=0" 
                        type="application/pdf" 
                        width="100%" 
                        height="100%">
                      <p>Tu navegador no soporta PDFs embebidos. 
                         <a href="${src}" target="_blank" class="btn btn-sm btn-outline-primary">Abrir en nueva pestaña</a>
                      </p>
                    </object>
                `);
            } else {
                container.append(`<img src="${src}" class="img-fluid zoomable" style="cursor: grab; user-select: none;">`);
            }
            $('#imageModal').modal('show');
        }

        // Inicializar/reiniciar tabla de búsqueda de documentos
        function initDocumentSearchTable(query) {
            if ($.fn.DataTable.isDataTable('#tablaBusquedaDocumentos')) {
                $('#tablaBusquedaDocumentos').DataTable().destroy();
            }
            $('#tablaBusquedaDocumentos').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '<?php echo site_url('admin/dashboard/buscar_documentos'); ?>',
                    type: 'GET',
                    data: {
                        query
                    },
                    dataSrc: 'data'
                },
                columns: [{
                        data: 'file_name',
                        render: function(data) {
                            var src = '<?php echo base_url('uploads/'); ?>' + data;
                            if (data.toLowerCase().endsWith('.pdf')) {
                                return `<a href="#" class="ver-documento" data-src="${src}">${data}</a>`;
                            }
                            return `<img src="${src}" class="img-thumbnail preview-documento" width="50">`;
                        }
                    },
                    {
                        data: 'uploaded_at'
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(id) {
                            return `<button class="btn btn-success btn-sm enlazar-documento" data-file-id="${id}">Enlazar</button>`;
                        }
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug‑ins/1.13.5/i18n/es-ES.json"
                }
            });
        }

        // Clic en "Ver Detalle"
        $(document).on('click', '.ver-detalle', function() {
            var pedidoId = $(this).data('id');
            $.ajax({
                url: '<?= site_url('admin/revisar_archivos/get_pedido_detalle') ?>/' + pedidoId,
                method: 'GET',
                success: function(response) {
                    var $body = $('#detalleModal .modal-body').html(response);
                    // Forzar pestaña 1 activa
                    $body.find('.nav-pills a').removeClass('active');
                    $body.find('.tab-pane').removeClass('active show');
                    $body.find('.nav-pills a').first().addClass('active');
                    $body.find('.tab-pane').first().addClass('active show');
                    // Mostrar nombre y PDF embebido
                    $body.find('img[src], object[data]').each(function() {
                        var src = $(this).attr('src') || $(this).attr('data'),
                            fileName = src.split('/').pop();
                        $(this).before('<p class="fw-bold mb-1">' + fileName + '</p>');
                    });
                    $body.find('img[src$=".pdf"], object[data$=".pdf"]').each(function() {
                        var src = $(this).attr('src') || $(this).attr('data'),
                            fileName = src.split('/').pop();
                        $(this).replaceWith(`
                            <p class="fw-bold mb-1">${fileName}</p>
                            <object data="${src}#toolbar=0&navpanes=0"
                                    type="application/pdf"
                                    width="100%"
                                    height="600px">
                              <p>Tu navegador no soporta PDFs embebidos.
                                 <a href="${src}" target="_blank" class="btn btn-sm btn-outline-primary">Abrir en nueva pestaña</a>
                              </p>
                            </object>
                        `);
                    });
                    $body.find('a[download$=".pdf"]').each(function() {
                        var href = $(this).attr('href');
                        $(this).removeAttr('download')
                            .attr('target', '_blank')
                            .text('Abrir en nueva pestaña');
                    });
                    // Botón extra de enlazar
                    if (!$body.find('#btnBuscarYEnlazarDocumentos').length) {
                        $body.append('<button type="button" class="btn btn-secondary" id="btnBuscarYEnlazarDocumentos">Buscar y Enlazar Documentos</button>');
                    }
                    $('#detalleModal').modal('show');
                }
            });
        });

        // Unificar búsqueda y mostrar modal de enlace
        $(document).on('click', '#btnBuscarDocumentos, #btnBuscarYEnlazarDocumentos', function() {
            initDocumentSearchTable($('#searchTerm').val().trim());
            $('#enlazarDocumentoModal').modal('show');
        });

        // Clic en nombre de documento
        $(document).on('click', '.ver-documento', function(e) {
            e.preventDefault();
            showDocument($(this).data('src'));
        });

        // Guardar cambios en detalle
        $('#formDetalle').on('submit', function(e) {
            e.preventDefault();
            Swal.fire('Éxito', 'Datos guardados correctamente.', 'success');
            $('#detalleModal').modal('hide');
        });

        // Enlazar documento al pedido
        $(document).on('click', '.enlazar-documento', function() {
            var file_id = $(this).data('file-id'),
                pedidoId = $('#detalleModal').find('#pedidoId').val() || pedidoId;
            if (!pedidoId) {
                return Swal.fire('Error', 'No se pudo identificar el pedido.', 'error');
            }
            $.post('<?php echo site_url('admin/dashboard/enlazar_documento'); ?>', {
                    file_id: file_id,
                    order_id: pedidoId
                }, null, 'json')
                .done(function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Éxito', response.message, 'success');
                        $('.ver-detalle[data-id="' + pedidoId + '"]').click();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Ocurrió un error al enlazar el documento.', 'error');
                });
        });

    });
</script>
