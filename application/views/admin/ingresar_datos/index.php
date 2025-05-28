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
        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-success">
                <?php echo $this->session->flashdata('message'); ?>
            </div>
        <?php endif; ?>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>


        <div class="col">
            <div class="card d-block">
                <h5 class="card-header">Pedidos Recientes y Documentación Asociada
                </h5>
                <div class="card-body">
                    <?php echo form_open('admin/ingresar_datos/guardar'); ?>

                    <div class="container-fluid">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="po">PO</label>
                                    <input type="text" class="form-control" id="po" name="po" value="<?php echo set_value('po'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="orden">Orden</label>
                                    <input type="text" class="form-control" id="orden" name="orden" value="<?php echo set_value('orden'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tp">Tp</label>
                                    <input type="text" class="form-control" id="tp" name="tp" value="<?php echo set_value('tp'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fecha_oc">Fecha OC</label>
                                    <input type="date" class="form-control" id="fecha_oc" name="fecha_oc" value="<?php echo set_value('fecha_oc'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="cliente">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" name="cliente" value="<?php echo set_value('cliente'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo set_value('nombre'); ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="region">Región</label>
                                    <select class="form-select" id="region" name="region" required>
                                        <option value="" selected>Seleccione una región</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="guia">Guía</label>
                                    <input type="text" class="form-control" id="guia" name="guia" value="<?php echo set_value('guia'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="factura">Factura</label>
                                    <input type="text" class="form-control" id="factura" name="factura" value="<?php echo set_value('factura'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fecha_factura">Fecha Factura</label>
                                    <input type="date" class="form-control" id="fecha_factura" name="fecha_factura" value="<?php echo set_value('fecha_factura'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ship">Ship</label>
                                    <input type="text" class="form-control" id="ship" name="ship" value="<?php echo set_value('ship'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nombre2">Nombre2</label>
                                    <input type="text" class="form-control" id="nombre2" name="nombre2" value="<?php echo set_value('nombre2'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="direccion">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo set_value('direccion'); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="comuna">Comuna</label>
                                    <select class="form-select" id="comuna" name="comuna" required>
                                        <option value="" selected>Seleccione una comuna</option>
                                    </select>
                                </div>



                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary mt-3"> <span class="mdi mdi-content-save me-1"></span>Guardar</button>
                    </div>

                    <?php echo form_close(); ?>
                </div>
            </div>
    </section>
</div>

<script>
    $(document).ready(function() {


        // URL de la API proporcionada en una variable JS.
        const apiUrl = '<?php echo base_url('api/comunas_regiones'); ?>';

        let regionId = $('#region');
        let comunaId = $('#comuna');
        let regionsData = []; // Almacena las regiones para su uso posterior

        if (regionId.length) {
            $.ajax({
                url: apiUrl,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log(response);

                    // Guardar las regiones en regionsData
                    regionsData = response.regions;

                    // Iterar sobre las regiones y agregarlas al select de regiones
                    regionsData.forEach(function(region) {
                        let option = $('<option></option>')
                            .attr('value', region.id)
                            .text(region.name);
                        regionId.append(option);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        } else {
            console.error('Error: Region element not found');
        }

        // Actualizar el select de comunas al cambiar la región seleccionada
        regionId.on('change', function() {
            let selectedRegionId = $(this).val(); // Obtener el ID de la región seleccionada

            // Limpiar las opciones anteriores de comunas
            comunaId.empty();
            comunaId.append('<option value="" selected>Seleccione una comuna</option>');

            // Buscar la región seleccionada en regionsData
            let selectedRegion = regionsData.find(region => region.id === selectedRegionId);

            // Si se encuentra la región, iterar sobre sus comunas y agregarlas al select de comunas
            if (selectedRegion && selectedRegion.communes) {
                selectedRegion.communes.forEach(function(comuna) {
                    let option = $('<option></option>')
                        .attr('value', comuna.id)
                        .text(comuna.name);
                    comunaId.append(option);
                });
            }
        });
    });
</script>

