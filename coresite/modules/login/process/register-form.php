<?php
if (defined ('CS_REQUEST')) {
	var_dump ($this->error);
	if (isset ($_POST['register_user'])) {
		$username = self::r ('username', 'username');
		$password = self::r ('password');
		$confirmp = self::r ('confirm_password');
		$email = self::r ('email', 'email');
		$phone = self::r ('phone', 'phone');
		$terms = self::r ('terms', 'boolean');

		$pass = [ 'username', 'email', 'phone' ];

		if (is_null ($username)) {
			$error .= 'A4';		/*E[Invalid username.]*/
			}
		else
		if (username_exists ($username)) {
			$error .= 'A8';		/*E[Username is already in use.]*/
			}
		if (is_null ($email)) {
			$error .= 'D9';		/*E[Invalid e-mail address.]*/
			}
		else
		if (email_exists ($email)) {
			$error .= 'D10';	/*E[E-Mail address is already in use.]*/
			}
		if (strlen ($password) < 8) {
			$error .= 'B7';		/*E[Passwords should have at least 8 characters.]*/
			}
		if ($password != $confirmp) {
			$error .= 'C6';		/*E[Confirmed password does not match.]*/
			}
		if (is_null ($phone)) {
			$error .= 'E11';	/*E[Invalid phone number.]*/
			}
		if (!$terms) {
			$error .= 'F12';	/*E[You must accept the Terms &amp; Conditions.]*/
			}

		if (!is_null ($error)) self::prg ($error, $pass);

		$user_id = wp_create_user ($username, $password, $email);
		if (is_wp_error ($user_id)) {
			$error .= 'G1';
			self::prg ($error, $pass);
			}
		try {
			$cs_user = new \CoreSite\Core\User ($user_id);
			$cs_user->set ('phone', $phone);
			}
		catch (\CoreSite\Core\Fault $e) {
			$error .= 'G1';
			self::prg ($error, $pass);
			}

		self::prg ();
		}
	}
?>
