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

                <div class="card-body">
                    Enviar datos de la API

                </div>
            </div>
        </div>
    </section>
</div>