<?php
/**
 * Core of CoreSite
 */

/**
 * Exception Class
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

class Fault extends \Exception {
	const Unknown_Error		= 0;
	const Login_Error		= 1;
	const Unknown_Email		= 2;
	const SendMail_Fail		= 3;
	const Username_Exists		= 4;
	const Email_Exists		= 5;
	const Password_Mismatch		= 6;
	const Username_Invalid		= 7;
	const Email_Invalid		= 8;
	const Password_Invalid		= 9;
	const Invalid_ID		= 10;

	const Invalid_First_Name	= 11;
	const Invalid_Last_Name		= 12;
	const Invalid_Phone		= 13;
	

	private static $H = [
		self::Unknown_Error		=> /*T[*/'An unknown error has occured. Check with the developers of this application.'/*]*/,
		self::Login_Error		=> /*T[*/'Numele de utilizator sau parola nu sunt corecte. Te rog sa incerci din nou.'/*]*/,
		self::Unknown_Email		=> /*T[*/'Adresa de email pe care ai introdus-o nu se gaseste in baza noastra de date.'/*]*/,
		self::SendMail_Fail		=> /*T[*/'Nu am putut trimite email. Te rugam sa verifici adresa de email si sa incerci din nou.'/*]*/,
		self::Username_Exists		=> /*T[*/'Numele de utilizator este deja folosit de un alt utilizator.'/*]*/,
		self::Email_Exists		=> /*T[*/'Adresa de email este deja folosita de un alt utilizator.'/*]*/,
		self::Password_Mismatch		=> /*T[*/'Parola nu a fost confirmata corespunzator. Te rugam sa incerci din nou.'/*]*/,
		self::Username_Invalid		=> /*T[*/'Numele de utilizator nu este valid. Te rugam sa incerci din nou.'/*]*/,
		self::Email_Invalid		=> /*T[*/'Adresa de email nu este valida. Te rugam sa incerci din nou.'/*]*/,
		self::Password_Invalid		=> /*T[*/'Parola nu este suficient de sigura. Aceasta trebuie sa contina minimum 8 caractere, cel putin o litera si cel putin o cifra. Te rugam sa incerci din nou.'/*]*/,
		];

	public function __construct ($code = 0, $message = null) {
		$code = (int) $code;
		if (is_null ($message) && isset (self::$H[$code]))
			$message = self::$H[$code];
		$message = (string) $message;

		parent::__construct ($message, $code);
		}

	public function get ($key = null) {
		if ($key == 'code') return parent::getCode();
		return parent::getMessage ();
		}

	public function json () {
		echo json_encode ((object) array (
			'error'	=> parent::getCode()
			));
		}

	public static function msg ($code = 0) {
		if (is_numeric ($code) && isset (self::$H[$code])) return self::$H[$code];
		if (is_array ($code)) {
			$msgs = [];
			foreach ($code as $id)
				if (isset (self::$H[$id]))
					$msgs[] = self::$H[$id];
			return $msgs;
			}
		return '';
		}

	public static function err (&$error, $code = 0) {
		if (!is_array ($error)) $error = [];
		$error[] = $code;
		}
	};
?>
