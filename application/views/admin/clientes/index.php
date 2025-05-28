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
		<div class="col">
			<div class="card d-block">
				<div class="card-header with-border">
					<h5 class="card-title"><?php echo anchor('admin/clientes/create', '<i class="fa fa-plus"></i> ' . lang('users_create_user'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h5>
				</div>
				<div class="card-body">
					<ul class="nav nav-pills" id="navpills2" role="tablist">
						<li class="nav-item" role="presentation">
							<a class="nav-link active" data-bs-toggle="tab" href="#tab1" role="tab">
								<span class="d-block d-sm-none"><i class="mdi mdi-home-account"></i></span>
								<span class="d-none d-sm-block">Clientes Boleta</span>
							</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-bs-toggle="tab" href="#tab2" role="tab">
								<span class="d-block d-sm-none"><i class="mdi mdi-account-outline"></i></span>
								<span class="d-none d-sm-block">Clientes Factura</span>
							</a>
						</li>
					</ul>
					<div class="tab-content mt-3">
						<div id="tab1" class="tab-pane fade in active">

							<table style="width:100%" class="table table-striped table-hover" id="clientes">
								<thead>
									<tr>
										<th>Nº Cliente</th>
										<th>RUT</th>
										<th>Nombre Cliente</th>
										<th>Direccion</th>
										<th>Email</th>
										<th>Estado</th>
										<th>Acciones</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
						<div id="tab2" class="tab-pane">
							<div class="section-title">
								<table style="width:100%" class="table table-striped table-hover" id="clientes_factura">
									<thead>
										<tr>
											<th>Nº Cliente</th>
											<th>RUT</th>
											<th>Nombre Cliente</th>
											<th>Giro</th>
											<th>Direccion</th>
											<th>Email</th>
											<th>Estado</th>
											<th>Acciones</th>
										</tr>
									</thead>
									<tbody>
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

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Ver QR</h4>
			</div>
			<div class="modal-body">

				<div id="qrdata">
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				<button type="button" id="printbtn" class="btn btn-primary">Imprimir</button>
			</div>
		</div>
	</div>
</div>
