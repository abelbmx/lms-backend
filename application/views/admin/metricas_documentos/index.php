<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
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

    <!-- Alertas de Retrasos -->
    <?php if (!empty($alert_messages)): ?>
        <?php foreach ($alert_messages as $alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $alert['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Métricas Principales -->
    <div class="row">
        <!-- Tarjeta Total Documentos -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-4">
                <div class="card-header">Total Documentos</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_documentos_enlazados + $total_documentos_sin_enlazar; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Documentos Enlazados -->
        <div class="col-md-3">
            <div class="card text-white bg-success mb-4">
                <div class="card-header">Documentos Enlazados</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_documentos_enlazados; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Documentos Sin Enlazar -->
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-4">
                <div class="card-header">Documentos Sin Enlazar</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_documentos_sin_enlazar; ?></h5>
                </div>
            </div>
        </div>

        <!-- Tarjeta Documentos Retrasados -->
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-4">
                <div class="card-header">Documentos Retrasados</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_documentos_retrasados; ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Gráfico de Estado de Documentos -->
        <div class="col-md-6">
            <div class="card mb-4">
                <h5 class="card-header">Estado de Documentos</h5>
                <div class="card-body">
                    <!-- Contenedor del Gráfico con Tamaño Específico -->
                    <div class="d-flex justify-content-center">
                        <div class="chart-container" style="position: relative; height:250px; width:250px;">
                            <canvas id="documentsStatusChart"></canvas>
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
                    <!-- Contenedor del Gráfico con Tamaño Específico -->
                    <div class="d-flex justify-content-center">
                        <div class="chart-container" style="position: relative; height:250px; width:250px;">
                            <canvas id="documentsEnlazadosVsSinEnlazarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Documentos Retrasados -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">Documentos Retrasados</h5>
                <div class="card-body">
                    <?php if ($total_documentos_retrasados > 0): ?>
                        <table id="documentosRetrasadosTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Archivo</th>
                                    <th>Fecha Subida</th>
                                    <th>Días Retrasados</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentos_retrasados as $doc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doc->id, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($doc->file_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($doc->uploaded_at)); ?></td>
                                        <td>
                                            <?php
                                            $uploaded_at = new DateTime($doc->uploaded_at);
                                            $now = new DateTime();
                                            $interval = $uploaded_at->diff($now);
                                            echo $interval->days . ' días';
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-info" onclick="previewDocument('<?php echo base_url($doc->file_path); ?>', '<?php echo htmlspecialchars($doc->file_name, ENT_QUOTES, 'UTF-8'); ?>')">
                                                <span class="mdi mdi-eye-outline"></span> Ver Documento
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay documentos retrasados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    <!-- Modal para Previsualizar Documento -->
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

</div>
<!-- ============================================================== -->
<!-- Metricas_Documentos Page Fin -->
<!-- ============================================================== -->

<!-- Estilos CSS para el Contenedor del Gráfico -->
<style>
    .chart-container {
        position: relative;
        height: 250px;
        /* Ajusta según tus necesidades */
        width: 250px;
        /* Ajusta el ancho según tus necesidades */
        margin: auto;
        /* Centra el contenedor dentro del elemento padre */
    }

    /* Espaciado entre tarjetas de métricas */
    .card.mb-4 {
        margin-bottom: 1.5rem !important;
    }

    /* Asegurar que el texto de la leyenda no se superponga */
    .chart-container {
        overflow: hidden;
    }
</style>

<!-- Scripts para DataTables, Chart.js y funcionalidades de las modales -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Inicializar DataTables para Documentos Retrasados
        $('#documentosRetrasadosTable').DataTable({
            "order": [
                [3, 'desc']
            ], // Ordenar por Días Retrasados
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
        });

        // Inicializar Chart.js para Estado de Documentos
        var ctxDocumentsStatus = document.getElementById('documentsStatusChart').getContext('2d');
        var documentsStatusChart = new Chart(ctxDocumentsStatus, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($chart_documents_status['labels']); ?>,
                datasets: [{
                    label: '# de Documentos',
                    data: <?php echo json_encode($chart_documents_status['data']); ?>,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)', // Enlazados
                        'rgba(255, 99, 132, 0.6)' // Sin Enlazar
                        // Agrega más colores si tienes más estados
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)', // Enlazados
                        'rgba(255, 99, 132, 1)' // Sin Enlazar
                        // Agrega más colores si tienes más estados
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Permitir que el gráfico respete las dimensiones del contenedor
                plugins: {
                    legend: {
                        position: 'right', // Mover la leyenda al lado derecho
                        labels: {
                            boxWidth: 20, // Tamaño de la caja de color en la leyenda
                            padding: 20 // Espaciado entre las etiquetas de la leyenda
                        }
                    },
                    title: {
                        display: true,
                        text: 'Estado de Documentos'
                    }
                },
                layout: {
                    padding: {
                        right: 70 // Añadir espacio a la derecha para la leyenda
                    }
                }
            }
        });

        // Inicializar Chart.js para Documentos Enlazados vs Sin Enlazar
        var ctxDocumentsEnlazadosVsSinEnlazar = document.getElementById('documentsEnlazadosVsSinEnlazarChart').getContext('2d');
        var documentsEnlazadosVsSinEnlazarChart = new Chart(ctxDocumentsEnlazadosVsSinEnlazar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_documents_status['labels']); ?>,
                datasets: [{
                    label: '# de Documentos',
                    data: <?php echo json_encode($chart_documents_status['data']); ?>,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)', // Enlazados
                        'rgba(255, 159, 64, 0.6)' // Sin Enlazar
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

        // Función para previsualizar documentos
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
    });
</script>