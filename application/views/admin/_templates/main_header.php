<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!-- Topbar Start -->
<div class="topbar-custom">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <!-- Menú de navegación izquierdo -->
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <li>
                    <!-- Botón para colapsar el sidebar -->
                    <button class="button-toggle-menu nav-link">
                        <i data-feather="menu" class="noti-icon"></i>
                    </button>
                </li>
            </ul>

            <!-- Menú de navegación derecho -->
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

                <!-- Opción para pantalla completa -->
                <li class="d-none d-sm-flex">
                    <button type="button" class="btn nav-link" data-toggle="fullscreen">
                        <i data-feather="maximize" class="align-middle fullscreen noti-icon"></i>
                    </button>
                </li>


                <?php if (isset($admin_prefs['notifications_menu']) && $admin_prefs['notifications_menu'] == TRUE): ?>
                    <!-- Notificaciones -->
                    <li class="dropdown notification-list topbar-dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i data-feather="bell" class="noti-icon"></i>
                            <span class="badge bg-warning rounded-circle noti-icon-badge">1</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-lg">
                            <div class="dropdown-item noti-title">
                                <h5 class="m-0">Notifications</h5>
                            </div>
                            <div class="noti-scroll" data-simplebar>
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="notify-icon">
                                        <i class="fas fa-users text-primary"></i>
                                    </div>
                                    <p class="notify-details">Bienvenido a Repo-Viveros</p>
                                    <p class="text-muted mb-0">1 new notification</p>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item notify-all">
                                Ver Todo
                            </a>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (isset($admin_prefs['user_menu']) && $admin_prefs['user_menu'] == TRUE): ?>
                    <!-- Menú de usuario -->
                    <li class="dropdown notification-list topbar-dropdown">
                        <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="pro-user-name ms-1">
                                <?php echo $user_login['firstname'] . ' ' . $user_login['lastname']; ?> <i class="mdi mdi-chevron-down"></i>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end profile-dropdown ">

                            <small class="text-muted dropdown-header noti-title">
                                <?php echo lang('header_member_since'); ?> <?php echo date('d-m-Y', $user_login['created_on']); ?>
                            </small>

                            <div class="dropdown-divider"></div>

                            <a href="<?php echo base_url('admin/users/profile/' . $user_login['id']); ?>" class="dropdown-item notify-item">
                                <i class="mdi mdi-account-circle-outline fs-16 align-middle"></i>
                                <span>Ver Perfil</span>
                            </a>
                            <a href="<?php echo base_url('auth/logout/admin'); ?>" class="dropdown-item notify-item fw-bold cerrar-sesion">
                                <i class="mdi mdi-location-exit fs-16 align-middle"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</div>