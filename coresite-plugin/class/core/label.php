<?php
/**
 * Core of CoreSite
 */

/**
 * Label Class
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

class Label {
	public static $T = 'labels';

	public function __construct () {
		}

	public function get ($key = null, $opts = null) {
		}

	public function set ($key = null, $opts = null) {
		}

	public function save () {
		}

	public function __destruct () {
		}

	public static function install ($uninstall = FALSE) {
		global $wdpb;

		$sql = $uninstall ?
			'DROP TABLE `' . $wpdb->prefix . static::$T . '`;' :
			'CREATE TABLE `' . $wpdb->prefix . static::$T . '` (
			`id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`name` varchar(128) NOT NULL DEFAULT \'\',
			`slug` varchar(128) NOT NULL DEFAULT \'\',
			UNIQUE (`slug`),
			INDEX (`name`)
			)';
		if (!($uninstall xor ($wpdb->get_var ('SHOW TABLES LIKE \'' . $wpdb->prefix . static::$T . '\';') == ($wpdb->prefix . static::$T)))) {
			if (!defined ('CS_DEBUG'))
				$wpdb->query ($sql);
			else
				Model::debug ($sql);
			}

		$sql = $uninstall ?
			'DROP TABLE `' . $wpdb->prefix . static::$T . '_taxonomy`;' :
			'CREATE TABLE `' . $wpdb->prefix . static::$T . '_taxonomy` (
			`id` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`label_id` int NOT NULL DEFAULT 0,
			`slug` varchar(32) NOT NULL DEFAULT \'\',
			`parent` int NOT NULL DEFAULT 0,
			`count` int NOT NULL DEFAULT 0,
			UNIQUE (`label_id`, `slug`),
			INDEX (`slug`)
			)';
		if (!($uninstall xor ($wpdb->get_var ('SHOW TABLES LIKE \'' . $wpdb->prefix . static::$T . '_taxonomy\';') == ($wpdb->prefix . static::$T . '_taxonomy')))) {
			if (!defined ('CS_DEBUG'))
				$wpdb->query ($sql);
			else
				Model::debug ($sql);
			}

		$sql = $uninstall ?
			'DROP TABLE `' . $wpdb->prefix . static::$T . '_link`;' :
			'CREATE TABLE `' . $wpdb->prefix . static::$T . '_link` (
			`object_id` int NOT NULL DEFAULT 0,
			`label_taxonomy_id` int NOT NULL DEFAULT 0,
			`label_order` int NOT NULL DEFAULT 0,
			PRIMARY KEY (`object_id`, `label_taxonomy_id`),
			INDEX (`label_taxonomy_id`)
			)';
		if (!($uninstall xor ($wpdb->get_var ('SHOW TABLES LIKE \'' . $wpdb->prefix . static::$T . '_link\';') == ($wpdb->prefix . static::$T . '_link')))) {
			if (!defined ('CS_DEBUG'))
				$wpdb->query ($sql);
			else
				Model::debug ($sql);
			}
		}
	}
?> 
