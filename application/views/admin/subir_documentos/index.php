<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

<style>
    .tab-pane>.card>.card-body {
        padding-bottom: 200px
    }

    .upload-panel {
        background: #fefefe;
        border: 2px dashed #4caf50;
        border-radius: 8px;
        padding: 32px 24px;
        margin-bottom: 32px
    }

    .upload-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 18px;
        align-items: center
    }

    .po-free-wrap {
        background: #fff7e6;
        border: 1px solid #ffecb5;
        border-radius: 6px;
        padding: 10px 14px;
        display: inline-flex;
        align-items: center
    }

    .po-free-wrap .form-check-input {
        transform: scale(1.25);
        margin-right: 10px
    }

    .po-free-wrap label {
        margin: 0;
        font-weight: 600
    }

    .btn-toolbar {
        background: #8e336e;
        border: none;
        color: #fff;
        font-weight: 600
    }

    .btn-toolbar:hover {
        background: #7c2d60
    }

    .btn-upload {
        background: #2e7d32;
        border-color: #2e7d32;
        font-weight: 600;
        color: #fff
    }

    .btn-upload:hover {
        background: #279128;
        border-color: #279128;
        color: #fff
    }

    #bulkType {
        min-width: 270px
    }

    .dropzone {
        min-height: 200px;
        background: #fafafa;
        border: 2px dashed #adb5bd;
        border-radius: 8px;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch
    }

    .dz-message {
        text-align: center
    }

    .dz-preview {
        width: 100%;
        margin-bottom: 1rem;
        box-sizing: border-box
    }

    .dz-preview .mdi {
        font-size: 32px;
        color: #6c5ce7
    }

    .dz-preview select,
    .dz-preview input[type=checkbox] {
        margin-bottom: 4px
    }

    .dz-remove {
        margin-top: 6px;
        background: #dc3545;
        color: #fff;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: .8rem;
        text-decoration: none
    }

    .dz-remove:hover {
        background: #b52a37;
        color: #fff
    }

    table.dataTable tbody tr.selected {
        background: #f1f3f5
    }

    .dz-preview .dz-success-mark,
    .dz-preview .dz-error-mark {
        display: none !important
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
    var csrfName = '<?= $this->security->get_csrf_token_name() ?>',
        csrfHash = '<?= $this->security->get_csrf_hash() ?>';
</script>

<div class="container-fluid h-100 overflow-auto px-2">
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="uploadToast" class="toast" role="alert" data-bs-delay="3000">
            <div class="toast-header"><strong class="me-auto">Subida</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>
            <div class="toast-body"></div>
        </div>
    </div>

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h5 class="fs-5 fw-semibold m-0"><?= $pagetitle ?></h5>
        </div>
        <div class="text-end"><?= $breadcrumb ?></div>
    </div>

    <ul class="nav nav-tabs mb-3">
        <!-- NUEVO TAB DE CARGA MASIVA -->
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-bulk">Carga Masiva</a>
        </li>
        <!-- TABS EXISTENTES -->
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-upload">Subir Documentos</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-list">Mis Documentos</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-unlinked">Enlazar Documentos</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-assign">Asignar Tipo</a></li>
    </ul>

    <div class="tab-content">
        <!-- CONTENIDO CARGA MASIVA -->
        <div class="tab-pane fade show active" id="tab-bulk">
            <div class="card">
                <div class="card-body">
                    <!-- flash messages -->
                    <?php if ($this->session->flashdata('message')): ?>
                        <div class="alert alert-success">
                            <?= $this->session->flashdata('message') ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= $this->session->flashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <h6 class="mb-3">Carga Masiva de Archivos según patrón de nombre</h6>
                    <form id="frmBulkUpload" action="<?= site_url('admin/subir_documentos/upload_auto') ?>"
                        method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="bulkFiles" class="form-label">Selecciona los archivos</label>
                            <input type="file" name="documento[]" id="bulkFiles" multiple class="form-control" />
                        </div>
                        <button type="submit" class="btn btn-upload">
                            <i class="mdi mdi-cloud-upload"></i> Subir Masivo
                        </button>
                    </form>
                </div>
            </div>
        </div>


        <div class="tab-pane fade" id="tab-upload">
            <div class="card">
                <div class="card-body">
                    <?php if ($this->session->flashdata('message')): ?><div class="alert alert-success"><?= $this->session->flashdata('message') ?></div><?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div><?php endif; ?>

                    <div class="upload-panel">
                        <div class="upload-toolbar">
                            <div class="po-free-wrap">
                                <input type="checkbox" class="form-check-input" id="without_po" name="without_po" value="1">
                                <label for="without_po">Subir sin PO asociada</label>
                            </div>
                            <button type="button" class="btn btn-toolbar" id="btnBuscarOC"><i class="mdi mdi-magnify"></i> Seleccionar PO</button>
                            <div><span class="fw-semibold me-1">PO:</span><span id="labelPO" class="fw-bold">—</span></div>
                            <select id="bulkType" class="form-select">
                                <option value="">Tipo de documento (todos)</option>
                                <option value="OC">OC – Orden de Compra</option>
                                <option value="E1">E1 – Factura electrónica…</option>
                                <option value="E6">E6 – Factura Electrónica…</option>
                                <option value="R1">R1 – Guía Traslado…</option>
                                <option value="F8">F8 – Boleta electrónica…</option>
                                <option value="FM">FM – Fulfillment MercadoLibre</option>
                                <option value="CM">CM – Fulfillment</option>
                                <option value="S1">S1 – Guía Emitida…</option>
                                <option value="D">D – Nota de Débito</option>
                                <option value="C">C – Factura de Crédito</option>
                                <option value="E3">E3 – Factura Exportación</option>
                                <option value="X">X – Factura Exenta…</option>
                            </select>
                            <button id="applyBulkType" class="btn btn-toolbar"><i class="mdi mdi-check-all"></i> Aplicar</button>
                            <button id="changeAllType" class="btn btn-toolbar"><i class="mdi mdi-select-all"></i> Cambiar</button>
                            <button id="btnEnviar" class="btn btn-upload" style="display:none"><i class="mdi mdi-cloud-upload"></i> Subir Todo</button>
                        </div>

                        <form id="frmUpload" action="<?= site_url('admin/subir_documentos/upload') ?>" class="dropzone mt-4" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="order_id" id="order_id">
                            <div class="dz-message dz-placeholder w-100"><i class="mdi mdi-upload"></i><br>Arrastra archivos aquí o haz clic para buscarlos</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-list">
            <div class="card">
                <div class="card-body">
                    <table id="docsTable" class="table table-striped nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>PO</th>
                                <th>Orden</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Archivo</th>
                                <th>Subido</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-unlinked">
            <div class="card">
                <div class="card-body">
                    <form id="frmLink" action="<?= site_url('admin/subir_documentos/link_documents') ?>" method="post">
                        <input type="hidden" name="order_id" id="link_order_id">
                        <div class="d-flex mb-3">
                            <button type="button" class="btn btn-toolbar me-2" id="btnBuscarOCLink"><i class="mdi mdi-magnify"></i> Seleccionar PO</button>
                            <div><span class="fw-semibold me-1">PO:</span><span id="labelLinkPO" class="fw-bold">—</span></div>
                        </div>
                        <table id="unlinkedTable" class="table table-striped nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllUnlinked"></th>
                                    <th>Archivo</th>
                                    <th>Tipo</th>
                                    <th>Subido</th>
                                </tr>
                            </thead>
                        </table>
                        <button class="btn btn-upload mt-3"><i class="mdi mdi-link-variant"></i> Enlazar Seleccionados</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-assign">
            <div class="card">
                <div class="card-body">
                    <form id="frmAssign" action="<?= site_url('admin/subir_documentos/assign_types') ?>" method="post">
                        <table id="notypedTable" class="table table-striped nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllNotyped"></th>
                                    <th>Archivo</th>
                                    <th>Nuevo Tipo</th>
                                </tr>
                            </thead>
                        </table>
                        <button class="btn btn-upload mt-3"><i class="mdi mdi-tag-multiple"></i> Guardar Tipos</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalOC" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccione PO / Cliente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col"><input id="filterPO" class="form-control" placeholder="Filtrar PO"></div>
                    <div class="col"><input id="filterSO" class="form-control" placeholder="Filtrar SO"></div>
                    <div class="col"><input id="filterCliente" class="form-control" placeholder="Filtrar Cliente"></div>
                </div>
                <table id="tablaOC" class="table table-striped nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Orden</th>
                            <th>Cliente</th>
                            <th>Fecha PO</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    Dropzone.autoDiscover = false;
    $(function() {
        var ocTable = $('#tablaOC').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: '<?= site_url('admin/subir_documentos/orders_ajax') ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                },
                dataSrc: ''
            },
            columns: [{
                    data: 0
                },
                {
                    data: 1
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4,
                    orderable: false
                }
            ],
            lengthMenu: [
                [10, 25, 50],
                [10, 25, 50]
            ],
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            }
        });

        $('#filterPO').on('keyup', function() {
            ocTable.column(0).search(this.value).draw();
        });
        $('#filterSO').on('keyup', function() {
            ocTable.column(1).search(this.value).draw();
        });
        $('#filterCliente').on('keyup', function() {
            ocTable.column(2).search(this.value).draw();
        });
        $('#btnBuscarOC,#btnBuscarOCLink').click(function() {
            $('#modalOC').modal('show');
        });
        $('#tablaOC').on('click', '.btn-select-oc', function() {
            $('#order_id,#link_order_id').val($(this).data('id'));
            $('#labelPO,#labelLinkPO').text($(this).data('po'));
            $('#modalOC').modal('hide');
        });

        var docsTable = $('#docsTable').DataTable({
            serverSide: true,
            processing: true,
            responsive: true,
            autoWidth: true,
            ajax: {
                url: '<?= site_url('admin/subir_documentos/documents_ajax') ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                },
                dataSrc: 'data' // <-- aquí
            },
            columns: [{
                    data: 0
                },
                {
                    data: 1
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4
                },
                {
                    data: 5
                },
                {
                    data: 6,
                    orderable: false
                }
            ],
            lengthMenu: [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            pageLength: 20,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            }
        });


        var unlinkedTable = $('#unlinkedTable').DataTable({
            serverSide: false,
            processing: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: '<?= site_url('admin/subir_documentos/unlinked_ajax') ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                },
                dataSrc: 'data'
            },
            columns: [{
                    data: 0,
                    orderable: false
                },
                {
                    data: 1
                }, {
                    data: 2
                }, {
                    data: 3
                }
            ],
            lengthMenu: [
                [10, 25, 50],
                [10, 25, 50]
            ],
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug‑ins/1.11.5/i18n/es‑ES.json'
            }
        });
        $('#selectAllUnlinked').change(function() {
            $('#unlinkedTable input[name="doc_ids[]"]').prop('checked', this.checked)
        });

        var notypedTable = $('#notypedTable').DataTable({
            serverSide: false,
            processing: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: '<?= site_url('admin/subir_documentos/notyped_ajax') ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                },
                dataSrc: 'data'
            },
            columns: [{
                    data: 0,
                    orderable: false
                },
                {
                    data: 1
                }, {
                    data: 2
                }
            ],
            lengthMenu: [
                [10, 25, 50],
                [10, 25, 50]
            ],
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug‑ins/1.11.5/i18n/es‑ES.json'
            }
        });
        $('#selectAllNotyped').change(function() {
            $('#notypedTable input[name="doc_ids[]"]').prop('checked', this.checked)
        });

        var uploadToast = new bootstrap.Toast($('#uploadToast')[0]);
        var dz = new Dropzone('#frmUpload', {
            url: $('#frmUpload').attr('action'),
            paramName: 'documento[]',
            uploadMultiple: true,
            parallelUploads: 10,
            autoProcessQueue: false,
            addRemoveLinks: true,
            dictRemoveFile: 'Eliminar archivo',
            init: function() {
                this.on('addedfile', file => {
                    $('#btnEnviar').show();
                    var icon = document.createElement('i');
                    icon.className = 'mdi mdi-file-outline mdi-36px text-secondary mb-2';
                    file.previewElement.insertBefore(icon, file.previewElement.firstChild);
                    var wrap = document.createElement('div');
                    wrap.className = 'd-flex align-items-center mb-1';
                    var chk = document.createElement('input');
                    chk.type = 'checkbox';
                    chk.checked = true;
                    chk.className = 'dz-select me-2';
                    var sel = document.createElement('select');
                    sel.className = 'form-select form-select-sm';
                    var types = [
                        ['OC', 'OC – Orden de Compra'],
                        ['E1', 'E1 – Factura electrónica…'],
                        ['E6', 'E6 – Factura Electrónica…'],
                        ['R1', 'R1 – Guía Traslado…'],
                        ['F8', 'F8 – Boleta electrónica…'],
                        ['FM', 'FM – Fulfillment MercadoLibre'],
                        ['CM', 'CM – Fulfillment'],
                        ['S1', 'S1 – Guía Emitida…'],
                        ['D', 'D – Nota de Débito'],
                        ['C', 'C – Factura de Crédito'],
                        ['E3', 'E3 – Factura Exportación'],
                        ['X', 'X – Factura Exenta…']
                    ];
                    types.forEach(t => sel.add(new Option(t[1], t[0])));
                    var prefix = file.name.substring(0, 2).toUpperCase();
                    var codes = types.map(t => t[0]);
                    if (codes.includes(prefix)) sel.value = prefix;
                    wrap.append(chk, sel);
                    file.previewElement.appendChild(wrap);
                });
                this.on('removedfile', () => {
                    if (!this.files.length) $('#btnEnviar').hide();
                });
                this.on('sendingmultiple', (files, xhr, fd) => {
                    fd.append('without_po', $('#without_po').is(':checked') ? 1 : 0);
                    fd.append('order_id', $('#order_id').val());
                    files.forEach((file, i) => {
                        var sel = file.previewElement.querySelector('select');
                        fd.append('document_type[' + i + ']', sel.value);
                    });
                });
                this.on('successmultiple', () => {
                    $('.toast-body').text('Documentos subidos correctamente.');
                    uploadToast.show();
                    this.removeAllFiles();
                    $('#btnEnviar').hide();
                    docsTable.ajax.reload();
                    unlinkedTable.ajax.reload();
                    notypedTable.ajax.reload();
                });
                this.on('errormultiple', () => alert('Error al subir archivos.'));
            }
        });
        $('#btnEnviar').click(function(e) {
            e.preventDefault();
            if (!$('#order_id').val() && !$('#without_po').is(':checked')) {
                alert('Debe seleccionar PO o marcar "sin PO asociada".');
                return;
            }
            dz.processQueue();
        });
        $('#applyBulkType').click(() => {
            var v = $('#bulkType').val();
            dz.files.forEach(f => {
                var c = f.previewElement.querySelector('.dz-select');
                if (c.checked) f.previewElement.querySelector('select').value = v;
            });
        });
        $('#changeAllType').click(() => {
            var v = $('#bulkType').val();
            dz.files.forEach(f => f.previewElement.querySelector('select').value = v);
        });
    });
</script>
