<?php
/**
 * Core of *
 */

/**
 * Persons in CoreSite
 *
 * @package CoreSite
 * @subpackage None
 * @copyright Core Security Advisers SRL
 * @author Bogdan Dobrica <bdobrica @ gmail.com>
 * @version 0.1
 *
 */
namespace CoreSite\Core;

class Person extends Model {
	public static $version = '1.0.1';

	public static $human = /*T[*/'Person'/*]*/;

	public static $scheme = [];

	const AVATAR_DIR	= 'avatars';
	const AVATAR_DEFAULT	= 'robot-*.jpg';

	public static $T = 'persons';
	protected static $K = [
		'owner_id',
		'uin',
		'avatar',
		'name',
		'first_name',
		'last_name',
		'address',
		'city',
		'county',
		'country',
		'email',
		'phone',
		'language',
		'stamp',
		];
	protected static $M_K = TRUE;
	public static $F = [
		'create'	=> [
			'first_name'	=> /*T[*/'First Name'/*]*/,
			'last_name'	=> /*T[*/'Last Name'/*]*/,
			'email'		=> /*T[*/'E-Mail Address'/*]*/,
			'phone'		=> /*T[*/'Phone',
			'avatar:file'	=> /*T[*/'Avatar'/*]*/,
			'uin:ro_uin'	=> /*T[*/'UIN'/*]*/,
			'address'	=> /*T[*/'Street Address'/*]*/,
			'country:array;country_list'	=> /*T[*/'Country'/*]*/,
			'county:array;county_list'	=> /*T[*/'County'/*]*/,
			'city:array;city_list'		=> /*T[*/'City'/*]*/,
			],
		'read'		=> [
			'first_name'	=> /*T[*/'First Name'/*]*/,
			'last_name'	=> /*T[*/'Last Name'/*]*/,
			'email'		=> /*T[*/'E-Mail Address'/*]*/,
			'phone'		=> /*T[*/'Phone',
			'avatar:file'	=> /*T[*/'Avatar'/*]*/,
			'address'	=> /*T[*/'Street Address'/*]*/,
			'country:array;country_list'	=> /*T[*/'Country'/*]*/,
			'county:array;county_list'	=> /*T[*/'County'/*]*/,
			'city:array;city_list'		=> /*T[*/'City'/*]*/,
			],
		'update'	=> [
			'first_name'	=> /*T[*/'First Name'/*]*/,
			'last_name'	=> /*T[*/'Last Name'/*]*/,
			'email'		=> /*T[*/'E-Mail Address'/*]*/,
			'phone'		=> /*T[*/'Phone',
			'avatar:file'	=> /*T[*/'Avatar'/*]*/,
			'uin:ro_uin'	=> /*T[*/'UIN'/*]*/,
			'address'	=> /*T[*/'Street Address'/*]*/,
			'country:array;country_list'	=> /*T[*/'Country'/*]*/,
			'county:array;county_list'	=> /*T[*/'County'/*]*/,
			'city:array;city_list'		=> /*T[*/'City'/*]*/,
			],
		'delete'	=> [
			'first_name'	=> /*T[*/'First Name'/*]*/,
			'last_name'	=> /*T[*/'Last Name'/*]*/,
			'email'		=> /*T[*/'E-Mail Address'/*]*/,
			],
		'list'		=> [
			'first_name'	=> /*T[*/'First Name'/*]*/,
			'last_name'	=> /*T[*/'Last Name'/*]*/,
			'email'		=> /*T[*/'E-Mail Address'/*]*/,
			'phone'		=> /*T[*/'Phone',
#			'avatar:file'	=> /*T[*/'Avatar'/*]*/,
			'uin:ro_uin'	=> /*T[*/'UIN'/*]*/,
			'address'	=> /*T[*/'Street Address'/*]*/,
			'country:array;country_list'	=> /*T[*/'Country'/*]*/,
			'county:array;county_list'	=> /*T[*/'County'/*]*/,
			'city:array;city_list'		=> /*T[*/'City'/*]*/,
			]
		];
	protected static $U = [
		'owner_id',
		'email'
		];
	protected static $Q = [
		'`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT',
		'`owner_id` int(11) NOT NULL DEFAULT 0',
		'`uin` varchar(13) NOT NULL DEFAULT \'\'',
		'`avatar` text NOT NULL',
		'`name` varchar(128) NOT NULL DEFAULT \'\'',
		'`first_name` varchar(64) NOT NULL DEFAULT \'\'',
		'`last_name` varchar(64) NOT NULL DEFAULT \'\'',
		'`address` mediumtext NOT NULL',
		'`city` varchar(128) NOT NULL DEFAULT \'\'',
		'`county` varchar(64) NOT NULL DEFAULT \'\'',
		'`country` varchar(64) NOT NULL DEFAULT \'\'',
		'`email` varchar(64) NOT NULL DEFAULT \'\'',
		'`phone` varchar(12) NOT NULL DEFAULT \'\'',
		'`language` varchar(2) NOT NULL DEFAULT \'ro\'',
		'`stamp` int(11) NOT NULL DEFAULT 0',
		'UNIQUE (`owner_id`, `email`)',
		'INDEX (`owner_id`)',
		'FULLTEXT KEY `first_name` (`first_name`,`last_name`,`name`,`email`)',
		];

