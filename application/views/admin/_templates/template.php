<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Cabeceras de seguridad
header('X-Powered-By: Prod-domProjects.com');
header('X-XSS-Protection: 1');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Vary: Accept-Encoding');



// Incluimos el header
if (isset($header)) {
    echo $header;
}
echo '<div id="app-layout">';

// Incluimos el main_header
if (isset($main_header)) {
    echo $main_header;
}

// Incluimos el main_sidebar (Offcanvas)
if (isset($main_sidebar)) {
    echo $main_sidebar;
}


// Incluimos el contenido principal

echo '<main class="content-page">';
echo '<div class="content">';

if (isset($content)) {
    echo $content;
}
// Cerramos el main

echo '</div';
echo '</main>';


// Incluimos el footer
if (isset($footer)) {
    echo $footer;
}
