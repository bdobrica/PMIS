<?php
/**
 * Core of CoreSite
 */

/**
 * Access Control List Class
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

class ACL extends Model {
	public static $version = '1.0.0';
	public static $human = 'ACLs';

	public static $T = 'acls';

	public static $permissions = [
		'Full Control',
		'Find Children',
		'Read',
		'Get Data',
		'Get Metadata',
		'Create Object',
		'Update Object',
		'Set Data',
		'Set Metadata',
		'Delete',
		'Read ACLs',
		'Change ACLs',
		'Take Ownership'
		];

	protected static $K = [
		'user_id',
		'object_id',
		'object',
		'acl'
		];

	public static $F = [
		'create'	=> [
			],
		'read'		=> [
			],
		'update'	=> [
			],
		'delete'	=> [
			],
		'list'		=> [
			]
		];

	protected static $Q = [
		'`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT',
		'`user_id` int(11) NOT NULL DEFAULT 0',
		'`object_id` int(11) NOT NULL DEFAULT 0',
		'`object` varchar(256) NOT NULL DEFAULT \'\'',
		'UNIQUE (`user_id`, `object_id`, `object`)',
		'INDEX (`user_id`)',
		'INDEX (`object_id`)',
		'INDEX (`object`)',
		];

	private $attributes;

	public function get ($key = null, $opts = null) {
		if (is_string ($key)) {
			switch ($key) {
				case 'attributes':
					if (!is_null ($this->attributes)) return $this->attributes;
					$this->attributes = [];

					foreach (self::$permissions as $key => $attribute) {
						$mask = 1 << $key;
						if ($mask == ($mask & ((int) $this->data['acl'])))
							$this->attributes[] = $attribute;
						}
					break;
				}
			}
		return parent::get ($key, $opts);
		}

	public function set ($key = null, $value = null) {
		if (is_string ($key)) {
			switch ($key) {
				case 'attributes':
					$acl = 0;
					$this->attributes = [];
					if (!empty ($value))
						foreach ($value as $attribute) {
							$key = array_search ($attribute, self::$permissions);
							if ($key === FALSE) continue;
							$acl = $acl | (1 << $key);
							$this->attributes[] = $attribute;
							}
					$key = 'acl';
					$value = $acl;
					break;
				}
			}
		if (is_array ($key)) {
			if (in_array ('attributes', $key)) {
				$acl = 0;
				$this->attributes = [];
				if (!empty ($key['attributes']))
					foreach ($key['attributes'] as $attribute) {
						$key = array_search ($attribute, self::$permissions);
						if ($key === FALSE) continue;
						$acl = $acl | (1 << $key);
						$this->attributes[] = $attribute;
						}
				unset ($key['attributes']);
				$key['acl'] = $acl;
				}
			}
		return parent::set ($key, $value);
		}

	}
