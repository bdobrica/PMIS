<?php
/**
 * Core of CoreSite
 */

/**
 * Theme Class. The CoreSite theme is an instance of this class.
 *
 * @category
 * @package CoreSite
 * @subpackage None
 * @copyright Core Security Advisers SRL
 * @author Bogdan Dobrica <bdobrica @ gmail.com>
 * @version 0.1
 *
 */
namespace CoreSite\Core;

class Theme {
	const NAME		= 'Core Site';
	const BUFFER		= 128;
	const CAPABILITY	= 'administrator';
	const MENU		= 'menu';
	const ROLES_DIR		= 'roles';
	const TEXTDOMAIN	= __CLASS__;
	const HOME		= '/';

	const GET_PAGE		= 'page';
	const GET_MODULE	= 'module';
	const GET_ACTION	= 'action';
	const GET_ERROR		= 'error';

	const DEFAULT_ACTION	= 'read';

	const CONTENT_DIR	= 'coresite';
	const ASSETS_DIR	= 'assets';
	const MODULES_DIR	= 'modules';
	const MODULE_IFACE_DIR	= 'interface';
	const MODULE_CLASS_DIR	= 'class';
	const MODULE_PROC_DIR	= 'process';
	const MODULE_MANIFEST	= 'manifest';
	const MODULE_INFO	= 'module';

	const COMMON_DIR	= 'common';
	const INFO_DIR		= 'info';
	const HELP_DIR		= 'help';

	const TEMPLATE_DIR	= 'php';
	const ERROR_DIR		= 'err';
	const WIDGET_DIR	= 'widget';
	const HEADER_DIR	= 'header';
	const FOOTER_DIR	= 'footer';

	public static $colors	= [
		'bluejeans'	=> '#4a89dc',
		'aqua'		=> '#3bafda',
		'mint'		=> '#37bc9b',
		'grass'		=> '#8cc152',
		'sunflower'	=> '#f6bb42',
		'bittersweet'	=> '#e9573f',
		'grapefruit'	=> '#da4453',
		'lavender'	=> '#967adc',
		'pinkrose'	=> '#d770ad'
		];

	public static $field_types = [
		'string'	=> /*T*/'String'/*]*/,
		'file'		=> /*T[*/'File'/*]*/,
		'numeric'	=> /*T[*/'Numeric'/*]*/,
		'date'		=> /*T[*/'Date'/*]*/,
		'datetime'	=> /*T[*/'Date &amp; Time'/*]*/,
		'interval'	=> /*T[*/'Time Interval'/*]*/,
		'textarea'	=> /*T[*/'Textarea'/*]*/,
		'rte'		=> /*T[*/'Rich Text'/*]*/,
		];

	public static $A	= [
		'create',
		'read',
		'update',
		'delete',
		'list'
		];

	private $options;

	private $assets;

	private $storage;
	private $user;
	private $role;

	private $modules;
	private $pages;
	private $breadcrumbs;

	private $error;

	private $module;
	private $page;
	private $action;

	private $theme_dir;
	private $theme_url;

	public function __construct ($menus = [], $sidebars = []) {
		global $locale;

		$this->theme_dir = get_stylesheet_directory ();
		$this->theme_url = get_template_directory_uri ();

		$this->assets	= [];
		$this->storage	= new Storage ();
		try {
			$this->user = new User ( FALSE );
			$this->role = strtolower ($this->user->get ('role'));
			$this->user->log ();
			}
		catch (Fault $e) {
			$this->user = null;
			$this->role = null;
			}

		if ($this->storage->get ('locale'))
			$this->storage->set ('locale', $locale);
		else
			$locale = $this->storage->get ('locale');

		if (defined ('CORESITE_CUSTOM_URL')) {
			}

		$this->module	= isset ($_GET[self::GET_MODULE]) ? $_GET[self::GET_MODULE] : null;
		$this->page	= isset ($_GET[self::GET_PAGE]) ? $_GET[self::GET_PAGE] : null;
		$this->action	= isset ($_GET[self::GET_ACTION]) ? (in_array ($_GET[self::GET_ACTION], static::$A) ? $_GET[self::GET_ACTION] : self::DEFAULT_ACTION) : self::DEFAULT_ACTION;

		$this->modules = [];
		$this->get ('pages');

		$this->error = [];
		if (isset ($_GET[self::GET_ERROR]) && !empty ($_GET[self::GET_ERROR])) {
			$c = 0;
			$key = '';
			$value = '';
			$prev_is_value = false;
			$str = strtoupper ($_GET['error']);
			while ($c < strlen ($str)) {
				$chr = $str[$c++];

				if ('A' <= $chr && $chr <= 'Z') {
					if ($prev_is_value) {
						$prev_is_value = false;
						if (!isset ($this->error[$key]))
							$this->error[$key] = [ (int) $value ];
						else
							$this->error[$key][] = (int) $value;
						$key = '';
						$value = '';
						}
					$key .= $chr;
					}
				if ('0' <= $chr && $chr <= '9') {
					$prev_is_value = true;
					$value .= $chr;
					}
				}
			if ($prev_is_value)
				if (!isset ($this->error[$key]))
					$this->error[$key] = [ (int) $value ];
				else
					$this->error[$key][] = (int) $value;
			}

		if (!empty ($this->pages)) {
			if (is_null ($this->module))
				$this->module = current ($this->pages);
			else {
				foreach ($this->pages as $current) {
					if ($current->slug == $this->module) {
						$this->module = $current;
						reset ($this->pages);
						break;
						}
					}
				if (is_string ($this->module))
					$this->module = null;
				}

			if (is_null ($this->module))
				$this->page = null;
			else {
				if (is_null ($this->page)) {
					$_order = PHP_INT_MAX;

					$_stack = $this->module->children;
					while (!empty ($_stack)) {
						$current = array_shift ($_stack);
						if (isset ($current->order) && $current->order < $_order) {
							$this->page = $current;
							$_order = $current->order;
							}
						if (!empty ($current->children))
							$_stack = array_merge ($_stack, $current->children);
						}
					}
				else {
					$_stack = $this->module->children;
					while (!empty ($_stack)) {
						$current = array_shift ($_stack);
						if (isset ($current->slug) && $current->slug == $this->page) {
							$this->page = $current;
							break;
							}
						if (!empty ($current->children))
							$_stack = array_merge ($_stack, $current->children);
						}
					}
				if (is_string ($this->page))
					$this->page = null;
				}
			}

		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');

		add_action ('wp_enqueue_scripts', [$this, 'main_scripts']);
		add_action ('admin_enqueue_scripts', [$this, 'admin_scripts']);
		add_action ('admin_menu', [$this, 'admin_menu']);
		}

