<?php
/**
 * Class to access Wordpress user data
 *
 * @package CoreSite
 * @copyright Core Security Advisers SRL
 * @author Bogdan Dobrica <bdobrica @ gmail.com>
 * @version 0.1
 *
 */
namespace CoreSite\Core;

class User extends Model {
	const Defaults	= '_coresite_defaults';
	const Layout	= '_coresite_layout';
	const Settings	= '_coresite_settings';

	const Office_Owner	= 1;
	const Office_Employee	= 2;

	public static $version = '1.0.0';

	public static $human = /*T[*/'User'/*]*/;
	
	public static $scheme = [
		'Person'		=> '1:1',

		'Company'		=> 'M:M',
		'Office'		=> 'M:1',

		'Task'			=> 'M:M',
		];

	public static $requires = [
		'Fault',
		'Find',

		'Person',

		'Company',
		'Office',
		];

	public static $DEFAULTS = [
		'promoter_invitations'	=>	5,
		];
	public static $LAYOUT = [
		'dashboard' => [
			/** Rows, of widgets:								*/
			/** array ( array ('widget' => WIDGET, 'size' => {1...12}), ... ), ...		*/
			],
		];
	public static $SETTINGS  = [
		'root_folder'		=>	0,
		'scan_folder'		=>	0,
		'companies_folder'	=>	0,
		'default_office'	=>	0,
		'default_company'	=>	0
		];

	private static $CAPABILITIES = [
		];
	public static $ROLES = [
		'coresite_user'	=> [
			'title'	=> /*T[*/'Core Site User'/*]*/,
			'capabilities' => [
				],
			'group_id' => -96
			],
		'coresite_client' => [
			'title'	=> /*T[*/'Core Site Client'/*]*/,
			'capabilities' => [
				],
			'group_id' => -97
			],
		];
	public static $OFFICE_ROLES = [
		self::Office_Owner	=> [
			'title' => /*T[*/'Office Owner'/*]*/,
			'capabilities' => [
				],
			'group_id' => -1,
			],
		self::Office_Employee	=> [
			'title' => /*T[*/'Office Employee'/*]*/,
			'capabilities' => [
				],
			'group_id' => -2,
			],
		];

	public static $T = 'users';
	public static $K = array (
		'user_login',
		'user_pass',
		'user_nicename',
		'user_email',
		'user_registered',
		'user_status',
		'display_name'
		);
	public static $F = array (
		'new' => array (
			'user_login' => 'Nume utilizator',
			'user_nicename' => 'Nume',
			'password:password' => 'Parola',
			'user_email' => 'Adresa de email',
			'role:array;role_list' => 'Nivel de Acces'
			),
		'view' => array (
			'user_login' => 'Nume utilizator',
			'user_nicename' => 'Nume',
			'user_email' => 'Adresa de email',
			'role:array;role_list' => 'Nivel de Acces'
			),
		'edit' => array (
			'user_login' => 'Nume utilizator',
			'user_nicename' => 'Nume',
			'password:password' => 'Parola',
			'user_email' => 'Adresa de email',
			'role:array;role_list' => 'Nivel de Acces'
			)
		);

	protected static $Q = null;
	private $SRP = '$';

	private $person;

	private $facebook;	/** array, 'id' => user_id, 'token' => long_lived_access_token */
	private $offices;
	private $companies;

	private $defaults;
	private $layout;
	private $settings;

