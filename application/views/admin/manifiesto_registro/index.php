<!-- application/views/admin/manifiesto_registro/index.php -->
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<!-- CSS Adicional para la Tabla de Órdenes Seleccionadas -->
<style>
    #selectedOrdenesTable th,
    #selectedOrdenesTable td {
        text-align: center;
        vertical-align: middle;
    }
</style>

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
                <h5 class="card-header">Registrar Nuevo Manifiesto</h5>
                <div class="card-body">
                    <!-- alert para confirmación de entrtegado -->
                    <div id="alertContainer"></div>
                    <!-- Tabla de Manifiestos -->
                    <form id="formCrearManifiesto">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" id="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="conductor" class="form-label">Conductor</label>
                                <input type="text" name="conductor" class="form-control" id="conductor" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="camion" class="form-label">Camión</label>
                                <input type="text" name="camion" class="form-control" id="camion" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="number" name="telefono" class="form-control" id="telefono" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_estimada" class="form-label">Fecha Estimada</label>
                                <input type="date" name="fecha_estimada" class="form-control" id="fecha_estimada" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select name="estado" class="form-select" id="estado" required>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center border-bottom pb-4 mb-3">
                            <label class="form-label mb-0">Seleccionar Órdenes</label>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#seleccionarOrdenesModal">
                                <i class="bi bi-plus-circle"></i> Seleccionar Órdenes
                            </button>
                            <small class="form-text text-muted">Selecciona las órdenes que deseas enlazar.</small>
                        </div>

                        <!-- Tabla de Órdenes Seleccionadas -->
                        <div class="mb-3">
                            <label class="form-label h5">Órdenes Seleccionadas</label>
                            <table id="selectedOrdenesTable" class="table table-striped nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID Orden</th>
                                        <th>PO</th>
                                        <th>Cliente</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Órdenes seleccionadas se agregarán aquí -->
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary">Registrar Manifiesto</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Seleccionar Órdenes -->
<div class="modal fade" id="seleccionarOrdenesModal" tabindex="-1" aria-labelledby="seleccionarOrdenesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- Tamaño extra grande para mejor visibilidad -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Órdenes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table id="ordenesDisponiblesTable" class="table table-striped nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllOrdenesDisponibles"></th>
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
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="agregarOrdenesSeleccionadas">Agregar Órdenes Seleccionadas</button>
            </div>
        </div>
    </div>
</div>


<script>
    var ordenesDisponiblesTable;
    var seleccionarOrdenesModal;

    $(document).ready(function() {
        // Inicializar el modal con Bootstrap 5
        var seleccionarOrdenesModalEl = document.getElementById('seleccionarOrdenesModal');
        seleccionarOrdenesModal = new bootstrap.Modal(seleccionarOrdenesModalEl);

        // Inicializar DataTable para órdenes disponibles en el modal
        ordenesDisponiblesTable = $('#ordenesDisponiblesTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": '<?php echo site_url("admin/manifiestos_registro/get_available_orders"); ?>',
                "type": "POST"
            },
            "columns": [{
                    "data": null,
                    "orderable": false,
                    "render": function(data, type, row) {
                        return `<input type="checkbox" class="orden-checkbox" value="${row.id}">`;
                    }
                },
                {
                    "data": "id"
                },
                {
                    "data": "PO"
                },
                {
                    "data": "FechaOC"
                },
                {
                    "data": "Cliente"
                },
                {
                    "data": "Guia"
                },
                {
                    "data": "Factura"
                }
            ],
            "order": [
                [1, 'asc']
            ],
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": 10
        });

        // Inicializar DataTable para órdenes seleccionadas
        var selectedOrdenesTable = $('#selectedOrdenesTable').DataTable({
            "paging": false,
            "info": false,
            "searching": false,
            "ordering": false,
            "data": [],
            "columns": [{
                    "data": "id"
                },
                {
                    "data": "PO"
                },
                {
                    "data": "Cliente"
                },
                {
                    data: null,
                    orderable: false,
                    defaultContent: '<button class="btn btn-sm btn-danger eliminarOrden"><i class="mdi mdi-trash-can-outline"></i></button>'
                }
            ]
        });

        // Seleccionar/Deseleccionar todas las órdenes disponibles
        $('#selectAllOrdenesDisponibles').on('click', function() {
            var isChecked = $(this).is(':checked');
            $('#ordenesDisponiblesTable tbody .orden-checkbox').prop('checked', isChecked);
        });

        // Al hacer clic en "Agregar Órdenes Seleccionadas"
        $('#agregarOrdenesSeleccionadas').on('click', function() {
            // Obtener las órdenes seleccionadas
            var selectedOrders = [];
            $('#ordenesDisponiblesTable tbody input[type="checkbox"]:checked').each(function() {
                var data = ordenesDisponiblesTable.row($(this).closest('tr')).data();
                if (data) {
                    selectedOrders.push({
                        id: data.id,
                        PO: data.PO,
                        Cliente: data.Cliente
                    });
                }
            });

            // Agregar las órdenes seleccionadas a la tabla de órdenes seleccionadas
            selectedOrders.forEach(function(order) {
                // Verificar si la orden ya está en la tabla seleccionada
                var exists = false;
                selectedOrdenesTable.rows().every(function() {
                    var data = this.data();
                    if (data.id == order.id) {
                        exists = true;
                    }
                });
                if (!exists) {
                    selectedOrdenesTable.row.add(order).draw();
                    // Añadir un input oculto al formulario
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'ordenes[]',
                        value: order.id,
                        class: 'inputOrden'
                    }).appendTo('#formCrearManifiesto');
                }
            });

            // Desmarcar todos los checkboxes y deseleccionar "Select All"
            $('#selectAllOrdenesDisponibles').prop('checked', false);
            $('#ordenesDisponiblesTable tbody .orden-checkbox').prop('checked', false);

            // Cerrar el modal usando la instancia de Bootstrap
            seleccionarOrdenesModal.hide();

            // Remover cualquier backdrop restante
            setTimeout(function() {
                $('.modal-backdrop').remove();
            }, 500);
        });

        // Eliminar una orden de la tabla de órdenes seleccionadas
        $('#selectedOrdenesTable tbody').on('click', '.eliminarOrden', function() {
            var row = selectedOrdenesTable.row($(this).parents('tr'));
            var data = row.data();

            // Eliminar el input oculto correspondiente
            $('input.inputOrden').filter(function() {
                return $(this).val() == data.id;
            }).remove();

            // Eliminar la fila de la tabla
            row.remove().draw();
        });

        // Al enviar el formulario de crear manifiesto
        $('#formCrearManifiesto').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?php echo site_url("admin/manifiestos_registro/crear"); ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Manifiesto registrado exitosamente.');
                        $('#formCrearManifiesto')[0].reset();
                        // Limpiar la tabla de órdenes seleccionadas
                        selectedOrdenesTable.clear().draw();
                        // Eliminar todos los inputs ocultos
                        $('.inputOrden').remove();
                    } else if (response.status === 'error') {
                        if (response.errors) {
                            let errorMessages = '';
                            $.each(response.errors, function(key, value) {
                                errorMessages += value + '\n';
                            });
                            alert(errorMessages);
                        } else {
                            alert('Error al registrar el manifiesto.');
                        }
                    }
                },
                error: function() {
                    alert('Error en la solicitud.');
                }
            });
        });
    });
</script>