	private $flags;

	public function __construct ($data = []) {
		global $wpdb;

		if (is_string($data) && strpos($data, '@')) {
			$sql = $wpdb->prepare ('select * from `' . $wpdb->prefix . self::$T . '` where email like %s;', trim($data));
			$data = $wpdb->get_row ($sql, ARRAY_A);
			if (empty($data)) throw new Fault (Fault::Unknown_Email);
			parent::__construct ( $data );
			}
		else
		if (is_string($data) && preg_match ('/^[0-9]{11,}$/', $data)) {
			$sql = $wpdb->prepare ('select * from `' . $wpdb->prefix . self::$T . '` where uin=%d;', $data);
			$data = $wpdb->get_row ($sql, ARRAY_A);
			if (empty($data)) throw new Fault (Fault::Unknown_UIN);
			parent::__construct ( $data );
			}
		else
			parent::__construct ( $data );

		$this->_uin();
		$this->flags = isset($data['flags']) ? (int) $data['flags'] : 0;
		}

	private function _uin () {
		$uin = preg_replace('/[^0-9]+/','',$this->data['uin']);
		if (strlen($uin) != 13) { $this->errors[] = 'UIN number error'; return FALSE; }
		$gender	= substr ($uin, 0, 1);
		$year	= substr ($uin, 1, 2);
		$month	= substr ($uin, 3, 2);
		$day	= substr ($uin, 5, 2);

		if (!in_array($gender, [1,2,5,6])) { $this->errors[] = 'UIN gender error'; return FALSE; }
		if (($gender == 1 || $gender == 2) && ($birthday = strtotime('19'.$year.'-'.$month.'-'.$day)) === FALSE) { $this->errors[] = 'UIN birthday error'; return FALSE; }
		if (($gender == 5 || $gender == 6) && ($birthday = strtotime('20'.$year.'-'.$month.'-'.$day)) === FALSE) { $this->errors[] = 'UIN birthday error'; return FALSE; }

		$key = [2,7,9,1,4,6,3,5,8,2,7,9];
		$control = 0;
		for ($c = 0; $c<12; $c++) $control += ((int)substr($uin,$c,1))*$key[$c];
		$control %= 11;
		if ($control == 10) $control = 1;
		if (substr($uin,12,1) != $control) { $this->errors[] = 'UIN checksum error'; return FALSE; }
		$this->data['gender'] = $gender%2 ? 'M' : 'F';
		$this->data['age'] = date('Y')-date('Y',$birthday);
		if (date('d')-date('d',$birthday) < 0 || date('m')-date('m',$birthday) < 0) $this->data['age']--;
		$this->data['birthday'] = $birthday;
		$this->data['happy_birthday'] = (date('m-d') == date('m-d', $birthday)) ? TRUE : FALSE;
		return TRUE;
		}

