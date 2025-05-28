<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!-- ============================================================== -->
<!-- Dashboard Page Inicio -->
<!-- ============================================================== -->
<div class="container-fluid h-100 overflow-auto">

  <!-- Título & Breadcrumb -->
  <section class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
      <h5 class="fs-5 fw-semibold m-0"> <?php echo $pagetitle; ?></h5>
    </div>

    <div class="text-end">
      <?php echo $breadcrumb; ?>
    </div>
  </section>

  <?php echo $dashboard_alert_file_install; ?>

  <!-- Definición de variables para evitar errores -->
  <?php
  // Estas variables deberían ser reemplazadas con datos reales
  $total_pedidos = isset($total_pedidos) ? $total_pedidos : 10;
  $total_documentos_escaneados = isset($total_documentos_escaneados) ? $total_documentos_escaneados : 7;
  $pedidos_terminados = isset($pedidos_terminados) ? $pedidos_terminados : 5;
  $pedidos_pendientes = isset($pedidos_pendientes) ? $pedidos_pendientes : 5;
  $alerta_mes_diferente = isset($alerta_mes_diferente) ? $alerta_mes_diferente : false;
  $fecha_facturacion = isset($fecha_facturacion) ? $fecha_facturacion : date('Y-m');
  ?>

  <!-- Alerta si el mes es diferente -->
  <?php if ($alerta_mes_diferente): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex flex-row flex-wrap gap-1 align-items-center" role="alert">
      <strong>¡Atención!</strong> El mes actual es diferente al mes de facturación (<?php echo $fecha_facturacion; ?>). Por favor, revise el cambio de mes.
      <a href="<?php echo base_url('admin/config'); ?>" class="alert-link d-flex align-items-center"><i class="mdi mdi-calendar-month fs-5"></i> Cambiar Mes Facturación</a>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Gráfico y Cards -->
  <div class="row w-100">

    <!-- Gráfico de Pedidos -->
    <div class="col-md-12 col-xl-6" style="padding-left:0;">
      <div class="card">
        <div class="card-body">
          <div id="zoomable_line_chart" class="apex-charts"></div>
        </div>
      </div>
    </div>

    <!-- Contenedores de Información del Día -->
    <div class="col-md-12 col-xl-6" style="height: 200px; padding-right:0;">
      <div class="row g-4">

        <!-- Total Pedidos -->
        <div class="col-md-6 col-xl-6">
          <div class="card mb-0 h-100">
            <div class="card-body">
              <div class="widget-first d-flex gap-2 justify-content-between align-items-center h-100">

                <div class="d-flex align-items-center mb-2">
                  <div class="p-2 border border-primary border-opacity-10 bg-primary-subtle rounded-pill me-2">
                    <div class="bg-primary rounded-circle widget-size text-center">
                      <i class="mdi mdi-clipboard-text-multiple text-white"></i>
                    </div>
                  </div>
                  <p class="mb-0 text-dark fs-15">Total Pedidos</p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="mb-0 fs-22 text-black me-3"><?php echo $total_pedidos; ?></h3>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Documentos Escaneados Sin Enlazar -->
        <div class="col-md-6 col-xl-6">
          <div class="card mb-0">
            <div class="card-body">
              <div class="widget-first d-flex gap-2 justify-content-between">

                <div class="d-flex align-items-center mb-2">
                  <div class="p-2 border border-secondary border-opacity-10 bg-secondary-subtle rounded-pill me-2">
                    <div class="bg-secondary rounded-circle widget-size text-center">
                      <i class="mdi mdi-file-document-alert text-white"></i>
                    </div>
                  </div>
                  <p class="mb-0 text-dark fs-15">Documentos Escaneados Sin Enlazar</p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="mb-0 fs-22 text-black me-3"><?php echo $total_documentos_escaneados; ?></h3>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Pedidos Terminados -->
        <div class="col-md-6 col-xl-6">
          <div class="card mb-0">
            <div class="card-body">
              <div class="widget-first d-flex gap-2 justify-content-between">

                <div class="d-flex align-items-center mb-2">
                  <div class="p-2 border border-danger border-opacity-10 bg-danger-subtle rounded-pill me-2">
                    <div class="bg-danger rounded-circle widget-size text-center">
                      <i class="mdi mdi-package-variant-closed-check text-white"></i>
                    </div>
                  </div>
                  <p class="mb-0 text-dark fs-15">Pedidos Terminados</p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="mb-0 fs-22 text-black me-3"><?php echo $pedidos_terminados; ?></h3>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Pedidos Pendientes -->
        <div class="col-md-6 col-xl-6">
          <div class="card mb-0">
            <div class="card-body">
              <div class="widget-first d-flex gap-2 justify-content-between">

                <div class="d-flex align-items-center mb-2">
                  <div class="p-2 border border-warning border-opacity-10 bg-warning-subtle rounded-pill me-2">
                    <div class="bg-warning rounded-circle widget-size text-center">
                      <i class="mdi mdi-store-clock text-white"></i>
                    </div>
                  </div>
                  <p class="mb-0 text-dark fs-15">Pedidos Pendientes</p>
                </div>


                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="mb-0 fs-22 text-black me-3"><?php echo $pedidos_pendientes; ?></h3>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- **Nuevas Tarjetas de Manifiestos** -->

        <!-- Total Manifiestos -->
        <div class="col-md-6 col-xl-6">
          <div class="card mb-0 h-100">
            <div class="card-body">
              <div class="widget-first d-flex gap-2 justify-content-between align-items-center h-100">

                <div class="d-flex align-items-center mb-2">
                  <div class="p-2 border border-secondary border-opacity-10 bg-secondary-subtle rounded-pill me-2">
                    <div class="bg-secondary rounded-circle widget-size text-center">
                      <i class="mdi mdi-truck-check text-white"></i>
                    </div>
                  </div>
                  <p class="mb-0 text-dark fs-15">Total Manifiestos</p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="mb-0 fs-22 text-black me-3"><?php echo $total_manifiestos; ?></h3>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Manifiestos Entregados -->
        <div class="col-md-6 col-xl-6">
          <div class="card mb-0 h-100">
            <div class="card-body">
              <div class="widget-first d-flex gap-2 justify-content-between align-items-center h-100">

                <div class="d-flex align-items-center mb-2">
                  <div class="p-2 border border-success border-opacity-10 bg-success-subtle rounded-pill me-2">
                    <div class="bg-success rounded-circle widget-size text-center">
                      <i class="mdi mdi-checkbox-marked-circle text-white"></i>
                    </div>
                  </div>
                  <p class="mb-0 text-dark fs-15">Manifiestos Entregados</p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="mb-0 fs-22 text-black me-3"><?php echo $manifiestos_entregados; ?></h3>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Manifiestos Pendientes -->
        <div class="col-md-6 col-xl-6">
          <div class="card mb-0 h-100">
            <div class="card-body">
              <div class="widget-first d-flex gap-2 justify-content-between align-items-center h-100">
                <div class="d-flex align-items-center mb-2">
                  <div class="p-2 border border-danger border-opacity-10 bg-danger-subtle rounded-pill me-2">
                    <div class="bg-danger rounded-circle widget-size text-center">
                      <i class="mdi mdi-truck-delivery text-white"></i>
                    </div>
                  </div>
                  <p class="mb-0 text-dark fs-15">Manifiestos Pendientes</p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="mb-0 fs-22 text-black me-3"><?php echo $manifiestos_pendientes; ?></h3>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Promedio Días de Entrega -->
        <div class="col-md-6 col-xl-6">
          <div class="card mb-0 h-100">
            <div class="card-body">
              <div class="widget-first d-flex gap-2 justify-content-between align-items-center h-100">
                <div class="d-flex align-items-center mb-2">
                  <div class="p-2 border border-info border-opacity-10 bg-info-subtle rounded-pill me-2">
                    <div class="bg-info rounded-circle widget-size text-center">
                      <i class="mdi mdi-calendar-check text-white"></i>
                    </div>
                  </div>
                  <p class="mb-0 text-dark fs-15">Promedio Días de Entrega</p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                  <h3 class="mb-0 fs-22 text-black me-3"><?php echo $promedio_dias_entrega; ?> días</h3>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
  <!-- end start -->
  <!-- ... Código existente ... -->

  <!-- Gráficos de Manifiestos -->
  <div class="row w-100 mt-4">
    <!-- Gráfico de Estado de Manifiestos -->
    <div class="col-md-6">
      <div class="card mb-4">
        <h5 class="card-header">Estado de Manifiestos</h5>
        <div class="card-body">
          <div id="manifiestosStatusChart"></div>
        </div>
      </div>
    </div>

    <!-- Gráfico de Tiempo de Entrega de Manifiestos -->
    <div class="col-md-6">
      <div class="card mb-4">
        <h5 class="card-header">Tiempo de Entrega de Manifiestos</h5>
        <div class="card-body">
          <div id="manifiestosTiempoChart"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráfico de Pedidos por Tipo -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <h5 class="card-header">Pedidos por Tipo</h5>
        <div class="card-body">
          <canvas id="pedidosPorTipoChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- OAurth 2.0 -->
  <!-- Requisitos de Implementación -->
  <div class="row w-100">
    <div class="card">

      <div class="card-header">
        <h5 class="card-title mb-0">Documentación OAuth2</h5>
      </div><!-- end card header -->

      <div class="card-body">

        <div class="row">
          <div class="col-md-3">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
              <a class="nav-link mb-2 active" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Requisitos de Implementación</a>
              <a class="nav-link mb-2" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Autenticación OAuth 2.0</a>
              <a class="nav-link mb-2" id="v-pills-messages-tab" data-bs-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Envío de Datos a la API</a>
              <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Consideraciones de Seguridad</a>
            </div>
          </div>
          <div class="col-md-9">
            <div class="tab-content p-0 text-muted mt-md-0" id="v-pills-tabContent">
              <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <div class="card text-dark bg-light mb-3">
                  <div class="card-header">Requisitos de Implementación</div>
                  <div class="card-body">
                    <ul>
                      <li>Acceso a la API: Se proporcionará una vez finalizado el desarrollo y realizadas las pruebas.</li>
                      <li>Credenciales OAuth 2.0: Necesita <code>client_id</code> y <code>client_secret</code> proporcionados por nuestro equipo.</li>
                      <li>Herramientas: Se recomienda utilizar herramientas como <strong>curl</strong> o <strong>Postman</strong> para probar las solicitudes a la API.</li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                <div class="card text-dark bg-light mb-3">
                  <div class="card-header">Autenticación OAuth 2.0</div>
                  <div class="card-body">
                    <p>Para autenticar y obtener un token de acceso, realice una solicitud <strong>POST</strong> al servidor de autorización:</p>
                    <pre><code>POST https://empresa_prueba.com/connect/token</code></pre>
                    <p>Con los siguientes parámetros:</p>
                    <ul>
                      <li><code>grant_type=client_credentials</code></li>
                      <li><code>client_id=[su_client_id]</code></li>
                      <li><code>client_secret=[su_client_secret]</code></li>
                      <li><code>scope=api1</code></li>
                    </ul>
                    <p>Ejemplo usando <strong>curl</strong>:</p>
                    <pre><code class="language-bash">
