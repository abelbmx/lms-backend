<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!-- ============================================================== -->
<!-- Reportes Page Inicio -->
<!-- ============================================================== -->
<div class="container-fluid h-100 overflow-auto px-2">

    <!-- Título & Breadcrumb -->
    <section class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h5 class="fs-5 fw-semibold m-0"> <?php echo $pagetitle; ?></h5>
        </div>

        <div class="text-end">
            <?php echo $breadcrumb; ?>
        </div>
    </section>

    <!-- Alerta de éxito o error -->
    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('message'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario de Filtros -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">Filtros de Reportes</h5>
                <div class="card-body">
                    <form method="GET" action="<?php echo site_url('admin/reportes'); ?>" class="row g-3">
                        <div class="col-md-3">
                            <label for="fecha_oc_inicio" class="form-label">Fecha OC Inicio</label>
                            <input type="date" class="form-control" id="fecha_oc_inicio" name="fecha_oc_inicio" value="<?php echo set_value('fecha_oc_inicio', isset($_GET['fecha_oc_inicio']) ? $_GET['fecha_oc_inicio'] : ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_oc_fin" class="form-label">Fecha OC Fin</label>
                            <input type="date" class="form-control" id="fecha_oc_fin" name="fecha_oc_fin" value="<?php echo set_value('fecha_oc_fin', isset($_GET['fecha_oc_fin']) ? $_GET['fecha_oc_fin'] : ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_f_inicio" class="form-label">Fecha F Inicio</label>
                            <input type="date" class="form-control" id="fecha_f_inicio" name="fecha_f_inicio" value="<?php echo set_value('fecha_f_inicio', isset($_GET['fecha_f_inicio']) ? $_GET['fecha_f_inicio'] : ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_f_fin" class="form-label">Fecha F Fin</label>
                            <input type="date" class="form-control" id="fecha_f_fin" name="fecha_f_fin" value="<?php echo set_value('fecha_f_fin', isset($_GET['fecha_f_fin']) ? $_GET['fecha_f_fin'] : ''); ?>">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                            <a href="<?php echo site_url('admin/reportes'); ?>" class="btn btn-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas y Gráficos -->
    <div class="row">
        <!-- Tarjeta Total Órdenes -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-4">
                <div class="card-header">Total Órdenes</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_pedidos; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Órdenes Terminadas -->
        <div class="col-md-3">
            <div class="card text-white bg-success mb-4">
                <div class="card-header">Órdenes Terminadas</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $pedidos_terminados; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Órdenes Pendientes -->
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-4">
                <div class="card-header">Órdenes Pendientes</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $pedidos_pendientes; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Tiempo Promedio de Enlace -->
        <div class="col-md-3">
            <div class="card text-white bg-info mb-4">
                <div class="card-header">Tiempo Promedio de Enlace</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $tiempo_promedio_enlace; ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas de Manifiestos -->
    <div class="row">
        <!-- Tarjeta Total Manifiestos -->
        <div class="col-md-3">
            <div class="card text-white bg-secondary mb-4">
                <div class="card-header">Total Manifiestos</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_manifiestos; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Manifiestos Entregados -->
        <div class="col-md-3">
            <div class="card text-white bg-success mb-4">
                <div class="card-header">Manifiestos Entregados</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $manifiestos_entregados; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Manifiestos Pendientes -->
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-4">
                <div class="card-header">Manifiestos Pendientes</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $manifiestos_pendientes; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Promedio Días de Entrega -->
        <div class="col-md-3">
            <div class="card text-white bg-info mb-4">
                <div class="card-header">Promedio Días de Entrega</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $promedio_dias_entrega; ?> días</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Gráfico de Estado de Órdenes -->
        <div class="col-md-6">
            <div class="card mb-4">
                <h5 class="card-header">Estado de Órdenes</h5>
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="chart-container" style="position: relative; height:315px; width:350px;">
                            <canvas id="ordersStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Documentos Enlazados vs Sin Enlazar -->
        <div class="col-md-6">
            <div class="card mb-4">
                <h5 class="card-header">Documentos Enlazados vs Sin Enlazar</h5>
                <div class="card-body">
                    <canvas id="documentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Nuevos Gráficos -->
    <div class="row">
        <!-- Gráfico de Top Clientes -->
        <div class="col-md-6">
            <div class="card mb-4">
                <h5 class="card-header">Top 10 Clientes por Órdenes</h5>
                <div class="card-body">
                    <canvas id="topClientsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Órdenes por Región -->
        <div class="col-md-6">
            <div class="card mb-4">
                <h5 class="card-header">Órdenes por Región</h5>
                <div class="card-body">
                    <canvas id="ordersByRegionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Manifiestos -->
    <div class="row">
        <!-- Gráfico de Estado de Manifiestos -->
        <div class="col-md-6">
            <div class="card mb-4">
                <h5 class="card-header">Estado de Manifiestos</h5>
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="chart-container" style="position: relative; height:315px; width:350px;">
                            <canvas id="manifiestosStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Tiempo de Entrega de Manifiestos -->
        <div class="col-md-6">
            <div class="card mb-4">
                <h5 class="card-header">Tiempo de Entrega de Manifiestos</h5>
                <div class="card-body">
                    <canvas id="manifiestosTiempoChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas Detalladas -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">Detalles de Órdenes, Documentos y Manifiestos</h5>
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="reportesTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="ordenes-tab" data-bs-toggle="tab" data-bs-target="#ordenes" type="button" role="tab" aria-controls="ordenes" aria-selected="true">Órdenes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documentos-sin-enlazar-tab" data-bs-toggle="tab" data-bs-target="#documentos-sin-enlazar" type="button" role="tab" aria-controls="documentos-sin-enlazar" aria-selected="false">Documentos Sin Enlazar</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documentos-enlazados-tab" data-bs-toggle="tab" data-bs-target="#documentos-enlazados" type="button" role="tab" aria-controls="documentos-enlazados" aria-selected="false">Documentos Enlazados</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="manifiestos-tab" data-bs-toggle="tab" data-bs-target="#manifiestos" type="button" role="tab" aria-controls="manifiestos" aria-selected="false">Manifiestos</button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content" id="reportesTabsContent">
                        <!-- Órdenes Tab -->
                        <div class="tab-pane fade show active" id="ordenes" role="tabpanel" aria-labelledby="ordenes-tab">
                            <div class="mt-3">
                                <div class="table-responsive">
                                    <table id="ordenesTable" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>PO</th>
                                                <th>Orden</th>
                                                <th>Tipo</th>
                                                <th>Fecha OC</th>
                                                <th>Cliente</th>
                                                <th>Nombre</th>
                                                <th>Guía</th>
                                                <th>Factura</th>
                                                <th>Fecha F</th>
                                                <th>Ship</th>
                                                <th>Nombre 2</th>
                                                <th>Dirección</th>
                                                <th>Comuna</th>
                                                <th>Región</th>
                                                <th>Chofer</th>
                                                <th>RUT</th>
                                                <th>Patente</th>
                                                <th>Entregado Conforme</th>
                                                <th>Recepcionista</th>
                                                <th>Fecha Entrega</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ordenes as $orden): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($orden->id, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->PO, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Orden, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Tp, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo date('d-m-Y', strtotime($orden->FechaOC)); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Cliente, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Nombre, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Guia, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Factura, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo date('d-m-Y', strtotime($orden->FechaF)); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Ship, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Nombre2, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Direccion, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Comuna, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Region, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->NombreChofer, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->RUT, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Patente, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->EntregadoConforme, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($orden->Recepcionista, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo !empty($orden->FechaEntrega) ? date('d-m-Y', strtotime($orden->FechaEntrega)) : 'N/A'; ?></td>
                                                    <td><?php echo htmlspecialchars(isset($estados[$orden->Estado]) ? $estados[$orden->Estado] : 'Desconocido', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Documentos Sin Enlazar Tab -->
                        <div class="tab-pane fade" id="documentos-sin-enlazar" role="tabpanel" aria-labelledby="documentos-sin-enlazar-tab">
                            <div class="mt-3">
                                <table id="documentosSinEnlazarTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Archivo</th>
                                            <th>Fecha Subida</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documentos_sin_enlazar as $doc): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($doc->id, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($doc->file_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($doc->uploaded_at)); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Documentos Enlazados Tab -->
                        <div class="tab-pane fade" id="documentos-enlazados" role="tabpanel" aria-labelledby="documentos-enlazados-tab">
                            <div class="mt-3">
                                <table id="documentosEnlazadosTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Archivo</th>
                                            <th>Fecha Subida</th>
                                            <th>Fecha Enlazado</th>
                                            <th>Tiempo de Enlace</th>
                                            <th>Orden PO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documentos_enlazados as $doc): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($doc->id, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($doc->file_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo date('d-m-Y H:i', strtotime($doc->uploaded_at)); ?></td>
                                                <td><?php echo date('d-m-Y H:i', strtotime($doc->enlazado_at)); ?></td>
                                                <td>
                                                    <?php
                                                    $uploaded_at = new DateTime($doc->uploaded_at);
                                                    $enlazado_at = new DateTime($doc->enlazado_at);
                                                    $interval = $uploaded_at->diff($enlazado_at);
                                                    echo $interval->days . ' días, ' . $interval->h . ' horas, ' . $interval->i . ' minutos';
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($doc->PO, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Manifiestos Tab -->
                        <div class="tab-pane fade" id="manifiestos" role="tabpanel" aria-labelledby="manifiestos-tab">
                            <div class="mt-3">
                                <table id="manifiestosTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Conductor</th>
                                            <th>Camión</th>
                                            <th>Teléfono</th>
                                            <th>Fecha</th>
                                            <th>Fecha Estimada</th>
                                            <th>Fecha Entrega</th>
                                            <th>Estado</th>
                                            <th>Tiempo de Entrega (días hábiles)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($manifiestos as $manifiesto): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($manifiesto['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($manifiesto['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($manifiesto['conductor'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($manifiesto['camion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($manifiesto['telefono'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($manifiesto['fecha'])); ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($manifiesto['fecha_estimada'])); ?></td>
                                                <td><?php echo !empty($manifiesto['fecha_entrega']) ? date('d-m-Y', strtotime($manifiesto['fecha_entrega'])) : 'N/A'; ?></td>
                                                <td><?php echo htmlspecialchars($manifiesto['estado'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo isset($manifiesto['tiempo_entrega']) ? $manifiesto['tiempo_entrega'] : 'N/A'; ?></td>
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
    </div>

    <!-- Gráfico de Pedidos por Día -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">Pedidos por Periodo</h5>
                <div class="card-body">
                    <canvas id="pedidosPorDiaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- ============================================================== -->
<!-- Reportes Page Fin -->
<!-- ============================================================== -->

<!-- Scripts para jQuery, DataTables y Chart.js -->
<!-- Asegúrate de incluir estos scripts solo una vez -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Especificar una versión compatible de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Configuración común para DataTables
        var dataTableConfig = {
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<span class="mdi mdi-file-excel"></span> Exportar a Excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<span class="mdi mdi-file-delimited"></span> Exportar a CSV',
                    className: 'btn btn-info',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ]
        };

        // Inicializar DataTables para Órdenes
        $('#ordenesTable').DataTable($.extend({}, dataTableConfig, {
            "order": [
                [4, 'desc']
            ]
        }));

        // Inicializar DataTables para Documentos Sin Enlazar
        $('#documentosSinEnlazarTable').DataTable($.extend({}, dataTableConfig, {
            "order": [
                [2, 'desc']
            ]
        }));

        // Inicializar DataTables para Documentos Enlazados
        $('#documentosEnlazadosTable').DataTable($.extend({}, dataTableConfig, {
            "order": [
                [3, 'desc']
            ]
        }));

        // Inicializar DataTables para Manifiestos
        $('#manifiestosTable').DataTable($.extend({}, dataTableConfig, {
            "order": [
                [5, 'desc']
            ]
        }));

        // Función para generar colores dinámicamente
        function generateColors(num) {
            var colors = [];
            for (var i = 0; i < num; i++) {
                var r = Math.floor(Math.random() * 255);
                var g = Math.floor(Math.random() * 255);
                var b = Math.floor(Math.random() * 255);
                colors.push('rgba(' + r + ', ' + g + ', ' + b + ', 0.6)');
            }
            return colors;
        }

        // Verificar que los datos sean arrays antes de pasarlos a Chart.js
        function safeJsonEncode(data) {
            return typeof data === 'undefined' ? [] : JSON.parse(JSON.stringify(data));
        }

        // Inicializar Chart.js para Estado de Órdenes
        var ctxOrdersStatus = document.getElementById('ordersStatusChart').getContext('2d');
        var ordersStatusChart = new Chart(ctxOrdersStatus, {
            type: 'pie',
            data: {
                labels: safeJsonEncode(<?php echo json_encode($chart_orders_status['labels']); ?>),
                datasets: [{
                    label: '# de Órdenes',
                    data: safeJsonEncode(<?php echo json_encode($chart_orders_status['data']); ?>),
                    backgroundColor: generateColors(<?php echo count($chart_orders_status['labels']); ?>),
                    borderColor: generateColors(<?php echo count($chart_orders_status['labels']); ?>),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Estado de Órdenes'
                    }
                }
            }
        });

        // Inicializar Chart.js para Documentos Enlazados vs Sin Enlazar
        var ctxDocuments = document.getElementById('documentsChart').getContext('2d');
        var documentsChart = new Chart(ctxDocuments, {
            type: 'bar',
            data: {
                labels: safeJsonEncode(<?php echo json_encode($chart_documents['labels']); ?>),
                datasets: [{
                    label: '# de Documentos',
                    data: safeJsonEncode(<?php echo json_encode($chart_documents['data']); ?>),
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Documentos Enlazados vs Sin Enlazar'
                    }
                }
            }
        });

        // Inicializar Chart.js para Top Clientes
        var ctxTopClients = document.getElementById('topClientsChart').getContext('2d');
        var topClientsChart = new Chart(ctxTopClients, {
            type: 'bar',
            data: {
                labels: safeJsonEncode(<?php echo json_encode($chart_top_clients['labels']); ?>),
                datasets: [{
                    label: '# de Órdenes',
                    data: safeJsonEncode(<?php echo json_encode($chart_top_clients['data']); ?>),
                    backgroundColor: generateColors(<?php echo count($chart_top_clients['labels']); ?>),
                    borderColor: generateColors(<?php echo count($chart_top_clients['labels']); ?>),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Top 10 Clientes por Órdenes'
                    }
                }
            }
        });

        // Inicializar Chart.js para Órdenes por Región
        var ctxOrdersByRegion = document.getElementById('ordersByRegionChart').getContext('2d');
        var ordersByRegionChart = new Chart(ctxOrdersByRegion, {
            type: 'pie',
            data: {
                labels: safeJsonEncode(<?php echo json_encode($chart_orders_by_region['labels']); ?>),
                datasets: [{
                    label: '# de Órdenes',
                    data: safeJsonEncode(<?php echo json_encode($chart_orders_by_region['data']); ?>),
                    backgroundColor: generateColors(<?php echo count($chart_orders_by_region['labels']); ?>),
                    borderColor: generateColors(<?php echo count($chart_orders_by_region['labels']); ?>),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Órdenes por Región'
                    }
                }
            }
        });

        // Inicializar Chart.js para Estado de Manifiestos
        var ctxManifiestosStatus = document.getElementById('manifiestosStatusChart').getContext('2d');
        var manifiestosStatusChart = new Chart(ctxManifiestosStatus, {
            type: 'pie',
            data: {
                labels: safeJsonEncode(<?php echo json_encode($chart_manifiestos_status['labels']); ?>),
                datasets: [{
                    label: '# de Manifiestos',
                    data: safeJsonEncode(<?php echo json_encode($chart_manifiestos_status['data']); ?>),
                    backgroundColor: generateColors(<?php echo count($chart_manifiestos_status['labels']); ?>),
                    borderColor: generateColors(<?php echo count($chart_manifiestos_status['labels']); ?>),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Estado de Manifiestos'
                    }
                }
            }
        });

        // Inicializar Chart.js para Tiempo de Entrega de Manifiestos
        var ctxManifiestosTiempo = document.getElementById('manifiestosTiempoChart').getContext('2d');
        var manifiestosTiempoChart = new Chart(ctxManifiestosTiempo, {
            type: 'bar',
            data: {
                labels: safeJsonEncode(<?php echo json_encode($chart_manifiestos_tiempo['labels']); ?>),
                datasets: [{
                    label: '# de Manifiestos',
                    data: safeJsonEncode(<?php echo json_encode($chart_manifiestos_tiempo['data']); ?>),
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Tiempo de Entrega de Manifiestos'
                    }
                }
            }
        });

        // Inicializar Chart.js para Pedidos por Día
        var pedidosPorDia = <?php echo json_encode($pedidos_por_dia); ?>;
        var dias = [];
        var cantidades = [];

        pedidosPorDia.forEach(function(pedido) {
            dias.push(pedido.fecha);
            cantidades.push(parseInt(pedido.total));
        });

        var ctxPedidosPorDia = document.getElementById('pedidosPorDiaChart').getContext('2d');
        var pedidosPorDiaChart = new Chart(ctxPedidosPorDia, {
            type: 'line',
            data: {
                labels: dias,
                datasets: [{
                    label: 'Pedidos',
                    data: cantidades,
                    backgroundColor: 'rgba(40, 127, 113, 0.2)',
                    borderColor: 'rgba(40, 127, 113, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Pedidos por Periodo'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
    });
</script>