	public function __construct ($data = null) {
		global
			$current_user,
			$wpdb;

		if ($data === FALSE) {
			$current_user = wp_get_current_user ();
			$data = (int) $current_user->ID;
			if (!$data)
				throw new Fault (Fault::Invalid_ID);
			}

		if (is_string ($data) && !is_numeric ($data)) {
			$sql = $wpdb->prepare ('select * from `' . $wpdb->prefix . self::$T . '` where user_login=%s;', array ($data));
			$row = $wpdb->get_row ($sql, ARRAY_A);
			if (!empty($row)) {
				$this->ID = (int) $row['ID'];
				$this->data = $row;
				}
			else
				throw new Fault (Fault::Invalid_ID);
			}		

		parent::__construct ($data);

		if ($this->ID) {
			/**
			 * Adding the defaults meta-keys to this user:
			 * Defaults are settings that can be changed by admins
			 */
			if (($this->defaults = get_user_meta ($this->ID, self::Defaults, TRUE)) === '') {
				add_user_meta ($this->ID, self::Defaults, self::$DEFAULTS, TRUE);
				$this->defaults = self::$DEFAULTS;
				}
			if (empty ($this->defaults)) $this->defaults = self::$DEFAULTS;

			/**
			 * Adding the layout meta-keys to this user:
			 * Layout are settings that this user can change, in order to alter the layout of the app
			 */
			if (($this->layout = get_user_meta ($this->ID, self::Layout, TRUE)) === '') {
				add_user_meta ($this->ID, self::Layout, self::$LAYOUT, TRUE);
				$this->layout = self::$LAYOUT;
				}
			if (empty ($this->layout)) $this->settings = self::$LAYOUT;

			/**
			 * Adding the settings meta-keys to this user:
			 * Settings can be changed by this user alone.
			 */
			if (($this->settings = get_user_meta ($this->ID, self::Settings, TRUE)) === '') {
				add_user_meta ($this->ID, self::Settings, self::$SETTINGS, TRUE);
				$this->settings = self::$SETTINGS;
				}
			if (empty ($this->settings)) $this->settings = self::$SETTINGS;

			/**
			 * Adding the facebook meta-keys to this user:
			 * Facebook meta-keys can be changed only by allowing access to the facebook app.
			 */
			if (class_exists ('\CoreSite\Module\Login\Facebook')) {
				$this->facebook = get_user_meta ($this->ID, \CoreSite\Module\Login\Facebook::MetaKey, TRUE);
				$this->facebook = $this->facebook === '' ? [] : $this->facebook;
				}
		
			if (class_exists ('Company')) {	
				$this->companies = get_user_meta ($this->ID, Company::MetaKey, TRUE);
				$this->companies = $this->companies === '' ? [] : (is_array ($this->companies) ? $this->companies : array ($this->companies));
				}

			if (class_exists ('Office')) {
				$this->offices = get_user_meta ($this->ID, Office::MetaKey, TRUE);
				$this->offices = $this->offices === '' ? [] : (is_array ($this->offices) ? $this->offices : array ($this->offices));
				}
			}

		try {
			$this->person = new Person ($this->data['user_email']);
			}
		catch (Fault $exception) {
			$this->person = null;
			}

		if (is_null ($this->person)) {
			try {
				$this->person = new Person (['email' => $this->data['user_email'], 'owner_id' => $this->ID]);
				$this->person->save ();
				}
			catch (Fault $exception) {
				$this->person = null;
				}
			}
		}

	public function get ($key = null, $opts = null) {
		global
			$wpdb,
			$wp_roles;

		if (is_string ($key)) {
			switch ($key) {
				case 'person':
					return $this->person instanceof \CoreSite\Core\Person ? $this->person : NULL;
					break;
				case 'password':
				case 'confirm_password':
					return '';
					break;
				case 'offices':
					return $this->offices;
					break;
				case 'office_list':
					$offices = new Find ('Office', array (sizeof ($this->offices) == 1 ? sprintf ('id=%d', current(array_keys ($this->offices))) : sprintf ('id in (%s)', implode (',', array_keys ($this->offices)))));
					return $offices->get ('pair', array ('key' => 'slug', 'value' => 'name'));
					break;
				case 'offices_query':
					return sizeof ($this->offices) == 1 ? sprintf ('oid=%d', current(array_keys ($this->offices))) : sprintf ('oid in (%s)', implode (',', array_keys ($this->offices)));
					break;
				case 'companies':
					return $this->companies;
					break;
				case 'company_list':
					return new Find ('Company', array ($this->get ('offices_query')));
					break;
				case 'products':
					$products = array ();
					$sql = $wpdb->prepare ('select pid,bid,buyer from `' . $wpdb->prefix . \CoreSite\Core\Basket::$T . '` where uid=%d;', $this->ID);
					$results = $wpdb->get_results ($sql);
					if ($results)
					foreach ($results as $result)
						$products[] = array (
							'product' => new \CoreSite\Core\Product ((int) $result->pid),
							'company' => new \CoreSite\Core\Company ((int) $result->bid)
							);
					return $products;
					break;
				case 'full_name':
					if (is_object ($this->person)) {
						return $this->person->get ('first_name') . ' ' . $this->person->get ('last_name');
						}
					break;
				case 'role':
					$sql = $wpdb->prepare ('select meta_value from `' . $wpdb->usermeta . '` where meta_key=%s and user_id=%d;', array (
							$wpdb->prefix . 'capabilities',
							$this->ID
							));
					$roles = $wpdb->get_var ($sql);
					$roles = unserialize ($roles);
					if (is_array ($roles)) return current (array_keys ($roles));
					return FALSE;
					break;
				case 'role_list':
					$roles = $wp_roles->roles;
					$out = array ();
					if (!empty ($roles))
					foreach ($roles as $key => $capabilities) {
						if (strpos ($key, 'wp_crm_') === 0)
							$out[$key] = $capabilities['name'];
						else
						if (strpos ($key, 'admin') === 0)
							$out[$key] = $capabilities['name'];
						}
					return $out;
					break;
				case 'capability_list':
					return self::$CAPABILITIES;
					break;
				case 'role_slug':
					return str_replace ('wp_crm_', '', $this->get ('role'));
					break;
				case 'defaults':
					if (in_array ($opts, array_keys ($this->defaults))) return $this->defaults[$opts];
					return FALSE;
					break;
				}
			}

		if (!in_array ($key, static::$K))
			return is_object ($this->person) ? $this->person->get ($key, $opts) : null;
		return parent::get ($key, $opts);
		}

