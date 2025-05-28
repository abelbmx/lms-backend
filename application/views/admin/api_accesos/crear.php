<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <!-- Mensajes de éxito o error -->
        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-success">
                <?php echo $this->session->flashdata('message'); ?>
                <?php if (isset($new_client)): ?>
                    <hr>
                    <h5>Credenciales Generadas:</h5>
                    <p><strong>Client ID:</strong> <?php echo $new_client['client_id']; ?></p>
                    <p><strong>Client Secret:</strong> <?php echo $new_client['client_secret']; ?></p>
                    <p><strong>Redirect URI:</strong> <?php echo set_value('redirect_uri'); ?></p>
                    <p><strong>Scope:</strong> <?php echo set_value('scope') ? set_value('scope') : 'api1'; ?></p>
                    <p><em>Por favor, copia estas credenciales ahora ya que no se mostrarán nuevamente.</em></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <!-- Formulario para crear acceso API -->
        <div class="card">
            <div class="card-header">
                <h3>Crear Nuevo Acceso API</h3>
            </div>
            <div class="card-body">
                <?php echo form_open('admin/api_accesos/crear'); ?>

                <div class="form-group">
                    <label for="redirect_uri">Redirect URI</label>
                    <input type="url" class="form-control" id="redirect_uri" name="redirect_uri" placeholder="https://tuapp.com/callback" value="<?php echo set_value('redirect_uri'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="scope">Scope</label>
                    <input type="text" class="form-control" id="scope" name="scope" placeholder="api1" value="<?php echo set_value('scope'); ?>">
                    <small class="form-text text-muted">Define el alcance del acceso. Por defecto es "api1".</small>
                </div>

                <button type="submit" class="btn btn-success mt-3">Crear Acceso API</button>

                <?php echo form_close(); ?>
            </div>
        </div>
    </section>
</div>
