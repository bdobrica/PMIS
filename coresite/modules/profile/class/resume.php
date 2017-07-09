<?php
/**
 * Profile Module, part of CoreSite
 */

/**
 * Resume Class
 *
 * @category
 * @package CoreSite
 * @subpackage Profile
 * @copyright Core Security Advisers SRL
 * @author Bogdan Dobrica <bdobrica @ gmail.com>
 * @version 0.1
 *
 */
namespace CoreSite\Module\Profile;

class Resume extends \CoreSite\Core\Model {
	public static $version = '1.0.0';
	public static $human = /*T[*/'Resume'/*]*/;
	public static $T = 'resumes';

	public static $type = [
		'work_experience'	=> [
			],
		'professional_skill'	=> [
			],
		'education'		=> [
			'highschool' => /*T[*/'High School'/*]*/,
			'bachelor' => /*T[*/'Bachelor'/*]*/,
			'masters' => /*T[*/'Masters'/*]*/,
			'phd' => /*T[*/'Ph.D.'/*]*/,
			],
		];

	public static $level = [
		1 => /*T[*/'Potential / Theoretical knowledge &amp; academic projects'/*]*/,
		2 => /*T[*/'Junior / Assistant in real production environment'/*]*/,
		3 => /*T[*/'Confirmed / Relevant experience and skills for medium to complex tasks'/*]*/,
		4 => /*T[*/'Advanced / Consistent experience and ability for high difficulty tasks'/*]*/,
		5 => /*T[*/'Senior - Expert / Proven capacity to be the highest authority in the team'/*]*/,
		];

	protected static $K = [
		'owner_id',
		'period',
		'type',
		'category',
		'entity',
		'name',
		'description',
		'level',
		];

	protected static $Q = [
		'`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT',
		'`owner_id` int(11) NOT NULL DEFAULT 0',
		'`period` varchar(15) NOT NULL DEFAULT \'\'',
		'`type` enum(\'work_experience\',\'professional_skill\',\'education\') NOT NULL DEFAULT \'work_experience\'',
		'`category` varchar(32) NOT NULL DEFAULT \'\'',
		'`entity` text NOT NULL',
		'`name` text NOT NULL',
		'`description` text NOT NULL',
		'`level` int(2) NOT NULL DEFAULT 0',
		'`stamp` int(11) NOT NULL DEFAULT 0',
		'INDEX (`owner_id`)',
		'INDEX (`type`)',
		'INDEX (`category`)',
		'INDEX (`level`)',
		];
	}
?>
