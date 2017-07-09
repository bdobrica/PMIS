<?php
/**
 * Core of CoreSite
 */

/**
 * List Objects
 *
 * @category
 * @package SalesDrive
 * @subpackage None
 * @copyright Core Security Advisers SRL
 * @author Bogdan Dobrica <bdobrica @ gmail.com>
 * @version 0.1
 *
 */
namespace CoreSite\Core;

class Find {
	private $sql;
	private $list;

	public function __construct ($object, $filter = null, $limits = null, $order = null) {
		global
			$wpdb,
			$cs_theme;
		$this->list = [];

		$q_order = [];
		if (!is_null ($order)) {
			if (is_string ($order)) $order = [ $order ];
			foreach ($order as $item) {
				$order_by = null;
				$direction = 'asc';

				if (strpos ($item, ' '))
					list ($order_by, $direction) = explode (' ', strtolower ($item));
				else
					$order_by = strtolower ($item);

				if (is_null ($order_by)) continue;
				$order_by = trim ($order_by);
				$direction = trim ($direction);

				if (!in_array ($order_by, $object::$K)) continue;

				$q_order[] = $order_by . ' ' . $direction;
				}
			}
		if (!empty ($q_order)) $q_order = implode (',', $q_order);

		$q_limits = [];
		if (!is_null ($limits)) {
			}

		if (is_string ($object) && class_exists ($object) && property_exists ($object, 'Q')) {
			$all = $wpdb->get_var ($wpdb->prepare ('SELECT count(1) FROM `' . $wpdb->prefix . $object::$T . '` WHERE owner_id=%d', [
				$cs_theme->get ('user')->get ()
				]));
			Theme::v ($all);
			$sql = $wpdb->prepare ('SELECT * FROM `' . $wpdb->prefix . $object::$T . '` WHERE owner_id=%d ORDER BY id LIMIT 10', [
				$cs_theme->get ('user')->get ()
				]);

			$results = $wpdb->get_results ($sql, ARRAY_A);
			if (!empty ($results))
				foreach ($results as $result) {
					if (isset ($result['id']))
						$this->list[$result['id']] = new $object ($result);
					else
						$this->list[] = new $object ($result);
					}
			}
		}

	public function get ($key = null, $opts = null) {
		if (is_string ($key)) {
			switch ($key) {
				case 'sizeof':
				case 'count':
					return sizeof ($this->list);
					break;
				case 'first':
					reset ($this->list);
					return current ($this->list);
					break;
				case 'last':
					if (empty ($this->list)) return null;
					$out = end ($this->list);
					reset ($this->list);
					return $out;
					break;
				case 'select':
					if (empty ($this->list)) return [];
					$out = [];
					foreach ($this->list as $id => $object)
						$out[$id] = $object->get ($opts);
					return $out;
					break;
				case 'min':
					if (empty ($this->list)) return FALSE;
					$out = null;
					foreach ($this->list as $object) {
						$value = $object->get ($opts);
						$out = is_null ($out) ? $value : ($out > $value ? $value : $out);
						}
					return $out;
					break;
				case 'max':
					if (empty ($this->list)) return FALSE;
					$out = null;
					foreach ($this->list as $object) {
						$value = $object->get ($opts);
						$out = is_null ($out) ? $value : ($out < $value ? $value : $out);
						}
					return $out;
					break;
				case 'sum':
					if (empty ($this->list)) return FALSE;
					$out = 0;
					foreach ($this->list as $object)
						$out += $object->get ($opts);
					return $out;
					break;
				case 'average':
					if (empty ($this->list)) return FALSE;
					$out = 0;
					foreach ($this->list as $object)
						$out += $object->get ($opts);
					return $out / sizeof ($this->list);
					break;
				}
			}
		return $this->list;
		}

	public function is ($what = null) {
		if (is_string ($what)) {
			switch ($what) {
				case 'empty':
					return empty ($this->list);
					break;
				}
			}
		}

	public function sort ($by = null, $dir = 'asc') {
		if (empty ($this->list))
			$this->get ();

		if (!empty ($this->list)) {
			if (is_null ($by)) {
				$first = current ($this->list);
				$keys = $first instanceof CS_Model ? $first->get ('keys') : [];

				if (is_array ($keys) && !empty ($keys)) {
					if (in_array ('order', $keys))		
						uasort ($this->list, [$this, '_cmp_ord']);
					else
					if (in_array ('stamp', $keys))
						uasort ($this->list, [$this, '_cmp_stm']);
					else
						uasort ($this->list, [$this, '_cmp__id']);
					}
				}
			elseif (is_string ($by)) {
				switch ($by) {
					case 'id':
						uasort ($this->list, [$this, '_cmp__id']);
						break;
					case 'begin':
						uasort ($this->list, [$this, '_cmp_bgn']);
						break;
					case 'end':
						uasort ($this->list, [$this, '_cmp_end']);
						break;
					case 'order':
						uasort ($this->list, [$this, '_cmp_ord']);
						break;
					case 'stamp':
						uasort ($this->list, [$this, '_cmp_stm']);
						break;
					}
				}
			if ($dir == 'desc')
				$this->list = array_reverse ($this->list, TRUE);
			}
		}
	
	private function _cmp__id ($a, $b) {
		$va = $a->get ();
		$vb = $b->get ();
		return $va == $vb ? 0 : ($va < $vb ? -1 : 1);
		}

	private function _cmp_bgn ($a, $b) {
		$va = $a->get ('begin');
		$vb = $b->get ('begin');
		return $va == $vb ? 0 : ($va < $vb ? -1 : 1);
		}

	private function _cmp_end ($a, $b) {
		$va = $a->get ('end');
		$vb = $b->get ('end');
		return $va == $vb ? 0 : ($va < $vb ? -1 : 1);
		}

	private function _cmp_ord ($a, $b) {
		$va = $a->get ('order');
		$vb = $b->get ('order');
		return $va == $vb ? 0 : ($va < $vb ? -1 : 1);
		}

	private function _cmp_stm ($a, $b) {
		$va = $a->get ('stamp');
		$vb = $b->get ('stamp');
		return $va == $vb ? 0 : ($va < $vb ? -1 : 1);
		}
	}
?>
