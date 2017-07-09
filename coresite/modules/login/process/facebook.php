<?php
/*
Name: Facebook Login
Description: Processing Facebook Login
*/
define ('WP_USE_THEMES', false);
include (dirname (dirname (dirname (dirname (dirname (__DIR__))))) . '/wp-blog-header.php');

$facebook = new CoreSite\Module\Login\Facebook ();
$facebook->login ();

header ('Location: /', TRUE, 303);
CoreSite\Core\Theme::prg ();
?>
