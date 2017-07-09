<?php
/**
 * Core of CoreSite
 */

/**
 * Plugin Class. The CoreSite plugin is an instance of this class.
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

class Plugin {
	const PluginSlug	= 'cs_plugin';

	private static $CustomPosts	= [
		'mail_templates' => [
			'singular_name'	=> /*T[*/'Mail Template'/*]*/,
			'plural_name'	=> /*T[*/'Mail Templates'/*]*/,
			],
		];

	public function __construct () {
		add_action ('plugins_loaded', [$this, 'translation']);
		add_action ('init', [$this, 'custom_objects']);
		}

	public function translation () {
		load_plugin_textdomain (self::PluginSlug, false, dirname (__DIR__) . '/lang/');
		}

	public function custom_objects () {
		if (!empty (self::$CustomPosts))
			foreach (self::$CustomPosts as $posts_slug => $posts_data)
				register_post_type ($posts_slug, [
					'labels'	=> [
							'name'		=> Theme::__ ($posts_data['plural_name']),
							'singular_name'	=> Theme::__ ($posts_data['singular_name']),
							],
					'show_ui'	=> true,
					'show_in_menu'	=> true,
					'menu_position'	=> 20
					]);
		
		}
	}
?>