	private function _products () {
		global $wpdb;
		$sql = $wpdb->prepare ('select series,number,stamp from `'.$wpdb->prefix.'clients` where uin=%s;', $this->data['uin']);
		$products = $wpdb->get_results ($sql);
		$this->data['products'] = [];
		foreach ($products as $product) {
			$this->data['products'][] = [
				'product' => new Product(['series' => $product->series, 'number' => $product->number]),
				'stamp' => $product->stamp,
				];
			}
		}
	private function _invoices () {
		global $wpdb;
		$sql = $wpdb->prepare ('select iid,stamp from `'.$wpdb->prefix.'clients` where uin=%s;', $this->data['uin']);
		$invoices = $wpdb->get_results ($sql);
		$skip = [];
		$this->data['invoices'] = [];
		foreach ($invoices as $invoice) {
			$skip[] = (int) $invoice->iid;
			$this->data['invoices'][] = [
				'invoice' => new Invoice((int) $invoice->iid),
				'stamp' => $invoice->stamp,
				];
			}
		$sql = $wpdb->prepare ('select id,stamp from `'.$wpdb->prefix.'new_invoices` where bid=%s and buyer=\'person\';', $this->ID);
		$invoices = $wpdb->get_results ($sql);
		foreach ($invoices as $invoice) {
			if (!empty($skip) && in_array($invoice->id, $skip)) continue;
			$this->data['invoices'][] = [
				'invoice' => new Invoice((int) $invoice->id),
				'stamp' => $invoice->stamp,
				];
			}
		}

	public function register ($product, $invoice = NULL, $stamp = NULL) {
		global $wpdb;
		if (!is_object($product)) return FALSE;
		$code = $product->get('active');
		$series = trim(preg_replace('/[^A-Z]+/','',strtoupper($code)));
		$number = intval(preg_replace('/[^0-9]+/','',$code));

		$sql = $wpdb->prepare('insert into wp_clients (iid,uin,pid,series,number,stamp,flags) values (%d,%d,%d,%s,%d,%d,%d);', array (
			is_object($invoice) ? $invoice->get('id') : 0,
			$this->data['uin'],
			$product->get(),
			$series,
			$number,
			$stamp ? $stamp : time(),
			0));
		if (Debug)
		echo "Person::register::sql( $sql )\n";
		$wpdb->query ($sql);
		}

	private function _invoice ($code = '') {
		global $wpdb;
		$series = cs_extract_series($code);
		$number = cs_extract_number($code);
		$invoice_id = $wpdb->get_var($wpdb->prepare('select iid from `'.$wpdb->prefix.'clients` where uin=%s and series=%s and number=%d;', $this->data['uin'], $series, $number));
		$this->data['invoice'] = new Invoice ($invoice_id);
		}

	public function move ($code_a, $code_b) {
		global $wpdb;
		if (!$this->data['uin']) return FALSE;
		$sql = $wpdb->prepare ('update `wp_clients` set series=%s,number=%d where uin=%d and series=%s and number=%d;', array (
			cs_extract_series ($code_b),
			cs_extract_number ($code_b),
			$this->data['uin'],
			cs_extract_series ($code_a),
			cs_extract_number ($code_a),
			));
		$wpdb->query ($sql);
		}

