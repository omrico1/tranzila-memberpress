<?php

/*
Plugin Name: Tranzila Memberpress
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Tranzila payment gateway for Memberpress
Version: 1.0
Author: omrico1
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

define('MEPRZILA_PLUGIN_SLUG','tranzila-memberpress/tranzila-memberpress.php');
define('MEPRZILA_PLUGIN_NAME','tranzila-memberpress');
define('MEPRZILA_PATH',WP_PLUGIN_DIR.'/'.MEPRZILA_PLUGIN_NAME);
define('MEPRZILA_GATEWAYS_PATH',MEPRZILA_PATH.'/app/gateways');

add_filter ( 'mepr-gateway-paths', function($x) { return array_merge(array(MEPRZILA_GATEWAYS_PATH),$x); }, 11, 1);
