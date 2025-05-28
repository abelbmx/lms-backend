<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!-- Left Sidebar Start -->
<div class="app-sidebar-menu">
	<div class="h-100" data-simplebar>
		<!--- Sidemenu -->
		<div id="sidebar-menu">
			<div class="logo-box pt-1">
				<a href="index.html" class="logo">
					<span class="logo-sm object-fit-contain">
						<img src="<?= base_url('assets/img/BDLogo.svg') ?>"
							alt="Black+Decker bg"
							class="object-fit-cover" style="width: 100%;">
					</span>
					<span class="logo-lg object-fit-contain">
						<img src="<?= base_url('assets/img/BDLogo.svg') ?>" alt="Black+Decker bg"
							class="object-fit-cover" style="width: 100%;">
					</span>
				</a>
			</div>

			<ul id=" side-menu ">
				<!-- Ir al sitio web -->
				<li>
					<a href="<?php echo base_url('/'); ?>">
						<i data-feather="globe"></i>
						<span> <?php echo lang('menu_access_website'); ?></span>
					</a>
				</li>

				<li class="menu-title mt-2">Sistema</li>

				<!-- Dashboard -->
				<li>
					<a href="<?php echo site_url('admin/dashboard'); ?>">
						<i data-feather="home"></i>
						<span> <?php echo lang('menu_dashboard'); ?> </span>
					</a>
				</li>

				<!-- Importar Órdenes CSV -->
				<li>
					<a href="<?php echo site_url('admin/orders'); ?>">
						<i data-feather="file-plus"></i>
						<span> Importar Órdenes CSV </span>
					</a>
				</li>

				<!-- Pedidos y documentación -->
				<li>
					<a href="<?php echo site_url('admin/pedidos'); ?>">
						<i data-feather="file-text"></i>
						<span> Pedidos y documentación </span>
					</a>
				</li>
				<li>
					<a href="<?php echo site_url('admin/manifiestos'); ?>">
						<i data-feather="file-text"></i>
						<span> Manifiestos </span>
					</a>
				</li>

				<li class="menu-title mt-2">Gestión de datos</li>

				<!-- Ingresar Datos -->
				<li>
					<a href="<?php echo site_url('admin/ingresar_datos'); ?>">
						<i data-feather="edit"></i>
						<span> Ingresar datos </span>
					</a>
				</li>

				<!-- Subir Documentos Escaneados -->
				<li>
					<a href="<?php echo site_url('admin/subir_documentos'); ?>">
						<i data-feather="upload"></i>
						<span> Subir y Enlazar Documentos </span>
					</a>
				</li>

				<li class="menu-title mt-2">Manifiestos</li>
				<li>
					<a href="<?php echo site_url('admin/manifiestos_sin_documentos'); ?>">
						<i data-feather="file"></i>
						<span> Revisar órdenes sin enlazar </span>
					</a>
				</li>
				<li>
					<a href="<?php echo site_url('admin/manifiestos_registro'); ?>">
						<i data-feather="folder"></i>
						<span> Registrar Manifiestos </span>
					</a>
				</li>

				<li class="menu-title mt-2">Análisis y reportes</li>
				<li>
					<a href="<?php echo site_url('admin/reportes'); ?>">
						<i data-feather="bar-chart-2"></i>
						<span> Reportes </span>
					</a>
				</li>
				<li>
					<a href="#submenuHistoricos" data-bs-toggle="collapse">
						<i class="mdi mdi-history"></i>
						<span> Históricos y métricas </span>
						<span class="menu-arrow"></span>
					</a>
					<div class="collapse" id="submenuHistoricos">
						<ul class="nav-second-level">
							<li>
								<a href="<?php echo site_url('admin/historico_subidas'); ?>" class="tp-link">
									Histórico de Subida de Archivos
								</a>
							</li>
							<li>
								<a href="<?php echo site_url('admin/metricas_documentos'); ?>" class="tp-link">
									Métricas de Documentos
								</a>
							</li>
						</ul>
					</div>
				</li>

				<li class="menu-title mt-2">Usuarios y permisos</li>
				<li>
					<a href="#submenuUsuarios" data-bs-toggle="collapse">
						<i data-feather="users"></i>
						<span> Usuarios y Accesos </span>
						<span class="menu-arrow"></span>
					</a>
					<div class="collapse" id="submenuUsuarios">
						<ul class="nav-second-level">
							<li>
								<a href="<?php echo site_url('admin/usuarios'); ?>" class="tp-link">Perfiles de Usuarios</a>
							</li>
							<li>
								<a href="<?php echo site_url('admin/roles'); ?>" class="tp-link">Roles y Accesos</a>
							</li>
						</ul>
					</div>
				</li>

				<li class="menu-title mt-2">Integraciones</li>
				<li>
					<a href="#submenuApi" data-bs-toggle="collapse">
						<i class="mdi mdi-connection"></i>
						<span> Integración API </span>
						<span class="menu-arrow"></span>
					</a>
					<div class="collapse" id="submenuApi">
						<ul class="nav-second-level">
							<li>
								<a href="<?php echo base_url('oauth2/token'); ?>" class="tp-link">
									Obtener Token OAuth 2.0
								</a>
							</li>
							<li>
								<a href="<?php echo base_url('api/enviar_datos'); ?>" class="tp-link">
									Enviar Datos a la API
								</a>
							</li>
							<li>
								<a href="<?php echo site_url('admin/api_accesos'); ?>" class="tp-link">
									Accesos API
								</a>
							</li>
						</ul>
					</div>
				</li>

				<li class="menu-title mt-2">Clientes</li>
				<li>
					<a href="<?php echo base_url('admin/clientes'); ?>">
						<i class="mdi mdi-office-building-outline"></i>
						<span> <?php echo lang('clientes'); ?> </span>
					</a>
				</li>
			</ul>
		</div>
		<!-- End Sidebar -->
		<div class="clearfix"></div>
	</div>
</div>
