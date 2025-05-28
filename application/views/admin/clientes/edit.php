<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>

            <div class="content-wrapper">
                <section class="content-header">
                    <?php echo $pagetitle; ?>
                    <?php echo $breadcrumb; ?>
                </section>

                <section class="content">
                    <div class="row">
                        <div class="col-md-12">
                             <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><?php echo lang('users_edit_user'); ?></h3>
                                </div>
                                <div class="box-body">
                                    <?php echo $message;?>

                                    <?php echo form_open(uri_string(), array('class' => 'form-horizontal', 'id' => 'form-edit_user')); ?>
                                    <div class="form-group">
                                        <label for="n_cliente" class="col-sm-2 control-label">Numero Cliente</label>
                                        <div class="col-sm-10">
                                            <?php echo form_input($n_cliente);?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="col-sm-2 control-label">Rut</label>
                                        <div class="col-sm-10">
                                            <?php echo form_input($rut_cliente);?>
                                        </div>
                                    </div>
                                        <div class="form-group">
                                            <?php echo lang('users_firstname', 'first_name', array('class' => 'col-sm-2 control-label')); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_input($nombre);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Apellido Paterno</label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($ap_paterno);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Apellido Materno</label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($ap_materno);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Razon Social</label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($razon_social);?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Giro</label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($giro);?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Dirección</label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($direccion);?>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Email</label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($email);?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Tipo Documento</label>
                                            <div class="col-sm-10">
                                              <?php
                                              echo form_dropdown('tipo_documento', $options, $shirts_on_sale,'class="form-control"');
                                               ?>
                                                <?php //echo form_input($tipo_documento);?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Teléfono</label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($telefono);?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone" class="col-sm-2 control-label">Saldo a Favor</label>
                                            <div class="col-sm-10">
                                                <?php echo form_input($saldo_a_favor);?>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <?php echo form_input($id);?>
                                                <?php echo form_hidden($csrf); ?>
                                                <div class="btn-group">
                                                  <div class="row">
                                                    <div class="col-md-6">
                                                      <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                      <?php echo anchor('admin/clientes', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                                    </div>
                                                  </div>



                                                </div>
                                            </div>
                                        </div>
                                    <?php echo form_close();?>
                                </div>
                            </div>
                         </div>
                    </div>
                </section>
            </div>