	public function set ($key = null, $value = null) {
		if (is_string ($key)) {
			switch ($key) {
				}
			}
		else
		if (is_array ($key)) {
			}
		return FALSE;
		}

	public function get ($key = null, $opts = null) {
		switch ((string) $key) {
			case 'header':
				$out = [];

				$state = 0;
				
				if (!file_exists ($opts)) throw new Fault ();
				if (($fh = fopen ($opts, 'r')) === FALSE) throw new Fault ();
				
				while ((($line = fgets ($fh, self::BUFFER)) !== FALSE) && ($state < 2)) {
					$line = trim ($line);
					if (strpos ($line, '/*') === 0) { $state = 1; continue; }
					if (strpos ($line, '*/') === 0) break;
					if (strpos ($line, ':') === FALSE) continue;
					if ($state < 1) continue;
					list ($key, $value) = explode (':', $line);
					$out[str_replace (' ', '_', trim(strtolower($key)))] = trim($value);
					}
				if (empty ($out))
					throw new Fault ();

				return (object) $out;
				break;
			case 'assets':
				$out = [];

				$path = $this->theme_dir . '/' . self::ASSETS_DIR . '/';
				$uri = $this->theme_url . '/' . self::ASSETS_DIR . '/';

				$folders = [ 'js', 'css' ];

				foreach ($folders as $folder) {
					$search_path = $path . $folder;
					if (!is_dir ($search_path)) continue;
					if (($dh = opendir ($search_path)) === FALSE) continue;
					while (($file = readdir ($dh)) !== FALSE) {
						if ($file[0] == '.') continue;
						try {
							$header = $this->get ('header', $search_path . '/' . $file);
							}
						catch (Fault $e) {
							continue;
							}
						if (is_null ($header)) continue;
						$out[] = (object) [
							'type'		=> $folder,
							'path'		=> $uri . $folder . '/' . $file,
							'name'		=> isset ($header->name) ? $header->name : $file,
							'dependencies'	=> isset ($header->dependencies) ? explode (',', $header->dependencies) : [],
							'version'	=> isset ($header->version) ? $header->version : '0.1',
							'footer'	=> strtolower(isset ($header->footer) ? $header->footer : '') == 'true' ? TRUE : FALSE,
							'media'		=> isset ($header->media) ? $header->media : 'all',
							'scope'		=> isset ($header->scope) ? $header->scope : ''
							];
						}
					closedir ($dh);
					}

				return $out;
				break;
			case 'pages':
				if (!empty ($this->pages)) return $this->pages;
				$this->pages = [];
				
				$search_path = WP_CONTENT_DIR . '/' . self::CONTENT_DIR . '/' . self::MODULES_DIR;

				if (!is_dir ($search_path)) throw new Fault ();
				if (($dh = opendir ($search_path)) === FALSE) throw new Fault ();

				while (($file = readdir ($dh)) !== FALSE) {
					if ($file[0] == '.') continue;
					if (!is_dir ($search_path . '/' . $file)) continue;

					try {
						$manifest = $this->get ('manifest', $file);
						}
					catch (Fault $e) {
						continue;
						}

					if (isset ($manifest->slug)) $this->modules[] = $manifest->slug;
					if (!isset ($manifest->pages) || empty ($manifest->pages)) continue;
					if (!self::_can ($this->role, $manifest->roles)) continue;

					$stack = $manifest->pages;
					foreach ($manifest->pages as $key => $page)
						if (self::_can ($this->role, $page->roles))
							$stack[] = $page;
						else
							unset ($manifest->pages[$key]);

					while (!empty ($stack)) {
						$current = array_shift ($stack);
						if (is_null ($current)) break;
						if (!empty ($current->children)) {
							foreach ($current->children as $key => $page)
								if (self::_can ($this->role, $page->roles))
									$stack[] = $page;
								else
									unset ($current->children[$key]);
							}
						}

					$this->pages[] = (object) [
						'slug'		=> isset ($manifest->slug) ? $manifest->slug : '',
						'name'		=> isset ($manifest->name) ? $manifest->name : '',
						'description'	=> isset ($manifest->description) ? $manifest->description : '',
						'parent'	=> isset ($manifest->parent) ? $manifest->parent : 'root',
						'order'		=> isset ($manifest->order) ? $manifest->order : 0,
						'roles'		=> isset ($manifest->roles) ? $manifest->roles : null,
						'children'	=> $manifest->pages
						];
					}

				usort ($this->pages, [ $this, '_cmp_order' ]);
		
				return $this->pages;
				break;
			case 'manifest':
				$path = WP_CONTENT_DIR . '/' . self::CONTENT_DIR . '/' . self::MODULES_DIR . '/' . $opts;
				if (!is_dir ($path)) throw new Fault ();
				if (file_exists ($path . '/' . self::MODULE_MANIFEST . '.php')) {
					$manifest = include ($path . '/' . self::MODULE_MANIFEST . '.php');
					return $manifest;
					}

				$pages = [];

				$search_path = $path . '/' . self::MODULE_IFACE_DIR;
				if (!is_dir ($search_path)) throw new Fault ();
				if (($dh = opendir ($search_path)) === FALSE) throw new Fault ();

				$roles = [];

				while (($file = readdir ($dh)) !== FALSE) {
					if ($file[0] == '.') continue;
					if (is_dir ($search_path . '/' . $file)) continue;
					try {
						$header = $this->get ('header', $search_path . '/' . $file);	
						}
					catch (Fault $e) {
						continue;
						}

					$_roles = isset ($header->roles) ? self::_roles ($header->roles) : null;

					$pages[] = (object) [
						'name'		=> isset ($header->name) ? $header->name : '',
						'description'	=> isset ($header->description) ? $header->description : '',
						'slug'		=> substr ($file, 0, -4),
						'parent'	=> isset ($header->parent) ? $header->parent : $opts,
						'hidden'	=> isset ($header->hidden) ? strtolower ($header->hidden) == 'true' : FALSE,
						'order'		=> isset ($header->order) ? $header->order : 0,
						'roles'		=> isset ($header->roles) ? self::_roles ($header->roles) : null
						];

					if (!is_null ($_roles))
						$roles = $roles + (is_array ($_roles) ? $_roles : [ $_roles ]);
					}

				if (empty ($pages)) throw new Fault ();
				$children = [];
				foreach ($pages as $page)
					$children[$page->parent ? $page->parent : $opts][] = $page;

				foreach ($pages as $page)
					if (isset ($children[$page->slug])) {
						usort ($children[$page->slug], [ $this, '_cmp_order' ]);
						$page->children = $children[$page->slug];
						}

				$name = '';
				$description = '';
				$size = 0;
				$order = 0;
				$_order = PHP_INT_MAX;
				if (isset ($children[$opts]) && !empty ($children[$opts])) {
					foreach ($children[$opts] as $child) {
						if (isset ($child->hidden) && $child->hidden) continue;
						$size ++;
						if ($child->order < $_order) {
							$_order = $child->order;
							$name = isset ($child->name) ? $child->name : '';
							}
						}
					}

				$module_info = $path . '/' . self::MODULE_INFO . '.php';
				if (file_exists ($module_info)) {
					$header = $this->get ('header', $module_info);
					if (isset ($header->name)) $name = $header->name;
					if (isset ($header->description)) $description = $header->description;
					if (isset ($header->order)) $order = $header->order;
					if (isset ($header->roles)) $roles = self::_roles ($header->roles);
					}

				if (empty ($roles))
					$roles = null;
				if (is_array ($roles) && in_array ('*', $roles))
					$roles = [ '*' ];
				

				$manifest = (object) [
					'slug'		=> $opts,
					'active'	=> TRUE,
					'name'		=> $name,
					'description'	=> $description,
					'roles'		=> $roles,
					'parent'	=> null,
					'requires'	=> null,
					'order'		=> $order,
					'pages'		=> isset ($children[$opts]) ? $children[$opts] : [],
					'size'		=> $size
					];

				if (($fh = @fopen ($path . '/' . self::MODULE_MANIFEST . '.php', 'w+')) === FALSE) throw new Fault ();
				fwrite ($fh, '<' . '?php' . "\n" . 'return ' . str_replace ('stdClass::__set_state', '(object)', var_export ($manifest, TRUE)) . "\n" . '?' . '>');
				fclose ($fh);

				return $manifest;
				break;
			case 'content':
				if (!is_array ($opts) && is_string ($opts) && is_numeric ($opts)) {
					$opts = [ 'id' => (int) $opts, 'echo' => TRUE ];
					}
				$page = get_post ($opts);
				$content = apply_filters ('the_content', $page->post_content);
				$content = str_replace (']]>', ']]&gt;', $content);
				if (!$opts['echo']) return $content;
				echo $content;
				break;
			case 'dir':
				if (strpos ($opts, 'request::') === 0) {
					$opts = substr ($opts, 9);
					$path = TEMPLATEPATH . DIRECTORY_SEPARATOR . self::ROLES_DIR . DIRECTORY_SEPARATOR . $this->user->get ('role') . DIRECTORY_SEPARATOR . $opts . DIRECTORY_SEPARATOR . $this->page . '.php';
					return $path;
					}
				if (strpos ($opts, 'role::') === 0) {
					$opts = substr ($opts, 6);
					$path = TEMPLATEPATH . DIRECTORY_SEPARATOR . self::ROLES_DIR . DIRECTORY_SEPARATOR . $this->user->get ('role') . DIRECTORY_SEPARATOR . $opts;
					return $path;
					}
				break;
			case 'url':
				$url = $_SERVER['REQUEST_URI'];
				if (($pos = strpos ($url, '?')) !== FALSE)
					$url = substr ($url, 0, $pos);

				$opts = is_array ($opts) ? $opts : [];

				unset ($_GET['ok']);
				unset ($_GET['error']);

#				$query = isset ($_GET[self::GET]) ? array_merge ([self::GET => $_GET[self::GET]], $opts) : $opts;
				$query = $opts;
				return $url . '?' . http_build_query ($query);
				break;
			case '://':
				return $this->theme_url . '/' . $opts;
				break;
			case './':
				return $this->theme_dir . '/' . $opts;
				break;
			case 'page':
				$opts = is_null ($opts) ? 'slug' : $opts;
				if (is_string ($opts))
					switch ($opts) {
						case 'name':
							if (empty ($this->breadcrumbs)) $this->get ('breadcrumbs');
							return isset ($this->breadcrumbs[$this->page]) ? $this->breadcrumbs[$this->page] : '';
							break;
						case 'slug':
							return $this->page;
							break;
						}
				break;
			case 'action':
				return $this->action;
				break;
			case 'breadcrumbs':
				if (empty ($this->pages)) $this->get ('pages');
				if (empty ($this->pages)) return [];
				if (!empty ($this->breadcrumbs)) return $this->breadcrumbs;

				$found = [];
				$search = $this->page->slug;

				if ($search != 'dashboard') {
					while ($search != 'root') {
						$stack = $this->pages;
						while (!empty ($stack)) {
							$current = array_shift ($stack);
							if (is_null ($current)) break;
							if ($current->slug == $search) {
								$found[$current->slug] = $current->name;
								$search = $current->parent;
								break;
								}
							if (!empty ($current->children))
								$stack = array_merge ($stack, $current->children);
							}
						if ($search == $this->page)
							$search = 'root';
						}
					}

				$found['dashboard'] = 'Dashboard';

				$this->breadcrumbs = array_reverse ($found, TRUE);
				return $this->breadcrumbs;
				break;
			case 'user':
				if (is_null ($opts)) return $this->user;
				if (is_string ($opts) && is_object ($this->user))
					return $this->user->get ($opts);
				return null;
				break;
			case 'option':
				if (! ($this->options instanceof Options))
					$this->options = new Options ();
				return $this->options->get ('value', $opts);
				break;
			case 'processor':
				$path = WP_CONTENT_DIR . '/' . self::CONTENT_DIR . '/' . self::MODULES_DIR . '/' . $this->module->slug . '/' . self::MODULE_PROC_DIR . '/' . $this->page->slug . '.php';
				return file_exists ($path) ? $path : null;
				break;
			}
		return null;
		}

