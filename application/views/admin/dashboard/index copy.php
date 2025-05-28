<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="content-wrapper">
  <section class="content-header">
    <?php echo $pagetitle; ?>
    <?php echo $breadcrumb; ?>
  </section>

  <section class="content">
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

    <!-- Contenedores de Información del Día -->
    <div class="row">
      <!-- Total Pedidos -->
      <div class="col-md-3 col-sm-6 col-12">
        <div class="card mb-0">
          <div class="card-body">
            <div class="widget-first">

              <div class="d-flex align-items-center mb-2">
                <div class="p-2 border border-primary border-opacity-10 bg-primary-subtle rounded-pill me-2">
                  <div class="bg-primary rounded-circle widget-size text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                      <path fill="#ffffff" d="M12 4a4 4 0 0 1 4 4a4 4 0 0 1-4 4a4 4 0 0 1-4-4a4 4 0 0 1 4-4m0 10c4.42 0 8 1.79 8 4v2H4v-2c0-2.21 3.58-4 8-4" />
                    </svg>
                  </div>
                </div>
                <p class="mb-0 text-dark fs-15">Total Pedidos</p>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fs-22 text-black me-3"><?php echo $total_pedidos; ?></h3>
                <div class="text-center">
                  <span class="text-primary fs-14"><i class="mdi mdi-trending-up fs-14"></i> 12.5%</span>
                  <p class="text-dark fs-13 mb-0">Last 7 days</p>
                </div>
              </div>

            </div>
          </div>
        </div>
        <!-- <div class="card text-white bg-primary mb-3">
          <div class="card-header"><i class="fa fa-list"></i> Total Pedidos</div>
          <div class="card-body">
            <h5 class="card-title"><?php echo $total_pedidos; ?></h5>
          </div>
        </div> -->
      </div>
      <!-- Documentos Escaneados -->

      <div class="col-md-3 col-sm-6 col-12">
        <div class="card mb-0">
          <div class="card-body">
            <div class="widget-first">

              <div class="d-flex align-items-center mb-2">
                <div class="p-2 border border-secondary border-opacity-10 bg-secondary-subtle rounded-pill me-2">
                  <div class="bg-secondary rounded-circle widget-size text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 640 512">
                      <path fill="#ffffff" d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64s-64 28.7-64 64s28.7 64 64 64m448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64s-64 28.7-64 64s28.7 64 64 64m32 32h-64c-17.6 0-33.5 7.1-45.1 18.6c40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64m-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32S208 82.1 208 144s50.1 112 112 112m76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2m-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4" />
                    </svg>
                  </div>
                </div>
                <p class="mb-0 text-dark fs-15">Documentos Escaneados Sin Enlazar</p>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fs-22 text-black me-3"><?php echo $total_documentos_escaneados; ?></h3>
                <div class="text-center">
                  <span class="text-danger fs-14 me-2"><i class="mdi mdi-trending-down fs-14"></i> 1.5%</span>
                  <p class="text-dark fs-13 mb-0">Last 7 days</p>
                </div>
              </div>

            </div>
          </div>
        </div>
        <!--  <div class="card text-white bg-success mb-3">
          <div class="card-header"><i class="fa fa-file-pdf-o"></i> Documentos Escaneados Sin Enlazar</div>
          <div class="card-body">
            <h5 class="card-title"><?php echo $total_documentos_escaneados; ?></h5>
          </div>
        </div> -->
      </div>
      <!-- Pedidos Terminados -->
      <div class="col-md-3 col-sm-6 col-12">
        <div class="card mb-0">
          <div class="card-body">
            <div class="widget-first">

              <div class="d-flex align-items-center mb-2">
                <div class="p-2 border border-danger border-opacity-10 bg-danger-subtle rounded-pill me-2">
                  <div class="bg-danger rounded-circle widget-size text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                      <path fill="#ffffff" d="M7 15h2c0 1.08 1.37 2 3 2s3-.92 3-2c0-1.1-1.04-1.5-3.24-2.03C9.64 12.44 7 11.78 7 9c0-1.79 1.47-3.31 3.5-3.82V3h3v2.18C15.53 5.69 17 7.21 17 9h-2c0-1.08-1.37-2-3-2s-3 .92-3 2c0 1.1 1.04 1.5 3.24 2.03C14.36 11.56 17 12.22 17 15c0 1.79-1.47 3.31-3.5 3.82V21h-3v-2.18C8.47 18.31 7 16.79 7 15" />
                    </svg>
                  </div>
                </div>
                <p class="mb-0 text-dark fs-15">Pedidos Terminados</p>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fs-22 text-black me-3"><?php echo $pedidos_terminados; ?></h3>
                <div class="text-center">
                  <span class="text-primary fs-14 me-2"><i class="mdi mdi-trending-up fs-14"></i> 12.8%</span>
                  <p class="text-dark fs-13 mb-0">Last 7 days</p>
                </div>
              </div>

            </div>
          </div>
        </div>
        <!--  <div class="card text-white bg-info mb-3">
          <div class="card-header"><i class="fa fa-check"></i> Pedidos Terminados</div>
          <div class="card-body">
            <h5 class="card-title"><?php echo $pedidos_terminados; ?></h5>
          </div>
        </div> -->
      </div>
      <!-- Pedidos Pendientes -->
      <div class="col-md-3 col-sm-6 col-12">
        <div class="card mb-0 ">
          <div class="card-body">
            <div class="widget-first">

              <div class="d-flex align-items-center mb-2">
                <div class="p-2 border border-warning border-opacity-10 bg-warning-subtle rounded-pill me-2">
                  <div class="bg-warning rounded-circle widget-size text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                      <path fill="#ffffff" d="M5.574 4.691c-.833.692-1.052 1.862-1.491 4.203l-.75 4c-.617 3.292-.926 4.938-.026 6.022C4.207 20 5.88 20 9.23 20h5.54c3.35 0 5.025 0 5.924-1.084c.9-1.084.591-2.73-.026-6.022l-.75-4c-.439-2.34-.658-3.511-1.491-4.203C17.593 4 16.403 4 14.02 4H9.98c-2.382 0-3.572 0-4.406.691" opacity="0.5" />
                      <path fill="#988D4D" d="M12 9.25a2.251 2.251 0 0 1-2.122-1.5a.75.75 0 1 0-1.414.5a3.751 3.751 0 0 0 7.073 0a.75.75 0 1 0-1.414-.5A2.251 2.251 0 0 1 12 9.25" />
                    </svg>
                  </div>
                </div>
                <p class="mb-0 text-dark fs-15">Pedidos Pendientes</p>
              </div>


              <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fs-22 text-black me-3"><?php echo $pedidos_pendientes; ?></h3>

                <div class="text-muted">
                  <span class="text-danger fs-14 me-2"><i class="mdi mdi-trending-down fs-14"></i> 18%</span>
                  <p class="text-dark fs-13 mb-0">Last 7 days</p>
                </div>
              </div>

            </div>
          </div>
        </div>
        <!-- <div class="card text-white bg-warning mb-3">
          <div class="card-header"><i class="fa fa-hourglass-half"></i> Pedidos Pendientes</div>
          <div class="card-body">
            <h5 class="card-title"><?php echo $pedidos_pendientes; ?></h5>
          </div>
        </div> -->
      </div>
    </div>

    <!-- Alerta si el mes es diferente -->
    <?php if ($alerta_mes_diferente): ?>
      <div class="alert alert-danger" role="alert">
        <strong>¡Atención!</strong> El mes actual es diferente al mes de facturación (<?php echo $fecha_facturacion; ?>). Por favor, revise el cambio de mes.
        <a href="<?php echo base_url('admin/config'); ?>" class="alert-link"><i class="fa fa-book"></i> Cambiar Mes Facturación</a>
      </div>
    <?php endif; ?>


    <!-- Sección con Pestañas -->
    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button" role="tab" aria-controls="pedidos" aria-selected="true">Pedidos</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="graficos-tab" data-bs-toggle="tab" data-bs-target="#graficos" type="button" role="tab" aria-controls="graficos" aria-selected="false">Gráficos</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="documentacion-tab" data-bs-toggle="tab" data-bs-target="#documentacion" type="button" role="tab" aria-controls="documentacion" aria-selected="false">Documentación OAuth2</button>
      </li>
    </ul>

    <div class="tab-content" id="dashboardTabsContent">
      <!-- Contenido de la Pestaña Pedidos -->
      <div class="tab-pane fade show active" id="pedidos" role="tabpanel" aria-labelledby="pedidos-tab">
        <!-- Pedidos Recientes -->
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">



                <h5>Pedidos Recientes y Documentación Asociada</h5>

              </div>
              <div class=" card-body">
                <table id="tablaPedidos" class="table table-striped nowrap" style="width:100%">
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
                  <tbody>
                    <?php if (!empty($pedidos)): ?>
                      <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                          <td>
                            <button class="btn btn-primary btn-sm ver-detalle"
                              data-pedido='<?php echo json_encode($pedido); ?>'>
                              Ver Detalle
                            </button>
                          </td>
                          <td>
                            <?php
                            // Lógica para determinar el estado del pedido
                            switch ($pedido['Estado']) {
                              case 1:
                                echo '<span class="badge bg-secondary">Información Creada</span>';
                                break;
                              case 2:
                                echo '<span class="badge bg-primary">Documento Enlazado</span>';
                                break;
                              case 3:
                                echo '<span class="badge bg-warning text-dark">Documentos Escaneados</span>';
                                break;
                              case 4:
                                echo '<span class="badge bg-info text-dark">Terminado</span>';
                                break;
                              case 5:
                                echo '<span class="badge bg-success">Fin del Proceso</span>';
                                break;
                              default:
                                echo '<span class="badge bg-light">Desconocido</span>';
                            }
                            ?>
                          </td>
                          <td><?php echo $pedido['PO']; ?></td>
                          <td><?php echo $pedido['Orden']; ?></td>
                          <td><?php echo $pedido['Tp']; ?></td>
                          <td><?php echo $pedido['FechaOC']; ?></td>
                          <td><?php echo $pedido['Cliente']; ?></td>
                          <td><?php echo $pedido['Nombre']; ?></td>
                          <td><?php echo $pedido['Guia']; ?></td>
                          <td><?php echo $pedido['Factura']; ?></td>
                          <td><?php echo $pedido['FechaF']; ?></td>
                          <td><?php echo $pedido['Ship']; ?></td>
                          <td><?php echo $pedido['Nombre2']; ?></td>
                          <td><?php echo $pedido['Direccion']; ?></td>
                          <td><?php echo $pedido['Comuna']; ?></td>
                          <td><?php echo $pedido['Region']; ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="16" class="text-center">No hay pedidos recientes.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Información Adicional -->
        <!-- Puedes agregar información adicional aquí si lo deseas -->

      </div>

      <!-- Contenido de la Pestaña Gráficos -->
      <div class="tab-pane fade" id="graficos" role="tabpanel" aria-labelledby="graficos-tab">
        <div class="row mt-4">
          <div class="col-md-12">
            <!-- <figure class="highcharts-figure">
              <div id="container"></div>
              <p class="highcharts-description">
                Gráfico de pedidos procesados por día en los últimos 30 días.
              </p>
            </figure> -->
            <div class="card-header">
              <h5 class="card-title mb-0">Zoomable Timeseries Chart</h5>
            </div>

            <div class="card-body">
              <div id="zoomable_line_chart" class="apex-charts"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido de la Pestaña Documentación OAuth2 -->
      <div class="tab-pane fade" id="documentacion" role="tabpanel" aria-labelledby="documentacion-tab">
        <!-- Requisitos de Implementación -->
        <div class="row mt-4">
          <div class="col-md-6">
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

          <!-- Autenticación OAuth 2.0 -->
          <div class="col-md-6">
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
        </div>

        <!-- Envío de Datos a la API -->
        <div class="row">
          <div class="col-md-12">
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
        </div>

        <!-- Consideraciones de Seguridad -->
        <div class="row">
          <div class="col-md-12">
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

      </div>

    </div>

    <!-- Modal para Ver Detalle -->
    <div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
          <form id="formDetalle">
            <div class="modal-header">
              <h5 class="modal-title" id="detalleModalLabel">Detalle del Pedido</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <!-- Contenido del detalle se carga dinámicamente -->
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Modal para mostrar imágenes en grande -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down">
    <div class="modal-content">
      <div class="modal-body p-0"> <!-- Elimina el padding para que la imagen ocupe todo el espacio -->
        <div id="panzoom-container" style="width: 100%; height: 100%; overflow: hidden;">
          <img src="" id="imageModalSrc" class="img-fluid zoomable" alt="Documento" style="cursor: grab; user-select: none;">
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Modal para Enlazar Documentos -->
<div class="modal fade" id="enlazarDocumentoModal" tabindex="-1" aria-labelledby="enlazarDocumentoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="enlazarDocumentoModalLabel">Buscar y Enlazar Documento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Formulario de búsqueda -->
        <div class="mb-3">
          <label for="searchTerm" class="form-label">Buscar Documentos</label>
          <input type="text" class="form-control" id="searchTerm" placeholder="Ingrese el nombre del documento">
        </div>
        <button type="button" class="btn btn-primary mb-3" id="btnBuscarDocumentos">Buscar</button>

        <!-- Tabla de resultados de búsqueda -->
        <table id="tablaBusquedaDocumentos" class="table table-striped table-bordered" style="width:100%">
          <thead>
            <tr>
              <th>Nombre del Archivo</th>
              <th>Fecha Subida</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- Resultados se cargarán dinámicamente -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script type="text/javascript">
  $(document).ready(function() {
    // Inicializar DataTables con idioma en español y botón de exportar a Excel
    var table = $('#tablaPedidos').DataTable({
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
      },
      "order": [
        [4, "desc"]
      ],
      "pageLength": 10,
      "responsive": true,
      dom: 'Bfrtip',
      buttons: [{
        extend: 'excelHtml5',
        text: 'Exportar a Excel',
        titleAttr: 'Exportar a Excel',
        className: 'btn btn-success'
      }]
    });

    // Ajuste para que los botones de exportación se vean correctamente en pantallas pequeñas
    table.buttons().container()
      .appendTo('#tablaPedidos_wrapper .col-md-6:eq(0)');

    // Manejar el clic en "Ver Detalle"
    $(document).on('click', '.ver-detalle', function() {
      var pedido = $(this).data('pedido');
      var pedidoId = pedido.id;

      $.ajax({
        url: '<?php echo site_url('admin/revisar_archivos/get_pedido_detalle'); ?>/' + pedidoId,
        method: 'GET',
        success: function(response) {
          $('#detalleModal .modal-body').html(response); // Cargar los datos en el modal
          $('#detalleModal').modal('show');

          // Verificar si hay documentos asociados

          // Mostrar el botón para buscar y enlazar documentos
          $('#detalleModal .modal-body').append('<button type="button" class="btn btn-secondary mt-3" id="btnBuscarYEnlazarDocumentos">Buscar y Enlazar Documentos</button>');

        }
      });
    });

    // Manejar el clic en "Guardar Cambios" en el detalle
    $('#formDetalle').on('submit', function(e) {
      e.preventDefault();
      // Aquí puedes agregar la lógica para guardar los datos
      Swal.fire('Éxito', 'Datos guardados correctamente.', 'success');
      $('#detalleModal').modal('hide');
    });

    // Manejar el clic en el botón "Buscar y Enlazar Documentos"
    $(document).on('click', '#btnBuscarYEnlazarDocumentos', function() {
      $('#enlazarDocumentoModal').modal('show');
    });

    // Manejar la búsqueda de documentos
    $('#btnBuscarDocumentos').on('click', function() {
      var query = $('#searchTerm').val().trim();

      $('#tablaBusquedaDocumentos').DataTable().destroy(); // Destruir instancia anterior

      $('#tablaBusquedaDocumentos').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
          "url": '<?php echo site_url('admin/dashboard/buscar_documentos'); ?>',
          "type": "GET",
          "data": {
            "query": query
          },
          "dataSrc": "data"
        },
        "columns": [{
            "data": "file_name"
          },
          {
            "data": "uploaded_at"
          },
          {
            "data": "id",
            "render": function(data, type, row) {
              return `<button class="btn btn-success btn-sm enlazar-documento" data-file-id="${data}">Enlazar</button>`;
            },
            "orderable": false,
            "searchable": false
          }
        ],
        "language": {
          "url": "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
      });
    });

   

    // Generar datos para el gráfico
    var pedidosPorDia = <?php echo json_encode($pedidos_por_dia); ?>;
    var dias = [];
    var cantidades = [];

    // Extraer datos del array
    pedidosPorDia.forEach(function(pedido) {
      dias.push(pedido.fecha);
      cantidades.push(parseInt(pedido.total));
    });

    const options = {
      chart: {
        height: 350,
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
        text: "Page Statistics",
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
                return e + " (mins)";
              },
            },
          },
          {
            title: {
              formatter: function(e) {
                return e + " per session";
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
      colors: ["#f59440", "#001b2f", "#287F71"],
      grid: {
        borderColor: "#f1f1f1"
      },
    };

    // Crear el gráfico después de definir las opciones
    const myChart = new ApexCharts(
      document.querySelector("#zoomable_line_chart"),
      options
    );
    myChart.render();

  });
</script>