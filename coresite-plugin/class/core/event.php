<?php
/**
 * Core of CoreSite
 */

/**
 * Event Class
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

class Event {
	public static $version = '1.0.1';

	public static $human = 'Event';

	public static $scheme = [];

	const EVENTS_DIR	= 'events';
	const EVENTS_DIR_MASK	= 0755;
	const EVENTS_REGISTRY	= 'registry.php';

	/**
	 * @var string $slug
	 */
	private $slug;
	/**
	 * @var string $path
	 */
	private $path;
	/**
	 * @var array $actions
	 */
	private $actions;
	/**
	 * @var object storage
	 * @see State
	 */
	private $storage;

	public function __construct ($data = null, $storage = null) {
		return;
		$this->slug = Model::slug ($data);
		$this->path = dirname (__DIR__) . DIRECTORY_SEPARATOR . self::EVENTS_DIR . DIRECTORY_SEPARATOR . $this->slug;

		$this->storage = $storage;

		if (!file_exists ($this->path))
			if (!@mkdir ($this->path, self::EVENTS_DIR_MASK, TRUE))
				throw new Fault ();

		if (!file_exists ($this->path . DIRECTORY_SEPARATOR . self::EVENTS_REGISTRY))
			if (!@file_put_contents ($this->path . DIRECTORY_SEPARATOR . self::EVENTS_REGISTRY, '<?php' . "\n" . '$actions = [];' . "\n" . '?>'))
				throw new Fault ();

		include ($this->path . DIRECTORY_SEPARATOR . self::EVENTS_REGISTRY);

		$this->actions = isset ($actions) ? $actions : [];
		}

	public function trigger ($data = null, $filter = null) {
		if (empty ($this->actions)) return;
		foreach ($this->actions as $action) {
			$callback = $action[1];

			if (strpos ($callback, 'Event::') === 0) {
				$bubble = call_user_func ([$this, 'callback'], substr ($callback, 14), $data);
				if ($bubble == FALSE) break;
				continue;
				}
			if (is_callable ($callback)) {
				$bubble = call_user_func ($callback, $data);
				if ($bubble == FALSE) break;
				}
			}
		}

	public static function callback ($action = null, $data = null) {
		$bubble = TRUE;
		if (!file_exists ($this->path . DIRECTORY_SEPARATOR . $action . '.php')) return TRUE;

		/**
		 * @var mixed $data
		 * @var bool $bubble
		 */
		include ($this->path . DIRECTORY_SEPARATOR . $action . '.php');

		return $bubble;
		}

	private function _pack ($n, $a) {
		$o = '<?php' . "\n" . '$' . $n . ' = [';
		if (empty ($a)) return $o . '];' . "\n" . '?>';
		foreach ($a as $v)
			$o .= "\n\t[" . $v[0] . ',' . $v[1] . '],';
		return $o . "\n\t];\n?>";
		}

	public function connect ($callback, $order = 0) {
		if (file_exists ($this->path . DIRECTORY_SEPARATOR . $callback . '.php'))
			$callback = 'Event::' . $callback;
		else
		if (!is_callable ($callback))
			throw new Fault ();
		$order = (int) $order;

		$found = null;
		if (!empty ($this->actions))
			foreach ($this->actions as $key => $action)
				if ($action[1] == $callback)
					$found = $key;

		if (!is_null ($found))
			$this->actions[$found][0] = $order;
		else
			$this->actions[] = [$order, $callback];

		usort ($this->actions, ['Event', 'action_compare']);

		if (!@file_put_contents ($this->path . DIRECTORY_SEPARATOR . self::EventRegistry, self::_pack ('actions', $this->actions)))
			throw new Fault ();
		}

	public function disconnect ($callback) {
		if (empty ($this->actions)) return FALSE;
		$found = null;
		foreach ($this->actions as $key => $action)
			if ($action[1] == $callback)
				$found = $key;
		if (is_null ($found)) return FALSE;
		unset ($this->actions[$found]);
		usort ($this->actions, ['Event', 'action_compare']);

		if (!@file_put_contents ($this->path . DIRECTORY_SEPARATOR . self::EventRegistry, self::_pack ('actions', $this->actions)))
			throw new Fault ();
		}

	private static function _scan () {
		}

	public static function install ($uninstall = FALSE) {
		}

	private static function action_compare ($a, $b) {
		return $a[0] == $b[0] ? 0 : ($a[0] < $b[0] ? -1 : 1);
		}
	}
?>