curl -X POST \
  https://empresa_prueba.com/connect/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=su_client_id&client_secret=su_client_secret&scope=api1"
                  </code></pre>
                    <p>Respuesta esperada:</p>
                    <pre><code class="language-json">
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "Bearer",
  "expires_in": 3600
}
                  </code></pre>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                <div class="card text-dark bg-light mb-3">
                  <div class="card-header">Envío de Datos a la API</div>
                  <div class="card-body">
                    <p>Una vez obtenido el token de acceso, puede enviar los datos de pedidos a nuestra API:</p>
                    <pre><code>POST https://empresa_prueba.com/api/orders</code></pre>
                    <p>Incluya los siguientes encabezados:</p>
                    <ul>
                      <li><code>Authorization: Bearer [su_access_token]</code></li>
                      <li><code>Content-Type: application/json</code></li>
                    </ul>
                    <p>Ejemplo de solicitud usando <strong>curl</strong>:</p>
                    <pre><code class="language-bash">
curl -X POST \
  https://empresa_prueba.com/api/orders \
  -H "Authorization: Bearer [su_access_token]" \
  -H "Content-Type: application/json" \
  -d '{
        "orders": [
            {
                "PO": "4204682629",
                "Orden": "SO551303",
                "Tp": "5",
                "FechaOC": "2024-08-19",
                "Cliente": "YY00909",
                "Nombre": "CONSTRUMAR",
                "Guia": "R1499834",
                "Factura": "E1553405",
                "FechaF": "2024-08-20",
                "Ship": "YY11610",
                "Nombre2": "CONSTRUMAR SA (Quilicura)",
                "Direccion": "LAUTARO # 9202",
                "Comuna": "SANTIAGO",
                "Region": "RG09"
            },
            ...
        ]
    }'
                  </code></pre>
                    <p>Asegúrese de que el JSON enviado sigue la estructura correcta.</p>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                <div class="card text-dark bg-light mb-3">
                  <div class="card-header">Consideraciones de Seguridad</div>
                  <div class="card-body">
                    <ul>
                      <li><strong>Almacenamiento de Tokens:</strong> Los tokens de acceso deben almacenarse de forma segura y nunca compartirse.</li>
                      <li><strong>Expiración del Token:</strong> Los tokens tienen una vida útil limitada. Solicite un nuevo token cuando el actual expire.</li>
                      <li><strong>Uso de HTTPS:</strong> Todas las comunicaciones con la API deben realizarse sobre HTTPS para garantizar la seguridad.</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- end col -->
        </div><!-- end row -->

      </div>
    </div>

  </div>