	public function out ($key = null, $opts = null, $callback = null) {
		$content = $this->get ($key, $opts);
		if (!is_null ($callback) && is_callable ($callback))
			$content = call_user_func ($callback, $content);
		echo $content;
		}

	public function main_scripts () {
		if (empty ($this->assets)) $this->assets = $this->get ('assets');

		if (!empty ($this->assets))
		foreach ($this->assets as $asset) {
			if ($asset->scope != '') continue;
			if ($asset->type == 'js') wp_enqueue_script ($asset->name, $asset->path, $asset->dependencies, $asset->version, $asset->footer);
			if ($asset->type == 'css') wp_enqueue_style ($asset->name, $asset->path, $asset->dependencies, $asset->version, $asset->media);
			}

		#wp_enqueue_script ('google-charts', 'https://www.gstatic.com/charts/loader.js', ['jquery'], '0.1', TRUE);
		}

	public function admin_menu () {
		add_menu_page (self::NAME . ' Menu', self::NAME . ' Menu', self::CAPABILITY, Options::PREFIX . self::MENU, [$this, 'admin_page']);

		if (!($this->options instanceof Options))
			$this->options = new Options ();

		$this->options->register (self::MENU, self::CAPABILITY);
		}

	public function admin_page () {
		if (!($this->options instanceof Options))
			$this->options = new Options ();

		$this->options->page ();
		}

