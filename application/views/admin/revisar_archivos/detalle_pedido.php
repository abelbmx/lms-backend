<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
  /* Tercera pestaña: listar vertical */
  #detalleModal #navpills2-descargaDocs .list-group {
    display: flex;
    flex-direction: column;
  }

  #detalleModal #navpills2-descargaDocs .list-group-item {
    margin-bottom: 1rem;
  }
</style>

<div class="row">
  <ul class="nav nav-pills px-2" id="navpills2" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link active" data-bs-toggle="tab" href="#navpills2-infopedido" role="tab">
        <span class="d-block d-sm-none"><i class="mdi mdi-home-account"></i></span>
        <span class="d-none d-sm-block">Información del Pedido</span>
      </a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" data-bs-toggle="tab" href="#navpills2-datosfaltantes" role="tab">
        <span class="d-block d-sm-none"><i class="mdi mdi-account-outline"></i></span>
        <span class="d-none d-sm-block">Completar Datos Faltantes</span>
      </a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" data-bs-toggle="tab" href="#navpills2-descargaDocs" role="tab">
        <span class="d-block d-sm-none"><i class="mdi mdi-download"></i></span>
        <span class="d-none d-sm-block">Descargar Documentos</span>
      </a>
    </li>
  </ul>

  <div class="tab-content p-2 text-muted">
    <!-- Información general del pedido -->
    <div class="tab-pane active show" id="navpills2-infopedido" role="tabpanel">
      <div class="row">
        <div class="col-lg-6 col-md-12">
          <ul class="list-group list-group-flush" data-pedido-id="<?php echo $pedido->id; ?>">
            <li class="list-group-item"><strong>PO:</strong> <?php echo $pedido->PO; ?></li>
            <li class="list-group-item"><strong>Orden:</strong> <?php echo $pedido->Orden; ?></li>
            <li class="list-group-item"><strong>Tp:</strong> <?php echo $pedido->Tp; ?></li>
            <li class="list-group-item"><strong>Fecha OC:</strong> <?php echo $pedido->FechaOC; ?></li>
            <li class="list-group-item"><strong>Cliente:</strong> <?php echo $pedido->Cliente; ?></li>
            <li class="list-group-item"><strong>Nombre:</strong> <?php echo $pedido->Nombre; ?></li>
            <li class="list-group-item"><strong>Guía:</strong> <?php echo $pedido->Guia; ?></li>
            <li class="list-group-item"><strong>Factura:</strong> <?php echo $pedido->Factura; ?></li>
            <li class="list-group-item"><strong>Fecha Factura:</strong> <?php echo $pedido->FechaF; ?></li>
            <li class="list-group-item"><strong>Ship:</strong> <?php echo $pedido->Ship; ?></li>
            <li class="list-group-item"><strong>Nombre2:</strong> <?php echo $pedido->Nombre2; ?></li>
            <li class="list-group-item"><strong>Dirección:</strong> <?php echo $pedido->Direccion; ?></li>
            <li class="list-group-item"><strong>Comuna:</strong> <?php echo $pedido->Comuna; ?></li>
            <li class="list-group-item"><strong>Región:</strong> <?php echo $pedido->Region; ?></li>
            <li class="list-group-item"><strong>Estado:</strong>
              <?php
              switch ($pedido->Estado) {
                case 1:
                  echo '<span class="badge bg-secondary rounded-pill">Información Creada</span>';
                  break;
                case 2:
                  echo '<span class="badge bg-primary rounded-pill">Documento Enlazado</span>';
                  break;
                case 3:
                  echo '<span class="badge bg-warning rounded-pill text-dark">Documentos Escaneados</span>';
                  break;
                case 4:
                  echo '<span class="badge bg-info rounded-pill text-dark">Terminado</span>';
                  break;
                case 5:
                  echo '<span class="badge bg-success rounded-pill">Fin del Proceso</span>';
                  break;
                default:
                  echo '<span class="badge bg-light rounded-pill">Desconocido</span>';
              }
              ?>
            </li>
          </ul>
        </div>

        <!-- Visualización de Documentos -->
        <div class="col-lg-6 col-md-12">
          <h5 class="fw-bold">Documentos Asociados (Total: <?php echo count($documentos); ?>)</h5>

          <br>
          <div id="documentoContainer" class="overflow-auto" style="max-height:570px;">
            <?php if (!empty($documentos)): ?>
              <?php foreach ($documentos as $doc): ?>
                <?php $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)); ?>
                <div class="mb-3">
                  <?php if ($ext === 'pdf'): ?>
                    <embed src="<?php echo base_url($doc->file_path); ?>#toolbar=0&navpanes=0" type="application/pdf" width="100%" height="600px" />
                    <br>
                    <a href="<?php echo base_url($doc->file_path); ?>" class="btn btn-primary btn-sm me-2" download>
                      <span class="mdi mdi-tray-arrow-down"></span> Descargar PDF
                    </a>
                    <a href="<?php echo base_url($doc->file_path); ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                      <span class="mdi mdi-open-in-new"></span> Ver en pestaña
                    </a>
                  <?php else: ?>
                    <img src="<?php echo base_url($doc->file_path); ?>" alt="Documento" class="img-fluid zoomable mb-3" style="max-width:550px;" />
                    <br>
                    <a href="<?php echo base_url($doc->file_path); ?>" class="btn btn-primary btn-sm mb-3 me-2" download>
                      <span class="mdi mdi-tray-arrow-down px-1"></span> Descargar Archivo
                    </a>
                    <a href="<?php echo base_url($doc->file_path); ?>" class="btn btn-outline-secondary btn-sm mb-3" target="_blank">
                      <span class="mdi mdi-open-in-new"></span> Ver en pestaña
                    </a>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No hay documentos asociados a este pedido.</p>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>

    <!-- Formulario para Completar Datos Faltantes -->
    <div class="tab-pane" id="navpills2-datosfaltantes" role="tabpanel">
      <div class="row">
        <div class="col-12">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="nombreChofer" class="form-label">Nombre Chofer</label>
                <input type="text" class="form-control" id="nombreChofer" name="nombreChofer">
              </div>
              <div class="mb-3">
                <label for="patente" class="form-label">Patente</label>
                <input type="text" class="form-control" id="patente" name="patente">
              </div>
              <div class="mb-3">
                <label for="recepcionista" class="form-label">Recepcionista</label>
                <input type="text" class="form-control" id="recepcionista" name="recepcionista">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="rut" class="form-label">RUT</label>
                <input type="text" class="form-control" id="rut" name="rut">
              </div>
              <div class="mb-3">
                <label for="entregadoConforme" class="form-label">Entregado Conforme</label>
                <select class="form-control" id="entregadoConforme" name="entregadoConforme">
                  <option value="">Seleccione</option>
                  <option value="Sí">Sí</option>
                  <option value="No">No</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="fechaEntrega" class="form-label">Fecha de Entrega</label>
                <input type="date" class="form-control" id="fechaEntrega" name="fechaEntrega">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="documentos" class="form-label">Subir Documentos (Guía o Factura)</label>
            <input type="file" class="form-control" id="documentos" name="documentos[]" multiple>
          </div>
        </div>
      </div>
    </div>

    <!-- Descargar Documentos -->
    <div class="tab-pane" id="navpills2-descargaDocs" role="tabpanel">
      <h5>Documentos Asociados (Total: <?php echo count($documentos); ?>)</h5>
      <ul class="list-group list-group-flush">
        <?php if (!empty($documentos)): ?>
          <?php foreach ($documentos as $doc): ?>
            <?php $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)); ?>
            <li class="list-group-item">
              <?php if ($ext === 'pdf'): ?>
                <embed src="<?php echo base_url($doc->file_path); ?>" type="application/pdf" width="100%" height="900px" />
                <div class="mt-2">
                  <a href="<?php echo base_url($doc->file_path); ?>" class="btn btn-primary btn-sm me-2" download>
                    <span class="mdi mdi-tray-arrow-down"></span> Descargar PDF
                  </a>
                  <a href="<?php echo base_url($doc->file_path); ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <span class="mdi mdi-open-in-new"></span> Ver en pestaña
                  </a>
                </div>
              <?php else: ?>
                <img src="<?php echo base_url($doc->file_path); ?>" class="img-thumbnail mb-2" style="max-width:550px;" alt="Documento" />
                <div class="mt-2">
                  <a href="<?php echo base_url($doc->file_path); ?>" class="btn btn-primary btn-sm me-2" download>
                    <span class="mdi mdi-tray-arrow-down"></span> Descargar Archivo
                  </a>
                  <a href="<?php echo base_url($doc->file_path); ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <span class="mdi mdi-open-in-new"></span> Ver en pestaña
                  </a>
                </div>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="list-group-item">No hay documentos asociados a este pedido.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    // Inicializar Panzoom cuando se abre el modal
    $('#imageModal').on('shown.bs.modal', function() {
      var panzoomInstance = Panzoom(document.getElementById('panzoom-container'), {
        maxScale: 5,
        minScale: 1,
        contain: 'outside',
        startScale: 1,
        step: 0.5,
      });
      var parent = document.getElementById('panzoom-container');
      parent.parentElement.addEventListener('wheel', panzoomInstance.zoomWithWheel);
    });
    // Limpiar Panzoom cuando se cierra el modal
    $('#imageModal').on('hidden.bs.modal', function() {
      var panzoomInstance = Panzoom(document.getElementById('panzoom-container'));
    });
    // Manejar clic en imágenes zoomable
    $(document).on('click', '.zoomable', function() {
      var src = $(this).attr('src');
      $('#imageModalSrc').attr('src', src);
      $('#imageModal').modal('show');
    });
  });
</script>
