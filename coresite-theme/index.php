<?php
ini_set ('display_errors', TRUE);
define ('CS_REQUEST', TRUE);

$cs_theme->process ('request');
$cs_error = isset ($_GET['error']) ? json_decode (stripslashes (urldecode ($_GET['error']))) : null;

global $current_user;

get_header ();
$cs_theme->render ('menu');
$cs_theme->render ('interface');
get_footer ();
?>