	public function admin_scripts () {
		if (empty ($this->assets)) $this->assets = $this->get ('assets');

		if (!empty ($this->assets))
		foreach ($this->assets as $asset) {
			if ($asset->scope == '') continue;
			if (	$asset->scope == '' ||
				$asset->scope != 'admin' ||
				((strpos ($assets->scope, self::GET . '=') === 0) && !(isset ($_GET[self::GET]) && in_array ($_GET[self::GET], explode (',', substr($assets->scope, 5)))))
				) continue;
			if (!empty ($asset->dependencies)) {
				foreach ($asset->dependencies as $index => $dependency) {
					if ($dependency != 'media') continue;
					unset ($asset->dependencies[$index]);
					wp_enqueue_media ();
					}
				}
			if ($asset->type == 'js') wp_enqueue_script ($asset->name, $asset->path, $asset->dependencies, $asset->version, $asset->footer);
			if ($asset->type == 'css') wp_enqueue_style ($asset->name, $asset->path, $asset->dependencies, $asset->version, $asset->media);
			}
		}

	public function process ($key = null, $opts = null) {
		$error = null;

		if (is_string ($key)) {
			switch ($key) {
				case 'request':
					$request = array_merge ($_GET, $_POST);
					if (empty ($request))
						break;

					if (is_null ($this->module) || is_null ($this->page))
						break;

					$action = null;
					$object = null;

					foreach (array_keys ($request) as $key) {
						$split = explode ('_', $key);
						if (sizeof ($split) != 2) continue;
						if (in_array ($split[0], self::$A))
							list ($action, $object) = $split;
						}


					$path = WP_CONTENT_DIR . '/' . self::CONTENT_DIR . '/' . self::MODULES_DIR . '/' . $this->module->slug . '/' . self::MODULE_PROC_DIR . '/' . $this->page->slug . '.php';

					if (file_exists ($path))
						include ($path);
					break;
				}
			}
		}

