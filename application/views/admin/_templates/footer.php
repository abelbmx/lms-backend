<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!-- Footer -->
<footer class="footer" id="main-footer">
    <div class="container-fluid">
        <div class="row d-flex justify-content-between w-100">
            <div class="col fs-13 text-muted text-start">
                <strong>
                    &copy; 2024-<?php echo date('Y'); ?>
                    <a href="http://repo-viveros.cl" class="text-decoration-none">Stanley Black & Decker</a>.
                </strong>
                <?php echo lang('footer_all_rights_reserved'); ?>.
            </div>
            <div class="col text-muted text-end">
                <strong><?php echo lang('footer_version'); ?></strong> Desarrollo
            </div>
        </div>
    </div>
</footer>
<!-- end Footer -->

<!-- Scripts -->

<script src="<?php echo base_url('assets/js/byd.js'); ?>"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>

<!-- Flatpickr Timepicker Plugin js -->
<script src="<?php echo base_url('assets/libs/flatpickr/flatpickr.min.js') ?>"></script>



<!-- Panzoom.js CDN -->
<script src="https://unpkg.com/@panzoom/panzoom/dist/panzoom.min.js"></script>



<!-- Vendor -->
<script src="<?php echo base_url('assets/libs/node-waves/waves.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/libs/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/libs/simplebar/simplebar.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/libs/waypoints/lib/jquery.waypoints.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/libs/jquery.counterup/jquery.counterup.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/libs/feather-icons/feather.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/libs/swiper/swiper-bundle.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/libs/splide/splide.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/libs/feather-icons/feather.min.js'); ?>"></script>


<!-- Apexcharts JS -->
<script src="<?php echo base_url('assets/libs/apexcharts/apexcharts.min.js'); ?>"></script>


<!-- App js-->
<script src="<?php echo base_url('assets/js/app.js'); ?>"></script>





</body>

</html>