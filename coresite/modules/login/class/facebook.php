<?php
namespace CoreSite\Module\Login;

class Facebook {
	const MetaKey = '_coresite_facebook';

	private static $PERMISSIONS = [ 'email' ];

	protected static $K = array (
		'first_name',
		'last_name',
		'name',
		'email',
		'picture'
		);

	protected $data;
	private $token;
	private $fb;

	protected $ID;

	public function __construct ($data = null) {
		$data = [
			'app_id'		=> '441367249358039',
			'app_secret'		=> '0759529417af95886f878699823e64f3',
			];
		$this->data = [
			'app_id'		=> $data['app_id'],
			'app_secret'		=> $data['app_secret'],
			];
		$this->fb = new \Facebook\Facebook ([
			'app_id'		=> $data['app_id'],
			'app_secret'		=> $data['app_secret'],
			'default_graph_version'	=> 'v2.4'
			]);
		}

	public function get ($key = null, $opts = null) {
		if (is_string ($key)) {
			switch ($key) {
				case 'login_url':
					$helper = $this->fb->getRedirectLoginHelper ();
					return $helper->getLoginUrl (WP_CONTENT_URL . '/' . \CoreSite\Core\Theme::CONTENT_DIR . '/' . \CoreSite\Core\Theme::MODULES_DIR . '/' . basename (dirname (__DIR__)) . '/' .  \CoreSite\Core\Theme::MODULE_PROC_DIR . '/facebook.php'  , self::$PERMISSIONS);
					break;
				case 'me':
					$responses = $this->fb->sendBatchRequest ([
						'me' => $this->fb->request ('GET', '/me', ['fields' => 'id,first_name,last_name,name,email']),
						'picture' => $this->fb->request ('GET', '/me/picture', ['width' => 500])
						]);

					$data = $responses['me']->getGraphObject ()->asArray ();
					$this->ID = $data['id'];
					foreach (self::$K as $key) if (isset ($data[$key])) $this->data[$key] = $data[$key];

					$headers = $responses['picture']->getHeaders ();
					$this->data['picture'] = [];
					foreach ($headers as $header) {
						if (strtolower ($header['name']) == 'location') $this->data['picture']['url'] = $header['value'];
						if (strtolower ($header['name']) == 'content-type') $this->data['picture']['type'] = $header['value'];
						}

					return $this->data;
					break;
				case 'token':
					return $this->token;
					break;
				default:
					if (in_array ($key, self::$K)) {
						if (!$this->ID)
							$this->get ('me');
						return $this->data[$key];
						}
					return $this->ID;
				}
			}
		return $this->ID;
		}

	public function login () {
		$helper = $this->fb->getRedirectLoginHelper ();

		/**
		 * Getting access token.
		 */
		try {
			$accessToken = $helper->getAccessToken ();
			}
		catch (\Facebook\Exceptions\FacebookResponseException $exception) {
			return FALSE;
			}
		catch (\Facebook\Exceptions\FacebookSDKException $exception) {
			return FALSE;
			}

		if (!isset ($accessToken)) {
			return FALSE;
			}

		/**
		 * Access token recovered.
		 */
		$oAuth2Client = $this->fb->getOAuth2Client ();
		$tokenMetadata = $oAuth2Client->debugToken ($accessToken);
		
		/**
		 * Validating token metadata.
		 */
		try {
			$tokenMetadata->validateAppId ($this->data['app_id']);
			$tokenMetadata->validateExpiration ();
			}
		catch (\Facebook\Exceptions\FaceboookSDKException $exception) {
			return FALSE;
			}

		/**
		 * Get long-lived token.
		 */
		if (!$accessToken->isLongLived()) {
			try {
				$accessToken = $oAuth2Client->getLongLivedAccessToken ($accessToken);
				}
			catch (\Facebook\Exceptions\FacebookSDKException $exception) {
				return FALSE;
				}
			}

		$this->fb->setDefaultAccessToken ($this->token = $accessToken->getValue());
		/**
		 * Set-up a new user, if it does not exist.
		 */
		$this->get ('me');

		if (($user = get_user_by ('email', $this->get ('email'))) === FALSE) {
			$password = wp_generate_password ($length = 16);
			$user = wp_create_user ($this->get ('email'), $password, $this->get ('email'));

			if ($user instanceof WP_Error) {
				$user = null;
				return FALSE;
				}

			$user = get_user_by ('id', $user);

			/**
			 * Search for the default role
			 */
			$default_role = null;
			foreach (\CoreSite\Core\User::$ROLES as $role => $role_data)
				if (isset ($role_data['default']) && $role_data['default'])
					$default_role = $role;
			if (!is_null ($default_role))
				$user->set_role ($default_role);
			}

		if (!is_null ($user)) {
			$cs_user = new \CoreSite\Core\User ($user->ID);
			$cs_user->set ([
				'facebook' => [
					'id' => $this->get ('id'),
					'token' => $this->get ('token')
					],
				'user_nicename' => $this->get ('name'),
				'display_name' => $this->get ('name')
				]);


			try {
				$person = new \CoreSite\Core\Person (['email' => $this->get ('email')]);
				$person->set ([
					'first_name'	=> $this->get ('first_name'),
					'last_name'	=> $this->get ('last_name'),
					]);
				}
			catch (\CoreSite\Core\Fault $exception) {
				$person = new \CoreSite\Core\Person ();
				$person->set ([
					'email'		=> $this->get ('email'),
					'name'		=> $this->get ('name'),
					'first_name'	=> $this->get ('first_name'),
					'last_name'	=> $this->get ('last_name'),
					]);
				try {
					$person->save ();
					}
				catch (\CoreSite\Core\Fault $exception) {
					unset ($person);
					$person = null;
					}
				}

			$cs_user->auth ();
			return TRUE;
			}

		return FALSE;
		}
	}
?>
