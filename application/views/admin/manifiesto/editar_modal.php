<!-- application/views/admin/manifiesto/editar_modal.php -->
<div class="modal fade" id="editarManifiestoModal" tabindex="-1" aria-labelledby="editarManifiestoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditarManifiesto">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarManifiestoModalLabel">Editar Manifiesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $manifiesto->id; ?>">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($manifiesto->nombre); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="conductor" class="form-label">Conductor</label>
                        <input type="text" name="conductor" class="form-control" value="<?php echo htmlspecialchars($manifiesto->conductor); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="camion" class="form-label">Camión</label>
                        <input type="text" name="camion" class="form-control" value="<?php echo htmlspecialchars($manifiesto->camion); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="number" name="telefono" class="form-control" value="<?php echo htmlspecialchars($manifiesto->telefono); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="Activo" <?php echo ($manifiesto->estado == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="Inactivo" <?php echo ($manifiesto->estado == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                            <option value="Entregado" <?php echo ($manifiesto->estado == 'Entregado') ? 'selected' : ''; ?>>Entregado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#formEditarManifiesto').submit(function(e) {
        e.preventDefault();
        var id = $('input[name="id"]').val();
        $.ajax({
            url: '<?php echo site_url("admin/manifiestos/editar/"); ?>' + id,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert('Manifiesto actualizado exitosamente.');
                    $('#editarManifiestoModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error al actualizar el manifiesto.');
                }
            },
            error: function() {
                alert('Error en la solicitud.');
            }
        });
    });
</script>