	public function get ($key = null, $opts = null) {
		global
			$cs_theme;

		switch ((string) $key) {
			case 'email':
			case 'e-mail':
			case 'mail':
				return trim (strtolower ( $this->data['email'] ));
				break;
			case 'first_name':
			case 'last_name':
				return str_replace (' ', '-', ucwords( str_replace ( '-', ' ', trim( strtolower( $this->data[$key] )))));
				break;
			case 'name':
				return ucwords( strtolower( $this->data['first_name'] . ' ' . $this->data['last_name'] ));
				break;
			case 'keys':
				return array_merge (parent::get ('keys'), [
					'title',
					'gender',
					'birthday']);
				break;
			case 'gender':
				return ((int) $this->data['uin'][0]) % 2 ?
					/*T[*/'M'/*]*/ :
					/*T[*/'F'/*]*/ ;
				break;
			case 'title':
				$gender = (int) $this->data['uin'][0];
				return $gender%2 ?
						/*T[*/'Dear Sir'/*]*/ : (
						$gender ?
							/*T[*/'Dear Madam'/*]*/ :
							/*T[*/'Dear Sir/Madam'/*]*/ );
				break;
			case 'avatar':
				$avatar = null;

				if (!empty ($this->data['avatar'])) {
					try {
						$avatar = new File ($this->data['avatar'], $this->get ('self') . ':avatar');
						}
					catch (Fault $e) {
						}
					}

				if (is_null ($avatar)) {
					if ($this->ID) {
						$search_path = $cs_theme->get ('./', Theme::ASSETS_DIR . '/' . self::AVATAR_DIR);

						if (strpos (self::AVATAR_DEFAULT, '*') !== FALSE) {
							if (($dh = opendir ($search_path)) === FALSE)
								throw new Fault ();
							$avatars = [];
							while (($file = readdir ($dh)) !== FALSE) {
								if ($file[0] == '.') continue;
								if (fnmatch (self::AVATAR_DEFAULT, $file))
									$avatars[] = $search_path . DIRECTORY_SEPARATOR . $file;
								}
							if (empty ($avatars)) throw new Fault ();
							closedir ($dh);

							$avatar_path = $avatars[rand(0, sizeof($avatars) - 1)];
							$avatar_file = basename ($avatar_path);
							}


						try {
							$avatar = new File ([
								'path'	=> $avatar_path,
								'name'	=> $avatar_file
								], $this->get ('self') . ':avatar');
							$avatar->save ();
							$this->set ('avatar', $avatar->get ('self'));
							}
						catch (Fault $e) {
							}
						}
					else {
						$avatar = null;
						}
					}


				if (empty ($opts)) return $avatar;
				if (is_null ($avatar)) return null;
				return is_array ($opts) ? $avatar->get (isset ($opts['key']) ? $opts['key'] : null, isset ($opts['opts']) ? $opts['opts'] : null) : $avatar->get ($opts);
				break;
			}

		return parent::get ($key, $opts);
		/*
		HIST: sa nu uitam proprietatile
		*/
		if (!$key) return $this->ID;
		if ($key == 'keys') return $this->keys;
		if ($key == 'invoice') {
			$this->_invoice($value);
			return $this->data['invoice'];
			}
		if ($key == 'data') return $this->data;
		if ($key == 'initial') {
			$initials = preg_replace ('/[ ;,.-]+/', ' ', strtoupper($this->data['id_father']));
			$initials = mb_split (' ', $initials);
			$out = [];
			foreach ($initials as $initial) {
				$initial = trim($initial);
				if (!$initial) continue;
				$out[] = mb_substr($initial, 0, 1).'.';
				}
			if (empty($out)) return '';
			return implode('-', $out);
			}
		if ($this->data[$key]) return $this->data[$key];
		if ($key == 'invoices') {
			$this->_invoices();
			return $this->data[$key];
			}
		if ($key == 'products') {
			$this->_products();
			return $this->data[$key];
			}
		if ($key == 'products icons') {
			$this->_products();
			$out = [];
			if (!empty($this->data['products']))
				foreach ($this->data['products'] as $product)
					$out[] = $product['product']->get('icon');
			return $out;
			}
		if ($key == 'type') return 'person';
		if ($key == 'voucher') {
			$voucher = $wpdb->get_var($wpdb->prepare('select id from `'.$wpdb->prefix.'clients` where uin=%s and series=%s and number=%s;', $this->data['uin'], cs_extract_series($value), cs_extract_number($value)));
			return 'VCX'.str_pad($voucher, 6, 0, STR_PAD_LEFT);
			}
		if ($key == 'cnfpa') {
			return $wpdb->get_var($wpdb->prepare('select cnfpa from `'.$wpdb->prefix.'clients` where uin=%s and series=%s and number=%s;', $this->data['uin'], cs_extract_series($value), cs_extract_number($value)));
			}
		if ($key == 'grade') {
			return (float) $wpdb->get_var($wpdb->prepare('select grade from `'.$wpdb->prefix.'clients` where uin=%s and series=%s and number=%s;', $this->data['uin'], cs_extract_series($value), cs_extract_number($value)));
			}
		if ($key == 'diploma') {
			return (int) $wpdb->get_var($wpdb->prepare('select diploma from `'.$wpdb->prefix.'clients` where uin=%s and series=%s and number=%s;', $this->data['uin'], cs_extract_series($value), cs_extract_number($value)));
			}
		if ($key == 'when') {
			if (!is_object($value)) return FALSE;
			return $wpdb->get_var ($wpdb->prepare ('select stamp from `'.$wpdb->prefix.'clients` where uin=%s and series=%s and number=%s;', $this->data['uin'], $value->get('current series'), $value->get('current number')));
			}
		if ($key == 'invoice') return $this->_invoice($value);
		return FALSE;
		}

