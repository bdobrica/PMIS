<?php
define ('WP_USE_THEMES', false);
#define ('CS_DEBUG', true);
include (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-blog-header.php');

global $cs_theme;
if (!isset ($cs_theme)) $cs_theme = new CoreSite\Core\Theme;

CoreSite\Module\Profile\Resume::install ();
