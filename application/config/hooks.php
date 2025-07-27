<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

/*
| -------------------------------------------------------------------------
| Compress output
| -------------------------------------------------------------------------
|
*/
$hook['display_override'][] = array(
	'class'    => '',
	'function' => 'minify_html',
	'filename' => 'minify_html.php',
	'filepath' => 'hooks'
);

$hook['pre_system'] = array(
    'class'    => 'Cors',
    'function' => 'set_cors_headers',
    'filename' => 'Cors.php',
    'filepath' => 'hooks'
);

// También puedes agregar el hook en post_controller_constructor si necesitas acceso al CI instance
$hook['post_controller_constructor'] = array(
    'class'    => 'Cors',
    'function' => 'set_cors_headers',
    'filename' => 'Cors.php',
    'filepath' => 'hooks'
);
