<?php
/**
 * Core of *
 */

/**
 * Storage Class
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

class Storage {
	public static $K = [
		'user',
		'player',
		'scenario',
		'locale'
		];

	private $data;

	public function __construct () {
		$this->data = [];

		if (!session_id ())
			session_start ();

		$this->_unpack ();
		}

	public function set ($key = null, $value = null) {
		if (is_string ($key)) {
			if (in_array ($key, self::$K)) {
				if (is_null ($value))
					unset ($this->data[$key]);
				else
					$this->data[$key] = $value;
				}
			}
		else
		if (is_array ($key)) {
			foreach ($key as $_key => $_value) {
				if (in_array ($_key, self::$K)) {
					if (is_null ($_value))
						unset ($this->data[$_key]);
					else
						$this->data[$_key] = $_value;
					}
				}
			}
		$this->_pack ();
		}

	public function get ($key = null, $opts = null) {
		if (is_string ($key)) {
			if (in_array ($key, self::$K))
				return isset ($this->data[$key]) ? $this->data[$key] : null;
			}
		}

	private function _pack () {
		if (!session_id()) return;
		$_SESSION[__CLASS__] = serialize ($this->data);
		}

	private function _unpack () {
		if (!session_id()) return;
		if (isset ($_SESSION[__CLASS__])) {
			$data = unserialize ($_SESSION[__CLASS__]);
			foreach ($data as $key => $value) {
				if (in_array ($key, self::$K)) {
					$this->data[$key] = $value;
					}
				}
			}
		}
	}
?>