	public function set ($key = null, $value = null) {
		global
			$wpdb,
			$wp_roles;

		if (is_array ($key)) {
			if (isset ($key['facebook'])) {
				update_user_meta ($this->ID, \CoreSite\Module\Login\Facebook::MetaKey, $key['facebook']);
				unset ($key['facebook']);
				}
			if (isset ($key['offices'])) {
				update_user_meta ($this->ID, Office::MetaKey, $key['offices']);
				unset ($key['offices']);
				}
			if (isset ($key['companies'])) {
				update_user_meta ($this->ID, Company::MetaKey, $key['companies']);
				unset ($key['companies']);
				}

			if (isset ($key['user_pass'])) unset ($key['user_pass']);

			if (isset ($key['password'])) {
				if (!empty($key['password']) && !empty($key['confirm_password']) && ($key['password'] == $key['confirm_password']))
					$key['user_pass'] = wp_hash_password ($key['password']);
				
				unset ($key['password']);
				unset ($key['confirm_password']);
				}

			if (isset ($key['role']) && isset ($wp_roles->roles[$key['role']])) {
				$user = new WP_User ($this->ID);
				$user->set_role ($key['role']);
				unset ($key['role']);
				}

			if ($this->person instanceof Person)
				$this->person->set ($key);
			}
		else {
			switch ($key) {
				case 'defaults':
					if (!is_array ($value)) return FALSE;
					foreach ($value as $_k => $_v) {
						if (in_array ($_k, array_keys ($this->defaults)))
							$this->defaults[$_k] = $_v;
						}
					update_user_meta ($this->ID, self::Defaults, $this->defaults);
					return TRUE;
					break;
				case 'settings':
					if (!is_array ($value)) return FALSE;
					foreach ($value as $_k => $_v) {
						if (in_array ($_k, array_keys ($this->settings)))
							$this->settings[$_k] = $_v;
						}
					update_user_meta ($this->ID, self::Settings, $this->settings);
					return TRUE;
					break;
				case 'password':
					$key = 'user_pass';
					$value = wp_hash_password ($value);
					break;
				case 'role':
					if (!isset($wp_roles->roles[$value])) return FALSE;
					$user = new WP_User ($this->ID);
					$user->set_role ($value);
					return TRUE;
					break;
				case 'facebook':
					update_user_meta ($this->ID, \CoreSite\Module\Login\Facebook::MetaKey, $value);
					return TRUE;
					break;
				case 'offices':
					update_user_meta ($this->ID, Office::MetaKey, $value);
					return TRUE;
					break;
				case 'companies':
					update_user_meta ($this->ID, Company::MetaKey, $value);
					return TRUE;
					break;
				}

			if ($this->person instanceof Person && in_array ($key, $this->person->get ('keys')))
				$this->person->set ($key, $value);
			}

		#return parent::set ($key, $value);
		}

