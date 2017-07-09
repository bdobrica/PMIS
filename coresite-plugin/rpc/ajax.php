<?php
define ('WP_USE_THEMES', false);
define ('CS_AJAX', true);
parse_str (parse_url ($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $_GET);
include (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-blog-header.php');
header ('HTTP/1.1 200 OK');

global $cs_theme;
if (!isset ($cs_theme)) $cs_theme = new CoreSite\Core\Theme ();

$action = null;
$object = null;

$return = [];

foreach ($_POST as $key => $value) {
	if ($dash = strpos ($key, '_')) {
		$prefix = substr ($key, 0, $dash);
		if (in_array ($prefix, CoreSite\Core\Theme::$A)) {
			$action = $prefix;
			$object = substr ($key, $dash + 1);
			$_POST[$key] = null;
			unset ($_POST[$key]);
			}
		}
	}

$cs_user = $cs_theme->get ('user');
$cs_processor = $cs_theme->get ('processor');

if (!is_null ($cs_user) && !is_null ($cs_processor))
	include ($cs_processor);
else
	$return['error'] = 1;

echo json_encode ((object) $return);
?>