	public function is ($key = null, $opts = null) {
		global $wpdb;
		/*
		if ($key == 'customer' || $key == 'paying customer') {
			if ((intval($this->flags) & 2) == 2) return TRUE;
			if (is_numeric($value))
				$sql = $wpdb->prepare ('select iid,flags from `'.$wpdb->prefix.'clients` where uin=%s and iid!=%d;', $this->data['uin'], $value);
			else
				$sql = $wpdb->prepare ('select iid,flags from `'.$wpdb->prefix.'clients` where uin=%s;', $this->data['uin']);

			$clients = $wpdb->get_results ($sql);
			if (empty($clients)) return FALSE;
			if ($key == 'customer') return TRUE;
			$paying = FALSE;
			foreach ($clients as $client) {
				$sql = $wpdb->prepare ('select paidby from `'.$wpdb->prefix.'new_invoices` where id=%d;', $client->iid);
				$paidby = $wpdb->get_var ($sql);
				if ($paidby != 'none') $paying = TRUE;
				else {
					if ((((int) $client->flags) & 1) == 1) $paying = TRUE;
					}
				}
			return $paying;
			}
		*/
		}

	public function set ($key = null, $value = null) {
		parent::set ($key, $value);

		/*
		HIST: paying customers = sparla, used for discount only
		*//*
		if ($key == 'paying customer') {
			$this->flags = $value ? ((intval($this->flags)) | 2) : ((intval($this->flags)) & (~2));
			if ($this->ID) {
				$wpdb->query ($wpdb->prepare ('update `'.$wpdb->prefix.'persons` set flags=%d where id=%d;', $this->flags, $this->ID));
				}
			}
		*/

		return FALSE;
		}

	public function auth ($pass = null, $check = true) {
		if (!$check) {
			$hash = sha1 ($data['stamp'] . $pass);
			$this->set ('password', $hash);
			return $hash;
			}
		if (!$data['password']) return TRUE;
		if (sha1 ($data['stamp'] . $pass) == $data['password']) return TRUE;
		return FALSE;
		}

	public function save () {
		global $wpdb;

		/*
		INFO: Fix the missing names on the invoices generated from the admin iface
		*/
		if (($this->data['first_name'] && $this->data['last_name']) && !$this->data['name'])
			$this->data['name'] = $this->data['first_name'] . ' ' . $this->data['last_name'];
		if ((!$this->data['first_name'] && !$this->data['last_name']) && $this->data['name']) {
			$names = explode (' ', trim ($this->data['name']));
			if (count ($names) < 2) {
				$this->data['first_name'] = $names[0];
				$this->data['last_name'] = $names[0];
				}
			else {
				$this->data['last_name'] = array_pop ($names);
				$this->data['first_name'] = implode (' ', $names);
				}
			}

		$missing = 0;
		foreach (self::$U as $key)
			$missing += $this->data[$key] ? 0 : 1;

		if ($missing == count (self::$U)) throw new Fault (Fault::Saving_Failure);

		parent::save ();
		}

	public function __destruct () {
		}
	};
?>
