<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="container-fluid  h-100 overflow-auto px-2">

    <!-- titulo & breadcrumb -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h5 class="fs-5 fw-semibold m-0"> <?php echo $pagetitle; ?></h5>
        </div>

        <div class="text-end  ">
            <?php echo $breadcrumb; ?>
        </div>
    </div>

    <section class="row">
        <div class="col">
            <div class="card d-block">

                <div class="card-body">

                    <section class="content">
                        <!-- Mensajes de éxito o error -->
                        <?php if ($this->session->flashdata('message')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $this->session->flashdata('message'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $this->session->flashdata('error'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Botón para crear nuevo acceso API -->
                        <div class="mb-3">
                            <a href="<?php echo site_url('admin/api_accesos/crear'); ?>" class="btn btn-primary">
                                <i class="fa fa-plus me-2"></i> Crear Nuevo Acceso API
                            </a>
                        </div>

                        <!-- Tabla de accesos API -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Accesos API</h3>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($api_clients)): ?>
                                    <table id="apiAccesosTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th></th> <!-- Columna para el botón de colapsar -->
                                                <th>Client ID</th>
                                                <th>Redirect URI</th>
                                                <th>Scope</th>
                                                <th>Creado el</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($api_clients as $index => $client): ?>
                                                <tr>
                                                    <!-- Botón para desplegar tokens -->
                                                    <td class="text-center">
                                                        <?php if (!empty($client->tokens)): ?>
                                                            <button class="btn btn-sm btn-info toggle-tokens"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#tokens<?php echo $index; ?>"
                                                                aria-expanded="false"
                                                                aria-controls="tokens<?php echo $index; ?>"
                                                                title="Mostrar/Ocultar Tokens">
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">No Tokens</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($client->client_id, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($client->redirect_uri, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($client->scope, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php
                                                        // Verificar si 'created_at' existe y es válido
                                                        if (isset($client->created_at) && !empty($client->created_at)) {
                                                            try {
                                                                $date = new DateTime($client->created_at);
                                                                echo $date->format('Y-m-d H:i:s');
                                                            } catch (Exception $e) {
                                                                echo 'Fecha inválida';
                                                            }
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <!-- Opciones: Eliminar -->
                                                        <a href="<?php echo site_url('admin/api_accesos/eliminar/' . $client->client_id); ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este acceso API?');">
                                                            <i class="fa fa-trash"></i> Eliminar
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php if (!empty($client->tokens)): ?>
                                                    <tr id="tokens<?php echo $index; ?>" class="collapse">
                                                        <td colspan="6" class="bg-light">
                                                            <h5>Historial de Tokens</h5>
                                                            <table class="table table-sm table-bordered mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Token</th>
                                                                        <th>Expira</th>
                                                                        <th>Scope</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($client->tokens as $token): ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($token['access_token'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                            <td>
                                                                                <?php
                                                                                try {
                                                                                    // Intentar crear un objeto DateTime
                                                                                    $expiresDate = new DateTime($token['expires']);
                                                                                    echo $expiresDate->format('Y-m-d H:i:s');
                                                                                } catch (Exception $e) {
                                                                                    echo 'Fecha inválida (' . htmlspecialchars($token['expires'], ENT_QUOTES, 'UTF-8') . ')';
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td><?php echo htmlspecialchars($token['scope'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>No hay accesos API registrados.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Inicializar DataTables y Tooltips -->
<script>
    $(document).ready(function() {
        // Inicializar DataTables
        $('#apiAccesosTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            },
            "order": [
                [4, "desc"]
            ], // Ordenar por la columna 'Creado el' de forma descendente
            "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 5]
                } // Deshabilitar ordenamiento en 'Botón' y 'Acciones'
            ],
            "responsive": true, // Hacer la tabla responsiva
            "lengthChange": false, // Ocultar el selector de cantidad de filas
            "autoWidth": false // Desactivar el ancho automático
        });

        // Inicializar Tooltips para botones de colapsar
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('.toggle-tokens'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>