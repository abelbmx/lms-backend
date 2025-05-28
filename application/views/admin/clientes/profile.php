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
						<div class="col-md-6">
							 <div class="box">
								<div class="box-header with-border">
									<h3 class="box-title">Datos Cliente</h3>
								</div>
								<div class="box-body">
									<table class="table table-striped table-hover">
										<tbody>
											<?php foreach ($user_info as $user):?>
											<tr>
												<th>Rut</th>
												<td><?php echo $user->rut_cliente; ?></td>
											</tr>
											<tr>
												<th>Nombre</th>
												<td><?php echo htmlspecialchars($user->nombre, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Apellido Paterno</th>
												<td><?php echo htmlspecialchars($user->ap_paterno, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Apellido Materno</th>
												<td><?php echo htmlspecialchars($user->ap_materno, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Direccion</th>
												<td><?php echo htmlspecialchars($user->direccion, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Razón Social</th>
												<td><?php echo htmlspecialchars($user->razon_social, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Email</th>
												<td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Teléfono</th>
												<td><?php echo htmlspecialchars($user->telefono, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>

											<tr>
												<th>Saldo a Favor</th>
												<td><?php echo htmlspecialchars($user->saldo_a_favor, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>

											<tr>
												<th>Estado</th>
												<td><?php echo ($user->estadocli) ? '<span class="label label-success">'.lang('users_active').'</span>' : '<span class="label label-default">'.lang('users_inactive').'</span>'; ?></td>
											</tr>
											
										<?php endforeach;?>
										</tbody>
									</table>
								</div>
							</div>
						 </div>

						<div class="col-md-6">
							 <div class="box">
								<div class="box-header with-border">
									<h3 class="box-title">Datos Medidor</h3>
								</div>
								<div class="box-body">
									<table class="table table-striped table-hover">
										<tbody>
											<?php foreach ($user_info as $user):?>
												<tr>
													<th>Nº Medidor</th>
													<td><?php echo $user->id; ?></td>
												</tr>
											<tr>
												<th>Diametro</th>
												<td><?php echo $user->diametro; ?></td>
											</tr>
											<tr>
												<th>Tarifa</th>
												<td><?php echo htmlspecialchars($user->n_tarifa, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Limite Sobre consumo</th>
												<td><?php echo htmlspecialchars($user->limite_sobre_cons, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Lectura Inicial</th>
												<td><?php echo htmlspecialchars($user->lectura_inicial, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
											<tr>
												<th>Ultima Lectura</th>
												<td><?php echo htmlspecialchars($user->ultima_lectura, ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>


											<tr>
												<th>Estado</th>
												<td><?php echo ($user->estado) ? '<span class="label label-success">'.lang('users_active').'</span>' : '<span class="label label-default">'.lang('users_inactive').'</span>'; ?></td>
											</tr>
										<?php endforeach;?>
										</tbody>
									</table>

								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="box">
						<div class="container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#home">Documentos Pendientes</a></li>
								<li><a data-toggle="tab" href="#menu1">Documentos Pagados</a></li>



							</ul>

							<div class="tab-content">
								<div id="home" class="tab-pane fade in active">
									<form action="<?php echo base_url('admin/pago_de_cuentas/pago');?>" method="post">
									<h3>Documentos Pendientes</h3>
									<hr>
									<div class="container">
										<table style="width:100%" id="inventario" class="table table-bordered table-striped table-hover table-responsive">
												<thead>
												<tr>
														<th>N° Boleta</th>
														<th>Periodo</th>
														<th>Fecha lectura</th>
														<th>Vencimiento</th>
														<th>Interés</th>
														<th>Total Mes</th>
														<th>Saldo a favor</th>
														<th>Por Pagar</th>
														<th>ajuste Sencillo Mes</th>
														<th>Estado</th>
												</tr>
												</thead>
												<tbody id="inventario-data-pago">
													<?php $totalDeuda = 0; ?>
													<?php $int = 0; ?>
													<?php foreach ($users as $user): ?>
													<?php if($user["estado"] == "Pendiente"){ ?>
														<tr data-rowid="<?= $int +1 ?>">
																<td><?= $user["n_documento"] ?></td>
																<td><?= $user["periodo"] ?></td>
																<td><?= $user["fecha_lectura"] ?></td>
																<td><?= $user["fecha_vencimiento"] ?></td>
																<td><?= $user["interes"] ?></td>
																<td><?= $user["venta_mes"] //+ $user["ajuste_sencillo_anterior"]  ?></td>
																<td><?= $user["saldo_a_favor"] ?></td>
																<td><?= $user["venta_mes"]  - $user["saldo_a_favor"] ?></td>
																<td><?= $user["ajuste_sencillo_actual"] *-1 ?></td>
																<td><?= $user["estado"] ?></td>
														</tr>
														<?php $int ++; ?>
														<?php $totalDeuda = $totalDeuda + ($user["venta_mes"]  - $user["saldo_a_favor"])?>
													<?php } ?>

													<?php endforeach; ?>
												</tbody>
										</table>
									</div>

									</form>

								</div>
								<div id="menu1" class="tab-pane fade">
									<h3>Documentos Pagados</h3>
									<div class="container">
										<table style="width:100%" id="inventario2" class="table table-bordered table-striped table-hover table-responsive">
												<thead>
												<tr>

														<th>N° Boleta</th>
														<th>Vencimiento</th>
														<th>Fecha Pago</th>
														<th>Costo</th>
														<th>Estado</th>
												</tr>
												</thead>
												<tbody id="inventario-data-pago2">
													<?php $totalDeuda = 0; ?>
													<?php $int = 1; ?>
													<?php foreach ($documentosdes as $user): ?>
													<?php if($user["estado"] == "Pagada"){ ?>
														<tr data-rowid="<?= $int ?>">

																<td><?= $user["n_documento"] ?></td>
																<td><?= $user["fecha_vencimiento"] ?></td>
																<td><?= $user["fecha_pago"] ?></td>
																<td><?= ($user["venta_mes"] - $user["saldo_a_favor"] ) ?></td>
																<td><?= $user["estado"] ?></td>
														</tr>
														<?php $int ++; ?>
													<?php } ?>

													<?php endforeach; ?>
												</tbody>

										</table>
									</div>

								</div>
							</div>
						</div>
					</div>
					</div>


				</section>
			</div>
