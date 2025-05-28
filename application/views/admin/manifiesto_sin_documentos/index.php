<!-- application/views/admin/manifiesto_sin_documentos/index.php -->
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="container-fluid px-2">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h5 class="fs-5 fw-semibold m-0"><?php echo $pagetitle; ?></h5>
        </div>
        <div class="text-end">
            <?php echo $breadcrumb; ?>
        </div>
    </div>

    <section class="row">
        <div class="col">
            <div class="card">
                <h5 class="card-header">Manifiestos Sin Documentos</h5>
                <div class="card-body">
                    <table id="manifiestosTable" class="table table-striped nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID Manifiesto</th>
                                <th>Nombre</th>
                                <th>Conductor</th>
                                <th>Camión</th>
                                <th>Teléfono</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Estimada</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Datos cargados por DataTables vía AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Enlazar Órdenes -->
<div class="modal fade" id="enlazarOrdenesModal" tabindex="-1" aria-labelledby="enlazarOrdenesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enlazar Órdenes al Manifiesto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEnlazarOrdenes">
                <div class="modal-body">
                    <input type="hidden" name="id_manifesto" id="id_manifesto">
                    <table id="ordenesTable" class="table table-striped nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllOrdenes"></th>
                                <th>ID Orden</th>
                                <th>PO</th>
                                <th>Fecha OC</th>
                                <th>Cliente</th>
                                <th>Guía</th>
                                <th>Factura</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Datos cargados por DataTables vía AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger " data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enlazar Órdenes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    var manifiestosTable, ordenesTable;

    function abrirEnlazarOrdenes(id_manifesto) {
        $('#id_manifesto').val(id_manifesto);
        $('#enlazarOrdenesModal').modal('show');

        if ($.fn.DataTable.isDataTable('#ordenesTable')) {
            ordenesTable.ajax.url('<?php echo site_url("admin/manifiestos_sin_documentos/get_ordenes"); ?>/' + id_manifesto).load();
        } else {
            ordenesTable = $('#ordenesTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": '<?php echo site_url("admin/manifiestos_sin_documentos/get_ordenes"); ?>/' + id_manifesto,
                    "type": "POST"
                },
                "columns": [{
                        "data": 0,
                        "orderable": false
                    },
                    {
                        "data": 1
                    },
                    {
                        "data": 2
                    },
                    {
                        "data": 3
                    },
                    {
                        "data": 4
                    },
                    {
                        "data": 5
                    },
                    {
                        "data": 6
                    }
                ],
                "order": [
                    [1, 'asc']
                ],
                "lengthMenu": [10, 25, 50, 100],
                "pageLength": 10
            });
        }
    }

    $(document).ready(function() {
        // Inicializar DataTable para Manifiestos Sin Documentos
        manifiestosTable = $('#manifiestosTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": '<?php echo site_url("admin/manifiestos_sin_documentos/get_manifiestos"); ?>',
                "type": "POST"
            },
            "columns": [{
                    "data": 0
                },
                {
                    "data": 1
                },
                {
                    "data": 2
                },
                {
                    "data": 3
                },
                {
                    "data": 4
                },
                {
                    "data": 5
                },
                {
                    "data": 6
                },
                {
                    "data": 7
                },
                {
                    "data": 8,
                    "orderable": false
                }
            ],
            "order": [
                [0, 'asc']
            ],
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": 10
        });

        // Seleccionar/Deseleccionar todas las órdenes
        $('#selectAllOrdenes').on('click', function() {
            var rows = ordenesTable.rows({
                'search': 'applied'
            }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        // Al enviar el formulario de enlazar órdenes
        $('#formEnlazarOrdenes').submit(function(e) {
            e.preventDefault();
            var id_manifesto = $('#id_manifesto').val();
            var ordenes = [];
            $('.orden-checkbox:checked').each(function() {
                ordenes.push($(this).val());
            });

            if (ordenes.length === 0) {
                alert('Selecciona al menos una orden.');
                return;
            }

            $.ajax({
                url: '<?php echo site_url("admin/manifiestos_sin_documentos/enlazar_ordenes"); ?>',
                type: 'POST',
                data: {
                    id_manifesto: id_manifesto,
                    ordenes: ordenes
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Órdenes enlazadas exitosamente.');
                        $('#enlazarOrdenesModal').modal('hide');
                        manifiestosTable.ajax.reload(null, false);
                    } else {
                        alert(response.message || 'Error al enlazar órdenes.');
                    }
                },
                error: function() {
                    alert('Error en la solicitud.');
                }
            });
        });
    });
</script>