<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="card csv-card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h2 class="card-title h5 mb-0">Cargar archivo CSV</h2>
    </div>
    <div class="card-body">

        <!-- ALERTAS FLASH -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php elseif ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('admin/orders/upload') ?>" method="post" enctype="multipart/form-data" class="mb-4">
            <div class="form-group mb-3">
                <label for="csvFile" class="fw-semibold">Seleccione un archivo CSV:</label>
                <input type="file" name="csv_file" id="csvFile" class="form-control-file" accept=".csv" required>
            </div>
            <button type="submit" id="uploadBtn" class="btn btn-success" disabled>
                <i class="mdi mdi-upload"></i> Subir y procesar
            </button>
        </form>

        <h5 class="fw-semibold">Vista previa de los datos CSV:</h5>
        <div class="table-responsive position-relative mt-3">
            <div id="loadingSpinner" class="d-none spinner-overlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
            </div>
            <table id="previewTable" class="table table-striped table-bordered w-100">
                <!-- contenido generado dinámicamente -->
            </table>
        </div>
    </div>
</div>

<style>
    .spinner-overlay {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.6);
        z-index: 9999;
    }
</style>

<!-- Librerías JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js" integrity="sha512-xX6ZxcCxVM0...==" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script>
    $(function() {
        const colsToUpload = [
            'PO', 'Orden', 'Tp', 'Fecha OC', 'Cliente', 'Nombre',
            'Guia', 'Factura', 'Fecha F', 'Ship', 'Nombre2', 'Direccion', 'Comuna', 'Region'
        ];

        $('#csvFile').on('change', function(e) {
            $('#loadingSpinner').removeClass('d-none');
            const file = e.target.files[0];
            if (!file) return;
            $('#uploadBtn').prop('disabled', false);

            Papa.parse(file, {
                skipEmptyLines: true,
                complete: function(results) {
                    const data = results.data;
                    if (!data || data.length < 2) {
                        $('#loadingSpinner').addClass('d-none');
                        return;
                    }

                    const headers = data[0];
                    const rows = data.slice(1);
                    const uploadIndexes = headers
                        .map((h, i) => colsToUpload.includes(h) ? i : -1)
                        .filter(i => i >= 0);

                    let thead = '<thead class="table-dark"><tr>';
                    headers.forEach(h => {
                        const cls = colsToUpload.includes(h) ? ' table-warning' : '';
                        thead += `<th class="align-middle${cls}">${h}</th>`;
                    });
                    thead += '</tr></thead>';

                    let tbody = '<tbody>';
                    rows.forEach(r => {
                        tbody += '<tr>';
                        r.forEach((c, ci) => {
                            const cls = uploadIndexes.includes(ci) ? ' table-warning' : '';
                            tbody += `<td class="${cls}">${c ?? ''}</td>`;
                        });
                        tbody += '</tr>';
                    });
                    tbody += '</tbody>';

                    if ($.fn.DataTable.isDataTable('#previewTable')) {
                        $('#previewTable').DataTable().clear().destroy();
                    }
                    $('#previewTable').html(thead + tbody);

                    $('#previewTable').DataTable({
                        paging: true,
                        lengthChange: false,
                        pageLength: 20,
                        searching: false,
                        ordering: true,
                        info: false,
                        processing: true,
                        language: {
                            url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/Spanish.json",
                            processing: "Cargando..."
                        }
                    });

                    $('#loadingSpinner').addClass('d-none');
                }
            });
        });
    });
</script>