	public function is ($what = null, $opts = null) {
		global $current_user;

		if (is_null ($what))
			return ($current_user->ID && ($current_user->ID == $this->ID)) ? TRUE : FALSE;

		if (is_string ($what))
		switch ($what) {
			default:
				return ($current_user->ID && ($current_user->ID == $this->ID)) ? TRUE : FALSE;
			}
		}

	public function save () {
		if (!preg_match ('/^[0-9a-f]{32}$/', strtolower($this->data['user_pass'])))
			$this->data['user_pass'] = md5($this->data['user_pass']);

		parent::save ();
		}

	public function srp ($action = '', $opts = null) {
		switch ((string) $action) {
			case 'register':
				if (!$this->ID) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Invalid_ID);
				add_user_meta ($this->ID, '_wp_crm_verifier', $opts['verifier'], true);
				break;
			case 'init':
				if ($this->SRP != '$') return;
				$this->SRP = get_user_meta ($this->ID, '_wp_crm_verifier', true);
				if ($this->SRP == '') {
					$this->SRP = '$';
					throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Missing_SRP_Verifier);
					}
				$srp = new \CoreSite\Core\SRP ($this->SRP);
				$out = $srp->challenge ($opts['A'], $this->data['user_login']);
				
				if (!session_id()) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Session_Required);

				$_SESSION['M'] = $srp->get ('m64');
				$_SESSION['KEY'] = $srp->get ('key');
				$_SESSION['HAMK'] = $srp->get ('hamk64');

				$out['session'] = session_id ();

				return json_encode ($out);
				break;
			case 'server_check':
				if (!session_id()) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Session_Required);
				if ($_SESSION['M'] != $opts['M']) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::SRP_M_Check_Failed);
				return json_encode (array ('HAMK' => $_SESSION['HAMK']));
				break;
			case 'encrypt':
				if (!session_id()) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Session_Required);
				if (!$_SESSION['KEY']) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Missing_SRP_Key);

				$key = $_SESSION['KEY'];
				
				$data = $opts; #base64_decode ($opts);

				$iv_size = mcrypt_get_iv_size (MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
				$iv = mcrypt_create_iv ($iv_size, MCRYPT_DEV_URANDOM);

				$padlen = strlen($data) + $iv_size - (strlen($data) % $iv_size);

				$data = str_pad ($data, $padlen, "\x00");

				$encd = $iv . mcrypt_encrypt (MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_CBC, $iv);
				$hash = hash ('sha1', $iv . $data, true);

				return base64_encode ($encd . $hash);
				break;
			case 'decrypt':
				if (!session_id()) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Session_Required);
				if (!$_SESSION['KEY']) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Missing_SRP_Key);

				$key = $_SESSION['KEY'];

				$data = base64_decode ($opts);

				$iv_size = mcrypt_get_iv_size (MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
				$iv = substr ($data, 0, $iv_size);

				$hash = substr ($data, -20);
				$data = substr ($data, $iv_size, -20);

				$decd = mcrypt_decrypt (MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_CBC, $iv);
				$dech = hash ('sha1', $iv . $decd, true);
				
				if ($dech != $hash) throw new \CoreSite\Core\Fault (\CoreSite\Core\Fault::Invalid_SRP_Checksum);
				return $decd;
				break;
			}
		}

	Public function auth () {
		global
			$current_user;

		if (is_object ($current_user) && ($current_user instanceof WP_User) && $current_user->ID)
			return FALSE;

		wp_set_auth_cookie ($this->ID);
		$current_user = get_user_by ('id', $this->ID);
		return TRUE;
		}

	public static function install ($uninstall = FALSE) {
		/* stub */
		if (!empty (self::$ROLES))
		foreach (self::$ROLES as $role => $options)
			add_role ($role, $options['title'], $options['capabilities']);
		}

	public function can ($what = null) {
		if (is_null ($what)) return TRUE;
		if (!in_array ($what, array_keys (self::$CAPABILITIES))) return FALSE;
		if (!$this->ID) return FALSE;
		return user_can ($this->ID, $what);
		}

	public function check ($what = null, $against = null) {
		if (is_string ($what)) {
			switch ($what) {
				case 'password':
				case 'pass':
				case 'pwd':
					return wp_check_password (trim($against), $this->data['user_pass'], $this->ID);
					break;
				}
			}
		return FALSE;
		}

	public function log ($message = null, $date = null, $context = null) {
		}	
	}
?>