	public function render ($key = null, $echo = TRUE) {
		$content = '';

		if (is_string ($key)) {
			switch ($key) {
				case 'profile':
					$widget_path = $this->theme_dir . '/' . self::ASSETS_DIR . '/' . self::TEMPLATE_DIR . '/' . self::WIDGET_DIR . '/' . $key . '.php';
					if (!file_exists ($widget_path)) break;
					ob_start ();
					include ($widget_path);
					$content = ob_get_clean ();
					break;
				case 'menu':
					if (is_null ($this->role)) break;

					$site_name = $this->get ('option', ['page' => 'menu', 'option' => 'site_name', 'echo' => FALSE]);

					$stack = $this->get ('pages');
					$content = '<aside id="menu"><div class="navigation">' . $this->render ('profile', FALSE) . '<ul class="nav" id="side-menu"><li class="active">';

					while (!empty ($stack)) {
						$current = array_pop ($stack);

						if (is_string ($current)) {
							$content .= $current;
							continue;
							}
						if (isset ($current->hidden) && $current->hidden)
							continue;

						if (empty ($current->children)) {
							$content .= '<li><a href="' . $this->get ('url', [self::GET_MODULE => $current->parent, self::GET_PAGE => $current->slug]) . '">' . $current->name . '</a>';
							array_push ($stack, '</li>');
							continue;
							}

						$visible_children = 0;
						$front = null;
						$order = PHP_INT_MAX;
						foreach ($current->children as $child) {
							if (isset ($child->hidden) && $child->hidden) continue;
							$visible_children ++;
							if ($order > $child->order) {
								$order = $child->order;
								$front = $child;
								}
							}

						if ($visible_children == 1) {
							if (is_null ($front)) continue;

							$content .= '<li><a href="' . $this->get ('url', [self::GET_MODULE => $front->parent, self::GET_PAGE => $front->slug]) . '">' . $front->name . '</a>';
							array_push ($stack, '</li>');
							continue;
							}

						$content .= '<li><a href="' . $this->get ('url', [self::GET_MODULE => $current->parent]) . '"><span class="nav-label">' . $current->name . '</span><span class="fa arrow"></span></a>';
						$children = array_reverse ($current->children);
						array_unshift ($children, '</ul></li>');
						array_push ($children, '<ul class="nav nav-second-level">');
						$stack = array_merge ($stack, $children);
						}

					$content .= '</ul></div></aside><div id="wrapper">';
					break;
				case 'breadcrumbs':
					if (empty ($this->breadcrumbs)) $this->get ('breadcrumbs');
					if (empty ($this->breadcrumbs)) {
						$content = '';
						break;
						}
					$content .= '<ol class="hbreadcrumb breadcrumb">';
					foreach ($this->breadcrumbs as $slug => $name)
						$content .= '<li><a href="">' . self::__ ($name) . '</a></li>';
					$content .= '</ol>';
					break;
				case 'title':
					break;
				case 'header':
					$search_path = $this->theme_dir . '/' . self::ASSETS_DIR . '/' . self::TEMPLATE_DIR . '/' . self::HEADER_DIR;
					if (!is_dir ($search_path)) break;
					if (($dh = opendir ($search_path)) === FALSE) break;

					$order = PHP_INT_MAX;
					$found = null;

					while (($file = readdir ($dh)) !== FALSE) {
						if ($file[0] == '.') continue;
						try {
							$header = $this->get ('header', $search_path . '/' . $file);
							}
						catch (Fault $e) {
							continue;
							}
						if (is_null ($header)) continue;

						$roles = isset ($header->roles) ? self::_roles ($header->roles) : null;

						if (!self::_can ($this->role, $roles)) continue;

						$_order = isset ($header->order) ? ((int) $header->order) : 0;

						if ($order > $_order) {
							$order = $_order;
							$found = $file;
							}
						}
					closedir ($dh);
					if (!is_null ($found))
						include ($search_path . '/' . $found);
					break;
				case 'footer':
					$search_path = $this->theme_dir . '/' . self::ASSETS_DIR . '/' . self::TEMPLATE_DIR . '/' . self::FOOTER_DIR;
					if (!is_dir ($search_path)) break;
					if (($dh = opendir ($search_path)) === FALSE) break;

					$order = PHP_INT_MAX;
					$found = null;

					while (($file = readdir ($dh)) !== FALSE) {
						if ($file[0] == '.') continue;
						try {
							$header = $this->get ('header', $search_path . '/' . $file);
							}
						catch (Fault $e) {
							continue;
							}
						if (is_null ($header)) continue;

						$roles = isset ($header->roles) ? self::_roles ($header->roles) : null;

						if (!self::_can ($this->role, $roles)) continue;

						$_order = isset ($header->order) ? ((int) $header->order) : 0;

						if ($order > $_order) {
							$order = $_order;
							$found = $file;
							}
						}

					closedir ($dh);
					if (!is_null ($found))
						include ($search_path . '/' . $found);
					break;
				case 'interface':
					if (is_null ($this->module) || is_null ($this->page))
						break;

					$path = WP_CONTENT_DIR . '/' . self::CONTENT_DIR . '/' . self::MODULES_DIR . '/' . $this->module->slug . '/' . self::MODULE_IFACE_DIR . '/' . $this->page->slug . '.php';

					if (file_exists ($path))
						include ($path);

					break;
				}
			}
		if (!$echo) return $content;
		echo $content;
		}

	public function err ($block = null) {
		if (is_null ($block)) return null;
		if (empty ($this->error)) return null;
		$block = strtoupper ($block);
		if (!isset ($this->error[$block])) return null;
		$out = null;

		foreach ($this->error[$block] as $error_code) {
			$path = sprintf ('%s/%04d.html', $this->get ('./', self::ASSETS_DIR . '/' . self::ERROR_DIR), $error_code);
			if (!file_exists ($path)) continue;
			$out .= self::__ (file_get_contents ($path)) . '<br>';
			}

		return $out;
		}

	public function __destruct () {
		}

	private static function _can ($role, $roles) {
		if (is_null ($roles)) return is_null ($role);
		if (is_null ($role)) return FALSE;
		if (is_array ($roles)) return in_array ($role, $roles) || in_array ('*', $roles);
		return TRUE;
		}

	public static function _roles ($text) {
		return (strpos ($text, ',') !== FALSE) ? explode (',', strtolower ($text)) : (empty ($text) ? null : [ $text ]);
		}

	public static function _cmp_order ($a, $b) {
		return $a->order == $b->order ? 0 : ($a->order > $b->order ? -1 : 1);
		}

	public static function __ ($text) {
		return __ ($text, self::TEXTDOMAIN);
		}

	public static function _e ($text) {
		_e ($text, self::TEXTDOMAIN);
		}

	public static function _h ($file, $line) {
		$help = new Help ($file, $line);
		$message_content = $help->get ('text');

		if (current_user_can ('remove_users'))
			$content = '<div class="sd-help-window alert alert-success">
	<i class="arrow"></i>
	<i class="close fui-cross"></i>
	<div class="sd-message-read">
		<span>' . self::__($message_content) . '</span>
		<a href="#" class="sd-message-update"><i class="fui-new"></i>&nbsp;' . self::__ (/*T[*/'Edit Text'/*]*/) . '</a>
	</div>
	<div class="sd-message-update" data-message="Help" data-message-id="' . $help->get () . '">
		<textarea class="form-control" name="message_content">' . $help->get ('text') . '</textarea>
		<div class="row">
			<div class="col-lg-6">
				<a href="" class="btn btn-block btn-danger sd-cancel"><i class="fui-cross"></i>&nbsp;' . self::__ (/*T[*/'Cancel'/*]*/) . '</a>
			</div>
			<div class="col-lg-6">
				<a href="" class="btn btn-block btn-success sd-update"><i class="fui-gear"></i>&nbsp;' . self::__ (/*T[*/'Save Changes'/*]*/) . '</a>
			</div>
		</div>
	</div>
</div>';
		else
			$content = '<div class="sd-help-window alert alert-success">
	<i class="arrow"></i>
	<i class="close fui-cross"></i>
' . self::__($message_content) . '
</div>';
		$content = '<div class="sd-help">
	<i class="fui-question-circle"></i>
	' . $content . '
</div>';

		echo $content;
		}

