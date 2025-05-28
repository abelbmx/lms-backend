<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
*/
// Possible hosts locally. You can add some if needed.
$config['host_dev'] = array('localhost', '127.0.0.1', '::1');

// Fill in the file of your project here when you develop locally.
$host_dev = 'lms-backend';

// Fill in the domain name here when your project is online.
// Example : www.johndoe.com
//           johndoe.com
//
//$host_prod = 'your_domain.tld';
//$host_prod = 'www.surlit.cl/repo-viveros/';

//$host_prod= defined('BASE_URL')?BASE_URL:'';
//$host_prod= 'www.surlit.cl/repo-viveros/';
//$host_prod= 'qa.surlit.cl/';
$host_prod = 'localhost/lms-backend/';

// WARNING: Do not modify the lines below
$domain = (in_array($_SERVER['HTTP_HOST'], $config['host_dev'], TRUE)) ? $_SERVER['HTTP_HOST'] . '/' . $host_dev : $host_prod;

$config['base_url'] = (! empty($_SERVER['HTTPS'])) ? 'https://' . $domain : 'http://' . $domain;

/*
|--------------------------------------------------------------------------
| Index File
|--------------------------------------------------------------------------
|
*/
$config['index_page'] = '';

/*
|--------------------------------------------------------------------------
| Assets
|--------------------------------------------------------------------------
|
*/
$config['assets_dir']     = 'assets';
$config['frameworks_dir'] = $config['assets_dir'] . '/frameworks';
$config['plugins_dir']    = $config['assets_dir'] . '/plugins';

/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
|
*/
$config['upload_dir']     = 'upload';
$config['avatar_dir']     = $config['upload_dir'] . '/avatar';
