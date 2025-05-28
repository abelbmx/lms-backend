<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Manifiestos</title>

    <style>
        #selectedOrdenesTable th,
        #selectedOrdenesTable td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="container-fluid h-100 overflow-auto px-2">
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
                <div class="card d-block">
                    <h5 class="card-header">Listado de Manifiestos</h5>
                    <div class="card-body">
                        <div class="mb-3">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearManifiestoModal">
                                <i class="bi bi-plus-lg"></i> Nuevo Manifiesto
                            </button>
                        </div>
                        <!-- alert para confirmación de entrtegado -->
                        <div id="alertContainer"></div>
                        <!-- Tabla de Manifiestos -->
                        <table id="manifiestosTable" class="table table-striped nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Conductor</th>
                                    <th>Camión</th>
                                    <th>Teléfono</th>
                                    <th>Fecha Creación</th>
                                    <th>Fecha Estimada</th>
                                    <th>Fecha Entrega</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($manifiestos as $manifiesto): ?>
                                    <tr>
                                        <td><?php echo $manifiesto['id']; ?></td>
                                        <td><?php echo htmlspecialchars($manifiesto['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($manifiesto['conductor']); ?></td>
                                        <td><?php echo htmlspecialchars($manifiesto['camion']); ?></td>
                                        <td><?php echo htmlspecialchars($manifiesto['telefono']); ?></td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($manifiesto['fecha'])); ?></td>
                                        <td><?php echo $manifiesto['fecha_estimada'] ? date('d-m-Y', strtotime($manifiesto['fecha_estimada'])) : '-'; ?></td>
                                        <td><?php echo $manifiesto['fecha_entrega'] ? date('d-m-Y H:i', strtotime($manifiesto['fecha_entrega'])) : '-'; ?></td>
                                        <td class="text-center">
                                            <span class="py-1 px-2 <?php echo ($manifiesto['estado'] == 'Activo') ? 'badge text-bg-light' : (($manifiesto['estado'] == 'Entregado') ? 'badge text-bg-dark' : 'secondary'); ?>">
                                                <?php echo htmlspecialchars($manifiesto['estado']); ?>
                                            </span>
                                        </td>
                                        <td class="d-flex w-100 gap-2">
                                            <button class="btn btn-sm btn-primary" onclick="verOrdenes(<?php echo $manifiesto['id']; ?>)">
                                                <span class="mdi mdi-list-box-outline"></span> Ver Órdenes
                                            </button>
                                            <button class="btn btn-sm btn-secondary" onclick="marcarEntregado(<?php echo $manifiesto['id']; ?>)" <?php echo ($manifiesto['estado'] == 'Entregado') ? 'disabled' : '' ?>>
                                                <span class="mdi mdi-check-circle-outline"></span> Marcar Entregado
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal para Crear Manifiesto -->
    <div class="modal fade" id="crearManifiestoModal" tabindex="-1" aria-labelledby="crearManifiestoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearManifiestoModalLabel">Nuevo Manifiesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCrearManifiesto">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" name="conductor" class="form-control" placeholder="Conductor" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="camion" class="form-control" placeholder="Camión" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="number" name="telefono" class="form-control" placeholder="Teléfono" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="date" name="fecha_estimada" class="form-control" placeholder="Fecha Estimada" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <select name="estado" class="form-select" required>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <!-- Puedes agregar más campos aquí si es necesario -->
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Órdenes Asociadas -->
    <div class="modal fade" id="verOrdenesModal" tabindex="-1" aria-labelledby="verOrdenesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verOrdenesModalLabel">Órdenes Asociadas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="verOrdenesModalContent">
                    <!-- Contenido cargado dinámicamente -->
                </div>
            </div>
        </div>
    </div>


    <!-- Modal confirmación de entregado -->
    <div class="modal fade" id="verConfirmarEntrega" tabindex="-1" aria-labelledby="verConfirmarEntregaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verConfirmarEntregaLabel">Confirmar Entrega</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de marcar como entregado?
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary confirm-btn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openEditarModal(id) {
            $.get('<?php echo site_url("admin/manifiestos/editar/"); ?>' + id, function(data) {
                $('#editarModalContent').html(data);
                $('#editarManifiestoModal').modal('show');
            });
        }

        function verOrdenes(id_manifesto) {
            $.ajax({
                url: '<?php echo site_url("admin/manifiestos/ver_ordenes/"); ?>' + id_manifesto,
                type: 'GET',
                success: function(response) {
                    $('#verOrdenesModalContent').html(response);
                    $('#verOrdenesModal').modal('show');
                },
                error: function() {
                    alert('Error al cargar las órdenes asociadas.');
                }
            });
        }


        function marcarEntregado(id) {
            // Store the ID in a data attribute of the confirm button
            $('#verConfirmarEntrega .confirm-btn').data('manifiesto-id', id);
            // Show the modal
            $('#verConfirmarEntrega').modal('show');
        }

        // Add this code for handling the confirmation in the modal
        $(document).ready(function() {
            $('#verConfirmarEntrega .confirm-btn').on('click', function() {
                const id = $(this).data('manifiesto-id');

                $.ajax({
                    url: '<?php echo site_url("admin/manifiestos/marcar_entregado/"); ?>' + id,
                    type: 'POST',
                    success: function(response) {
                        const res = JSON.parse(response);
                        // Hide the modal
                        $('#verConfirmarEntrega').modal('hide');

                        if (res.status === 'success') {
                            // Show success alert
                            const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Manifiesto marcado como entregado exitosamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                            $('#alertContainer').html(alertHtml);

                            // Reload after a short delay
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            // Show error alert
                            const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error al marcar como entregado.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                            $('#alertContainer').html(alertHtml);
                        }
                    },
                    error: function() {
                        // Hide the modal
                        $('#verConfirmarEntrega').modal('hide');

                        // Show error alert
                        const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error en la solicitud.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                        $('#alertContainer').html(alertHtml);
                    }
                });
            });
        });

        $(document).ready(function() {
            $('#manifiestosTable').DataTable();

            // Crear manifiesto
            $('#formCrearManifiesto').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: '<?php echo site_url("admin/manifiestos/crear"); ?>',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Manifiesto creado exitosamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                            $('#crearManifiestoModal').modal('hide');
                            $('#alertContainer').html(alertHtml);

                            // Reload after a short delay
                            setTimeout(function() {
                                location.reload();
                            }, 2000);


                        } else {
                            const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error al crear manifiesto.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                            $('#alertContainer').html(alertHtml);
                        }
                    },
                    error: function() {
                        // Hide the modal
                        $('#crearManifiestoModal').modal('hide');

                        // Show error alert
                        const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error en la solicitud.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                        $('#alertContainer').html(alertHtml);
                    }
                });
            });
        });
    </script>
</body>

</html>