	public static function _i ($file, $line) {
		$info = new Info ($file, $line);
		$message_content = $info->get ('text');

		if (current_user_can ('remove_users')) :
			?><div class="alert alert-success">
	<div class="sd-message-read">
		<span><?php echo $info->get ('text'); ?></span>
		<a href="" class="sd-message-update"><i class="fui-new"></i>&nbsp;<?php self::_e (/*T[*/'Edit Text'/*]*/); ?></a>
	</div>
	<div class="sd-message-update" data-message="Info" data-message-id="<?php $info->get (); ?>">
		<textarea class="form-control" name="message_content"><?php $info->get ('text'); ?></textarea>
		<div class="row">
			<div class="col-lg-6">
				<a href="" class="btn btn-block btn-danger sd-cancel"><i class="fui-cross"></i>&nbsp;<?php self::_e (/*T[*/'Cancel'/*]*/); ?></a>
			</div>
			<div class="col-lg-6">
				<a href="" class="btn btn-block btn-success sd-update"><i class="fui-gear"></i>&nbsp;<?php self::_e (/*T[*/'Save Changes'/*]*/); ?></a>
			</div>
		</div>
	</div>
</div><?php
		else :
			?><div class="alert alert-success"><?php self::_e($message_content); ?></div><?php
		endif;
		}

	public static function a ($text, $vars, $out = TRUE) {
		if (!empty ($vars) && is_array ($vars))
			foreach ($vars as $key => $value)
				$text = str_replace ('{' . $key . '}', $value, $text);

		if (!$out) return $text;
		echo $text;
		}

	public static function c ($data, $title = '', $echo = TRUE) {
		$chart = json_encode ((object) [
			'title'		=> $title,
			'data'		=> $data
			]);
		$chart = htmlspecialchars ($chart, ENT_QUOTES, 'UTF-8');
		$out = '<div class="sd-chart" data-chart="' . $chart . '"></div>';
		if (!$echo) return $out;
		echo $out;
		}

	public static function r ($key, $filter = null, $index = null) {
		if ($filter == 'interval') {
			$value = [
				'start' => isset ($_POST[$key . '_start']) ? $_POST[$key . '_start'] : (isset ($_GET[$key . '_start']) ? $_GET[$key . '_start'] : null),
				'end' => isset ($_POST[$key . '_end']) ? $_POST[$key . '_end'] : (isset ($_GET[$key . '_end']) ? $_GET[$key . '_end'] : null)
				];

			var_dump ($value);

			$has_null = 0;
			foreach (array_keys ($value) as $key) {
				$value[$key] = trim ($value[$key]);
				$value[$key] = filter_var ($value[$key], FILTER_SANITIZE_STRING);
				if (is_null ($value[$key])) $has_null ++;
				}
			if ($has_null) return null;
			}
		else {
			$value = isset ($_POST[$key]) ? $_POST[$key] : (isset ($_GET[$key]) ? $_GET[$key] : null);
			if (is_null ($value)) return null;
			if (is_array ($value) && !is_null ($index)) {
				if (is_numeric ($index) && (-1 < $index) && ($index < sizeof ($value))) return $value[$index];
				if (is_string ($index) && ($index == 'last')) return $value[sizeof ($value) - 1];
				}
			else {
				$value = trim ($value);
				$value = filter_var ($value, FILTER_SANITIZE_STRING);
				}
			}

		switch ((string) $filter) {
			case 'email':
				$value = filter_var ($value, FILTER_VALIDATE_EMAIL);
				$value = $value === FALSE ? null : $value;
				break;
			case 'phone':
				$value = preg_replace ('/[^0-9+]+/', '', $value);
				$value = preg_replace ('/([0-9])[+]+([0-9])/', '${1}${2}', $value);
				$value = preg_replace ('/^\++/', '+', $value);
				if (strlen ($value) < 10) $value = null;
				break;
			case 'username':
				$lc_username = strtolower ($value);
				$leet = [ 'o', 'i', 'z', 'e', 'a', 's', 'd', 't', 'b', 'g' ];
				$lc_username = str_replace (array_keys ($leet), array_values ($leet), $lc_username);
				$lc_username = preg_replace ('/[^a-z]+/', '', $lc_username);
				$denied = [ 'admin', 'root', 'user', 'master', 'webmaster', 'office', 'proyouth', 'core' ];

				foreach ($denied as $denied_username) {
					if (strpos ($lc_username, $denied_username) !== FALSE) {
						$value = null;
						break;
						}
					}
				break;
			case 'boolean':
				$value = isset ($_POST[$key]) ? : isset ($_GET[$key]);
				break;
			case 'float':
				$value = filter_var ($value, FILTER_VALIDATE_FLOAT);
				$value = $value === FALSE ? null : $value;
				break;
			case 'int':
				$value = filter_var ($value, FILTER_VALIDATE_INT);
				$value = $value === FALSE ? null : $value;
				break;
			case 'url':
				$value = filter_var ($value, FILTER_VALIDATE_URL);
				$value = $value === FALSE ? null : $value;
				break;
			case 'date':
				$value = preg_replace ('/[^0-9\/]+/', '', $value);
				$value = strtotime ($value);
				break;
			case 'interval':
				foreach (array_keys ($value) as $key) {
					$value[$key] = preg_replace ('/[^0-9\/]+/', '', $value[$key]);
					}
				$value = $value['start'] . ':' . $value['end'];
				break;
			}
		return $value;
		}