</div>



<!-- Tablas Detalladas -->
<!-- ... Código existente ... -->

</div>
<!-- ============================================================== -->
<!-- Dashboard Page Fin -->
<!-- ============================================================== -->

<!-- Scripts para jQuery, ApexCharts, Chart.js y otros necesarios -->
<!-- Asegúrate de incluir estos scripts solo una vez -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- ApexCharts JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    // Generar datos para el gráfico de pedidos
    var pedidosPorDia = <?php echo json_encode($pedidos_por_dia); ?>;
    var dias = [];
    var cantidades = [];

    // Extraer datos del array
    pedidosPorDia.forEach(function(pedido) {
      dias.push(pedido.fecha);
      cantidades.push(parseInt(pedido.total));
    });

    /* ApexCharts - Pedidos por Periodo */
    const optionsPedidosPeriodo = {
      chart: {
        height: 493,
        type: "line",
        parentHeightOffset: 0,
        zoom: {
          enabled: false // Cambié !1 por false para mayor claridad
        },
        toolbar: {
          show: false // Cambié !1 por false para mayor claridad
        },
      },
      dataLabels: {
        enabled: false // Cambié !1 por false para mayor claridad
      },
      stroke: {
        width: [3, 4, 3],
        curve: 'smooth',
        dashArray: [0, 8, 5]
      },
      series: [{
        type: 'area',
        name: 'Pedidos',
        data: cantidades
      }],
      title: {
        text: "Pedidos por periodo",
        align: "left",
        style: {
          fontWeight: 500
        }
      },
      markers: {
        size: 0,
        hover: {
          sizeOffset: 6
        }
      },
      xaxis: {
        categories: dias,
        title: {
          text: 'Días'
        }
      },
      yaxis: {
        title: {
          text: 'Cantidad de Pedidos'
        }
      },
      tooltip: {
        y: [{
            title: {
              formatter: function(e) {
                return e + " (días)";
              },
            },
          },
          {
            title: {
              formatter: function(e) {
                return e + " por sesión";
              },
            },
          },
          {
            title: {
              formatter: function(e) {
                return e;
              },
            },
          },
        ],
      },
      colors: ["#287F71", "#343a40"],
      fill: {
        type: 'gradient',
        gradient: {
          shade: 'dark',
          type: "vertical", // Cambiado a 'vertical' para que el degradado vaya de arriba a abajo
          shadeIntensity: 1,
          gradientToColors: ["#287F71"], // Define el color de degradado al que quieres llegar
          inverseColors: false,
          opacityFrom: 0.5, // Opacidad inicial
          opacityTo: 0, // Opacidad final (transparente)
          stops: [0, 100] // Define el inicio y fin del degradado
        }
      },
      grid: {
        borderColor: "#f1f1f1"
      },
    };

    // Crear el gráfico de pedidos
    const pedidosPeriodoChart = new ApexCharts(
      document.querySelector("#zoomable_line_chart"),
      optionsPedidosPeriodo
    );
    pedidosPeriodoChart.render();

    // Convertir arreglos PHP a arreglos JavaScript usando json_encode
    var chartManifiestosStatusLabels = <?php echo json_encode($chart_manifiestos_status['labels']); ?>; // ["Activo", "Entregado"]
    var chartManifiestosStatusData = <?php echo json_encode($chart_manifiestos_status['data']); ?>; // [4, 2]
    const totalManifiestos = Number(chartManifiestosStatusData[0]) + Number(chartManifiestosStatusData[1]);

    // Opciones del gráfico
    var optionsManifiestosStatus = {
      chart: {
        type: 'pie',
        height: 400,
      },
      labels: chartManifiestosStatusLabels,
      series: chartManifiestosStatusData,
      dataLabels: {
        enabled: true,
        formatter: function(val, opts) {
          // Calcular el porcentaje manualmente
          const index = opts.seriesIndex;
          const count = chartManifiestosStatusData[index];
          const percentage = ((count / totalManifiestos) * 100).toFixed(1);
          return `${chartManifiestosStatusLabels[index]}: ${count} (${percentage}%)`;
        },
        style: {
          fontSize: '14px',
          fontWeight: 'bold',
        },
      },
      legend: {
        position: 'right',
      },
      colors: ['#007bff', '#28a745'], // Colores personalizados
      tooltip: {
        y: {
          formatter: function(val, opts) {
            const index = opts.seriesIndex;
            return `${chartManifiestosStatusLabels[index]}: ${chartManifiestosStatusData[index]} Manifiestos`;
          },
        },
      },
    };

    var chartManifiestosStatus = new ApexCharts(
      document.querySelector("#manifiestosStatusChart"),
      optionsManifiestosStatus
    );

    chartManifiestosStatus.render();


    // **Gráfico de Tiempo de Entrega de Manifiestos**
    var chartManifiestosTiempoLabels = <?php echo json_encode($chart_manifiestos_tiempo['labels']); ?>;
    var chartManifiestosTiempoData = <?php echo json_encode($chart_manifiestos_tiempo['data']); ?>;

    // Verificar si los datos existen y son válidos
    if (chartManifiestosTiempoLabels && chartManifiestosTiempoData) {
      var optionsManifiestosTiempo = {
        chart: {
          type: 'bar',
          height: 350
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
          },
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        series: [{
          name: 'Manifiestos',
          data: chartManifiestosTiempoData
        }],
        xaxis: {
          categories: chartManifiestosTiempoLabels,
        },
        yaxis: {
          title: {
            text: 'Cantidad de Manifiestos'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function(val) {
              return val + " Manifiestos"
            }
          }
        }
      };

      var chartManifiestosTiempo = new ApexCharts(document.querySelector("#manifiestosTiempoChart"), optionsManifiestosTiempo);
      chartManifiestosTiempo.render();
    } else {
      console.error("Datos para el gráfico de Tiempo de Entrega de Manifiestos están incompletos.");
    }

    // **Gráfico de Pedidos por Tipo**
    var pedidosPorTipo = <?php echo json_encode($chart_pedidos_por_tipo['data']); ?>;
    var tipos = <?php echo json_encode($chart_pedidos_por_tipo['labels']); ?>;

    if (pedidosPorTipo && tipos) {
      var ctxPedidosPorTipo = document.getElementById('pedidosPorTipoChart').getContext('2d');
      var pedidosPorTipoChart = new Chart(ctxPedidosPorTipo, {
        type: 'bar',
        data: {
          labels: tipos,
          datasets: [{
            label: 'Pedidos',
            data: pedidosPorTipo,
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255,99,132,1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              precision: 0
            },
            x: {
              title: {
                display: true,
                text: 'Tipo de Pedido'
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            title: {
              display: true,
              text: 'Pedidos por Tipo'
            }
          }
        }
      });
    } else {
      console.error("Datos para el gráfico de Pedidos por Tipo están incompletos.");
    }

  });
</script>
