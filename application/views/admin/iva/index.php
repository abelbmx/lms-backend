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
									<h3 style="display:none;" class="box-title"><?php echo anchor('admin/iva/create', '<i class="fa fa-plus"></i> '. lang('users_create_user'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
								</div>
								<div class="box-body">
									<table class="table table-striped table-hover" id="clientes">
										<thead>
											<tr>
												<th>ID</th>
												<th>IVA</th>
												<th>Fecha inicio</th>
												<th>Acciones</th>
											</tr>
										</thead>
										<tbody>
<?php foreach ($iva as $iv):?>
											<tr>

												<td><?php echo $iv->id; ?></td>
												<td><?php echo $iv->iva; ?>%</td>
												<td><?php echo $iv->fecha_inicio; ?></td>
												<td>
													<?php echo anchor('admin/iva/edit/'.$iv->id, '<span class="label label-success">'.lang('actions_edit').'</span>'); ?>&nbsp;
													<?php echo anchor('admin/iva/profile/'.$iv->id, '<span class="label label-primary">'.lang('actions_see').'</span>'); ?>
												</td>
											</tr>
<?php endforeach;?>
										</tbody>
									</table>
								</div>
							</div>
						 </div>
					</div>
				</section>
			</div>
