<?php
namespace CoreSite\Core;

global $cs_theme;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title><?php wp_title (); ?></title><?php wp_head(); ?></head><body class="blank">
<!-- Simple splash screen-->
<div class="splash"><div class="color-line"></div><div class="splash-title"><h1><?php Theme::_e (/*T[*/'PROJECT MANAGEMENT<br>INFORMATION SYSTEM'/*]*/); ?></h1>
<p><?php Theme::_e (/*T[*/'Developed for Pro-Youth by Core Security Advisers'/*]*/); ?></p><div class="loading-logo"></div></div></div>
<!--[if lt IE 7]>
<p class="alert alert-danger">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
<?php $cs_theme->render ('header'); ?>