	public static function p () {
		$out = !empty ($_POST);
		for ($c = 0; $c < func_num_args(); $c++)
			$out = $out && isset ($_POST[func_get_arg ($c)]);
		return $out;
		}

	public static function inp ($opts = []) {
		$type = isset ($opts['type']) ? $opts['type'] : 'string';
		$class = isset ($opts['class']) ? ' ' . $opts['class'] : '';
		$label_split = $input_split = '';
		if (isset ($opts['split'])) {
			list ($label_split, $input_split) = explode (':', $opts['split']);
			$label_split = (int) $label_split;
			$input_split = (int) $input_split;
			if ((0 < $label_split + $input_split) && ($label_split + $input_split < 12)) {
				$factor = floor (12 / ($label_split + $input_split));
				$label_split *= $factor;
				$input_split *= $factor;
				}
			else
				$label_split = $input_split = '';
			}
		if ($label_split) $label_split = ' col-xs-' . $label_split;
		if ($input_split) $input_split = ' col-xs-' . $input_split;
		$label_class = '';
		if (isset ($opts['layout'])) {
			if ($opts['layout'] == 'horizontal') $label_class = 'control-label';
			}
		if (isset ($opts['key'])) {
			$value = isset ($_POST[$opts['key']]) ? $_POST[$opts['key']] : ( isset ($_GET[$opts['key']]) ? $_GET[$opts['key']] : null);
			if (!is_null ($value)) $opts['value'] = $value;
			}

		switch ((string) $type) {
			case 'checkbox':
				?><div class="checkbox"><input type="checkbox" name="<?php echo $opts['key']; ?>" class="i-checks"<?php echo (isset ($opts['value']) && $opts['value']) ? ' checked' : ''?>> <?php
				if (isset ($opts['label'])) :
					self::_e ($opts['label']);
				endif;
				if (isset ($opts['help'])) :
					?><p class="help-block small"><?php echo $opts['help']; ?></p><?php
				endif;
				if (isset ($opts['error'])) :
					?><p class="error-block small"><?php echo $opts['error']; ?></p><?php
				endif;
				?></div><?php
				break;
			case 'file':
				?><div class="form-group file-control"><?php
				if (isset ($opts['label'])) :
					?><label class="<?php echo $label_class; echo $label_split; ?>"><?php self::_e ($opts['label']); ?></label><?php
				endif;
				if ($input_split) :
					?><div class="col-sm-<?php echo $input_split; ?>"><?php
				endif;
				?><div class="input-group"><input class="form-control" type="text"<?php
				if (isset ($opts['value'])) :
					?> value="<?php echo $opts['value'] ?>"<?php
				endif;
				if (isset ($opts['placeholder'])) :
					?> placeholder="<?php echo $opts['placeholder']; ?>"<?php
				endif;
				?>><input class="hidden" type="file" name="<?php echo $opts['key']; ?>" /><span class="input-group-btn"><a href="#" class="btn btn-default file-clear"><i class="pe-7s-close-circle"></i></a><a href="#" class="btn btn-default file-upload"><i class="pe-7s-cloud-upload"></i></a></span></div><?php
				if ($input_split) :
					?></div><?php
				endif;
				?></div><?php
				break;
			case 'select':
			case 'multiselect':
				if (is_string ($opts['options'])) {
					$interval = explode (':', $opts['options']);
					$opts['options'] = [];
					if (sizeof ($interval) == 2) {
						$start = $interval[0];
						$end = $interval[1];
						$step = 1;
						}
					if (sizeof ($interval) == 3) {
						$start = $interval[0];
						$end = $interval[2];
						$step = $interva[1];
						}
					if (isset ($start) && isset ($end) && isset ($step))
						if ($start < $end)
							for ($c = $start; $c <= $end; $c += abs($step))
								$opts['options'][$c] = $c;
						else
							for ($c = $end; $c >= $start; $c -= abs($step))
								$opts['options'][$c] = $c;
					}
				?><div class="form-group<?php echo $class; ?>"><?php
				if (isset ($opts['label'])) :
					?><label class="' . $label_class . ($label_split ? ' col-sm-' . $label_split : '') . '"><?php self::_e ($opts['label']); ?></label> <?php
				endif;
				if ($input_split) :
					?><div class="<?php echo $input_split; ?>"><?php
				endif;
				?><select name="<?php echo $opts['key']; ?>" class="select2 form-control"<?php
				if (isset ($opts['placeholder'])) :
					?> placeholder="<?php $opts['placeholder']; ?>"<?php
				endif;
				echo $type == 'multiselect' ? ' multiple="multiple">' : '>';
				if (is_array ($opts['options']) && !empty ($opts['options'])) :
					foreach ($opts['options'] as $option_key => $option_label) :
						?><option value="<?php echo $option_key; ?>"<?php echo (isset ($opts['value']) && $opts['value'] == $option_key) ? ' selected' : ''; ?>><?php echo $option_label; ?></option><?php
					endforeach;
				endif;
				?></select><?php
				if ($input_split) :
					?></div><?php
				endif;
				?></div><?php
				break;
			case 'interval':
				?><div class="form-group"><?php
				if (isset ($opts['label'])) :
					?><label class="<?php echo $label_class; echo $label_split; ?>"><?php self::_e ($opts['label']); ?></label><?php
				endif;
				if ($input_split) :
					?><div class="<?php echo $input_split; ?>"><?php
				endif;
				?><div class="input-daterange input-group" data-date-format="<?php echo isset ($opts['format']) ? $opts['format'] : 'mm/yyyy'; ?>"><input type="text" class="input-sm form-control" name="<?php echo $opts['key']; ?>_start"><span class="input-group-addon"><?php self:_e(/*T[*/'to'/*]*/); ?></span><input type="text" class="input-sm form-control" name="<?php echo $opts['key']; ?>_end">
                                </div><?php
				if ($input_split) :
					?></div><?php
				endif;
				?></div><?php
				break;
			case 'textarea':
				?><div class="form-group"><?php
				if (isset ($opts['label'])) :
					?><label class="<?php echo $label_class; echo $label_split; ?>"><?php self::_e ($opts['label']); ?></label><?php
				endif;
				if ($input_split) :
					?><div class="<?php echo $input_split; ?>"><?php
				endif;
				?><textarea class="form-control" name="<?php echo $opts['key']; ?>"><?php
				if (isset ($opts['value'])) :
					echo $opts['value'];
				endif;
				?></textarea><?php
				if ($input_split) :
					?></div><?php
				endif;
				?></div><?php
				break;
			default:
				?><div class="form-group"><?php
				if (isset ($opts['label'])) :
					?><label class="<?php echo $label_class; echo $label_split; ?>"><?php self::_e ($opts['label']); ?></label><?php
				endif;
				if ($input_split) :
					?><div class="<?php echo $input_split; ?>"><?php
				endif;
				if (isset ($opts['unit'])) :
					?><div class="input-group"><?php
				endif;
				?><input class="form-control" type="<?php echo $type == 'password' ? 'password' : 'text'; ?>" name="<?php echo $opts['key']; ?>"<?php
				if (isset ($opts['value'])) :
					?> value="<?php echo $opts['value']; ?>"<?php
				endif;
				if (isset ($opts['placeholder'])) :
					?> placeholder="<?php echo $opts['placeholder']; ?>"<?php
				endif;
				?>><?php
				if (isset ($opts['unit'])) :
					?><span class="input-group-addon"><?php echo $opts['unit']; ?></span></div><?php
				endif;
				if ($input_split) :
					?></div><?php
				endif;
				if (isset ($opts['help'])) :
					?><span class="help-block small"><?php echo $opts['help']; ?></span><?php
				endif;
				if (isset ($opts['error'])) :
					?><p class="error-block small"><?php echo $opts['error']; ?></p><?php
				endif;
				?></div><?php
				break;
			}
		}

