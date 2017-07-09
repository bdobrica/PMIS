<?php
/**
 * Core of CoreSite
 */

/**
 * File Class
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

class File extends Model {
	const PATH	= 'files';
	const FILTER	= \Imagick::FILTER_CATROM; 	/* FILTER_CATROM: average speed, good quality
							 * FILTER_HERMITE: 50% slower than scaleImage, better quality
							 * scaleImage: fastest, poor quality
							 * @reference: http://urmaul.com/blog/imagick-filters-comparison/
							 */
	const DIR_MIME	= 'dir';

	public static $version = '1.0.0';
	public static $human = 'File';

	public static $T = 'files';

	public static $mime = [
		'doc'		=> 'application/msword',
		'docx'		=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'ppt'		=> 'application/vnd.ms-powerpointtd',
		'pptx'		=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'xls'		=> 'application/vnd.ms-excel',
		'xlsx'		=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

		'odt'		=> 'application/vnd.oasis.opendocument.text',
		'ods'		=> 'application/vnd.oasis.opendocument.spreadsheet',
		'odp'		=> 'application/vnd.oasis.opendocument.presentation',

		'pdf'		=> 'application/pdf',

		'jpg'		=> 'image/jpeg',
		'png'		=> 'image/png',
		];

	public static $sharing = [
		'private'	=> 'Private',
		'sharing'	=> 'Sharing',
		'public'	=> 'Public'
		];

	protected static $K = [
		'owner_id',
		'parent_id',
		'name',
		'type',
		'hash',
		'path',
		'sharing',
		'stamp'
		];

	public static $F = [
		'create'	=> [
			],
		'read'		=> [
			],
		'update'	=> [
			],
		'delete'	=> [
			],
		'list'		=> [
			]
		];

	protected static $Q = [
		'`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT',
		'`owner_id` int(11) NOT NULL DEFAULT 0',
		'`parent_id` int(11) NOT NULL DEFAULT 0',
		'`name` varchar(256) NOT NULL DEFAULT \'\'',
		'`type` varchar(4) NOT NULL DEFAULT \'\'',
		'`hash` varchar(40) NOT NULL DEFAULT \'\'',
		'`path` text NOT NULL',
		'`sharing` enum(\'hidden\',\'private\',\'sharing\',\'public\') NOT NULL DEFAULT \'hidden\'',
		'`stamp` int(11) NOT NULL DEFAULT 0',
		'UNIQUE (`hash`, `type`)',
		'INDEX (`owner_id`)',
		'INDEX (`parent_id`)',
		'INDEX (`type`)',
		'INDEX (`sharing`)'
		];

	public function __construct ($data = null) {
		if (is_string ($data)) {
			if (isset ($_FILES[$data])) {
				$current_user = wp_get_current_user ();
				if (!$current_user->ID) throw new Fault ();

				$file = $_FILES[$data];
				$data = [];

				if ($file['error'] != UPLOAD_ERR_OK) throw new Fault ();
			
				$file_info = finfo_open (FILEINFO_MIME_TYPE);	
				$file_mime = finfo_file ($file_info, $file['tmp_name']);
				finfo_close ($file_info);

				if (FALSE === $type = array_search ($file_mime, self::$mime, true)) throw new Fault ();
				
				$data = [
					'uid'	=> $current_user->ID,
					'name'	=> basename ($file['name']),
					'hash'	=> sha1_file ($file['tmp_name']),
					'type'	=> $type,
					'path'	=> $file['tmp_name'],
					'stamp'	=> time ()
					];
				}
			}
		else
		if (is_array ($data)) {
			if (isset ($data['path']) && file_exists ($data['path'])) {
				if (!isset ($data['type'])) {
					$file_info = finfo_open (FILEINFO_MIME_TYPE);	
					$file_mime = finfo_file ($file_info, $data['path']);
					finfo_close ($file_info);

					if (FALSE === $type = array_search ($file_mime, self::$mime, true)) throw new Fault ();

					$data['type'] = $type;
					}
				if (!isset ($data['hash'])) {
					$data['hash'] = sha1_file ($data['path']);
					}
				}
			if (!isset ($data['stamp'])) {
				$data['stamp'] = time ();
				}
			}

		parent::__construct ($data);
		}

	public function get ($key = null, $opts = null) {
		if (is_string ($key)) {
			switch ($key) {
				case 'url':
					return $this->_url ($opts);
					break;
				case 'path':
					if (is_null ($opts))
						return $this->data['path'];
					return $this->_resize ($opts);
					break;
				}
			}
		return parent::get ($key, $opts);
		}

	public function save () {
		if (is_uploaded_file ($this->data['path'])) {
			$path = $this->_path ();

			if (!is_dir (dirname ($path)) && !@mkdir (dirname ($path), 0755, TRUE)) throw new Fault ();
			if (!file_exists ($path)) {
				if (!@move_uploaded_file ($this->data['path'], $path)) throw new Fault ();
				$this->data['path'] = $path;
				}
			else
				$this->data['path'] = $path;
			}

		parent::save ();
		}

	public function __toString () {
		return $this->_url ();
		}

	private function _resize ($size = null) {
		if (is_array ($size))
			list ($rx, $ry) = $size;
		else
		if (is_string ($size) && (strpos ($size, 'x') !== FALSE))
			list ($rx, $ry) = explode ('x', $size);
		else
		if (is_numeric ($size))
			$rx = $ry = $size;

		$rx = (int) $rx;
		$ry = (int) $ry;

		if ($rx < 1 || $ry < 1)
			return null;

		$path = $this->data['path'];
		if (!file_exists ($path))
			return null;

		$resized_path = sprintf ('%s-%dx%d.%s', substr ($this->data['path'], 0, -4), $rx, $ry, $this->data['type']);
		if (file_exists ($resized_path))
			return $resized_path;

		$image = new \Imagick (realpath ($path));

		$x = $image->getImageWidth ();
		$y = $image->getImageHeight ();

		if ($x < 1 || $y < 1)
			return null;

		if (($x < $rx) && ($y < $ry))
			return null;
		
		$fx = $rx / $x;
		$fy = $ry / $y;
	
		if ($fx == $fy)
			$image->resizeImage ($rx, $ry, self::FILTER, 1.0);
		if ($fx < $fy) {
			$cx = (int) ($fy * $x);
			$cy = (int) ($fy * $y);
			$image->resizeImage ($cx, $cy, self::FILTER, 1.0);
			$ox = (int) (($cx - $rx) / 2);
			$image->cropImage ($rx, $ry, $ox, 0);
			}
		if ($fx > $fy) {
			$cx = (int) ($fx * $x);
			$cy = (int) ($fx * $y);
			$image->resizeImage ($cx, $cy, self::FILTER, 1.0);
			$oy = (int) (($cy - $ry) / 2);
			$image->cropImage ($rx, $ry, 0, $oy);
			}

		$image->writeImage ($resized_path);

		return $resized_path;
		}

	private function _path () {
		return implode (DIRECTORY_SEPARATOR, [
			rtrim (WP_CONTENT_DIR, '/'),
			\CoreSite\Core\Theme::CONTENT_DIR,
			self::PATH,
			$this->data['hash'] . '.' . $this->data['type']
			]);
		}

	private function _url ($opts = null) {
		return str_replace (WP_CONTENT_DIR, WP_CONTENT_URL, $this->get ('path', $opts));
		}
	}
?>
