<?php
if (defined ('CS_REQUEST')) {
	if (isset ($_POST['login_user'])) {
		if (isset ($_POST['username']) && isset ($_POST['password'])) {
			$user = wp_signon ([
				'user_login'	=> self::r ('username'),
				'user_password'	=> self::r ('password')
				], FALSE);

			if (is_wp_error ($user))
				$error .= 'B2'; /*E[Invalid username and/or password.]*/
			else
				self::prg ();
			}

		if (!isset ($_POST['username']))
			$error .= 'A3';	/*E[The username field is mandatory.]*/

		if (!isset ($_POST['password']))
			$error .= 'B5'; /*E[The password field is mandatory.]*/

		self::prg ($error);
		}
	}
?>