	public static function mod ($key = null, $opts = null) {
		if (is_string ($key)) {
			switch ($key) {
				case 'header':
?><div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="modal-<?php echo $opts['id']; ?>"><form action="" enctype="multipart/form-data" method="post" class="form-horizontal modal-dialog modal-lg"><?php
					if (isset ($opts['type'])) :
						?><input type="hidden" name="type" value="<?php echo $opts['type']; ?>" /><?php
					endif;
?><div class="modal-content"><div class="color-line"></div><div class="modal-header"><?php if (isset ($opts['name'])) : ?><h4 class="modal-title"><?php self::_e ($opts['name']); ?></h4><?php endif; if (isset ($opts['description'])) : ?><small><?php self::_e ($opts['description']); ?></small><?php endif; ?><div class="progress hidden m-t-xs"><div class="progress-bar progress-bar-warning" role="progressbar">0%</div></div></div><div class="modal-body"><?php
					break;
				case 'footer':
?></div><div class="modal-footer"><div class="col-xs-6"><a href="" class="btn btn-sm btn-block btn-danger" data-dismiss="modal"><?php self::_e (isset ($opts['cancel']) ? $opts['cancel'] : /*T[*/'Cancel'/*]*/); ?></a></div><div class="col-xs-6"><button name="<?php echo $opts['action']; ?>" class="btn btn-sm btn-block btn-info"><?php self::_e ($opts['button']); ?></button></div></div></div></form></div><?php
					break;
				}
			}
		}

	public static function prg ($error = null, $pass = []) {
		$url = $_SERVER['REQUEST_URI'];
		$get = $_GET;

		unset ($get['error']);
		unset ($get['ok']);

		if (!empty ($pass))
			foreach ($pass as $key)
				$get[$key] = $_POST[$key];

		if ($top)
			$get = isset ($get[self::GET_PAGE]) ? [self::GET_PAGE => $get[self::GET_PAGE]] : [];

		if (!is_null ($error))
			$get['error'] = $error;
		else
			$get['ok'] = 1;

		if (($pos = strpos ($url, '?')) !== FALSE)
			$url = !empty ($get) ? (substr ($url, 0, $pos + 1) . http_build_query ($get)) : substr ($url, 0, $pos);
		else
			$url .= '?' . http_build_query ($get);

		header ('Location: ' . $url, 303);
		exit (1);
		}

	public static function cut ($string, $length = 40) {
		if (strlen ($string) < $length + 5) return $string;
		$pos = strpos ($string, ' ', $length);
		if ($pos === FALSE) return substr ($string, 0, $length) . '...';
		if ($pos + 5 > strlen ($string)) return $string;
		return substr ($string, 0, $pos) . '...';
		}

	public static function m ($to, $subject, $body = '', $attachments = []) {
		if (!class_exists ('PHPMailer'))
			include_once (ABSPATH . WPINC . '/class-phpmailer.php');

		$mail = new PHPMailer ();
		$mail->IsSMTP ();

		$mail->SMTPDebug	= 0;
		$mail->Host		= 'smtp.gmail.com';
		$mail->Port		= 587;
		$mail->SMTPAuth		= true;
		$mail->Username		= 'office@pro-youth.ro';
		$mail->Password		= 'orange-juice';
		$mail->SMTPSecure	= 'tls';

		$mail->SetFrom		('office@pro-youth.ro', 'Asociatia Pro-Youth');
		$mail->AddReplyTo	('office@pro-youth.ro', 'Asociatia Pro-Youth');
		$mail->AddAddress	($to);
		$mail->Subject		= $subject;
		$mail->MsgHTML		($body);

		if (!empty ($attachments))
			foreach ($attachments as $path => $name)
				$mail->AddAttachment ($path, $name);

		if (!$mail->send ()) throw new Fault ();
		}

	public static function v ($var, $name = '', $file = '', $line = '') {
		echo '<pre>';
		if (!empty ($file))
			echo 'file: ' . $file . "\n";
		if (!empty ($line))
			echo 'line: ' . $line . "\n";
		if (!empty ($name))
			echo 'var : ' . $name . " = \n";
		echo str_replace ('stdClass::__set_state', '(object)', var_export ($var, TRUE));
		echo '</pre>';
		}
	}
?>
