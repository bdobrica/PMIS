<?php
/*
Plugin Name: CoreSite Plugin
Plugin URI: http://coresecurity.ro/wordpress/coresite
Description:
Author: Bogdan Dobrica
Version: 0.1
Author URI: http://ublo.ro/
*/
spl_autoload_register (function ($class) {
	if (strncmp ($class, 'CoreSite\\', 9) === 0) {
		$class = substr ($class, 9);

		if (strncmp ($class, 'Module\\', 7) === 0) {
			$class = substr ($class, 7);

			if (FALSE === ($pos = strrpos ($class, '\\'))) return;
		
			$file = WP_CONTENT_DIR . '/coresite/modules/' . strtolower (substr ($class, 0, $pos)) . '/class/' . strtolower (substr ($class, $pos + 1)) . '.php';
			if (!file_exists ($file)) return;
			}
		else {
			if (FALSE === ($pos = strrpos ($class, '\\'))) return;

			$file = __DIR__ . '/class/' . strtolower (substr ($class, 0, $pos)) . '/' . strtolower (substr ($class, $pos + 1)) . '.php';
			if (!file_exists ($file)) return;
			}
		}
	else {
		if (FALSE === ($pos = strpos ($class, '\\'))) return;

		$file = __DIR__ . '/class/vendor/' . strtolower (substr ($class, 0, $pos)) . '/' . str_replace ( '\\', '/', substr ($class, $pos + 1)) . '.php';
		if (!file_exists ($file)) return;
		}

	include ($file);
	});

if (file_exists ('coresite_config.php'))
	include ('coresite_config.php');

$cs_plugin = new CoreSite\Core\Plugin ();
?>
