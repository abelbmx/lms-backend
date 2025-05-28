<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<h1>Prueba de rutas admin y clientes</h1>

<p><a href="<?php echo base_url('/'); ?>">Home</a></p>

<p><a href="<?php echo base_url('admin'); ?>">Administrador</a></p>

<p><a href="<?php echo base_url('client'); ?>">Clientes</a></p>

<p><a href="<?php echo base_url('auth/logout'); ?>">Logout</a></p>
