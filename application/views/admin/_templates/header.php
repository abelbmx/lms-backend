<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta charset="<?php echo $charset; ?>">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <title><?php echo $title; ?></title>
    <!-- Meta tags para responsividad y compatibilidad -->
    <!-- ... (otras meta tags) -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('/assets/css/byd.css'); ?>">

    <!-- Datatables css -->
    <link href="<?php echo base_url('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css'); ?>" rel="stylesheet" type="text/css" />

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="<?php echo base_url('/assets/libs/sweetalert2/sweetalert2.min.css'); ?>">

    <!-- Custom CSS -->
    <!-- Incluye tus archivos CSS personalizados aquí -->
    <link rel="stylesheet" href="<?php echo base_url('/assets/css/app.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('/assets/css/icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/libs/node-waves/waves.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/libs/swiper/swiper-bundle.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/libs/splide/splide.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/icons.min.css'); ?>">


    <!-- jQuery -->
    <script src="<?php echo base_url('/assets/libs/jquery/jquery.min.js') ?>"></script>

    <!-- jQuery Migrate Plugin -->
    <script src="<?php echo base_url('/assets/libs/jquery-migrate/jquery-migrate-3.5.0.min.js'); ?>"></script>

    <!-- DataTables JS -->
    <script type="text/javascript" src="<?php echo base_url('/assets/libs/datatables.net/js/jquery.dataTables.min.js'); ?>"></script>

    <!-- DataTables Buttons JS -->
    <script type="text/javascript" src="<?php echo base_url('/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('/assets/libs/jszip/jszip.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('/assets/libs/datatables.net-buttons/js/buttons.html5.min.js'); ?>"></script>

    <!-- dataTables.bootstrap5 -->
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js'); ?>"></script>

    <!-- buttons.colVis -->
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-buttons/js/buttons.flash.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-buttons/js/buttons.html5.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-buttons/js/buttons.print.min.js'); ?>"></script>

    <!-- buttons.bootstrap5 -->
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js'); ?>"></script>

    <!-- dataTables.keyTable -->
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js'); ?>"></script>

    <!-- dataTable.responsive -->
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js'); ?>"></script>

    <!-- dataTables.select -->
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-select/js/dataTables.select.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js'); ?>"></script>


    <!-- DataTables Responsive JS -->
    <script type="text/javascript" src="<?php echo base_url('/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js'); ?>"></script>

    <!-- SweetAlert2 JS -->
    <script src="<?php echo base_url('/assets/libs/sweetalert2/sweetalert2.all.min.js'); ?>"></script>

    <!-- Highcharts JS -->
    <script src="<?php echo base_url('/assets/libs/highcharts/highcharts.js'); ?>"></script>

    <!-- Custom Scripts -->
    <!-- Incluye tu script dp.min.js después de jQuery y jQuery Migrate -->

    <script src="<?php echo base_url('assets/libs/animejs/anime.min.js'); ?>"></script>


</head>

<body data-menu-color="light" data-sidebar="default">