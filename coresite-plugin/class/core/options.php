<?php
/**
 * Core of CoreSite
 */

/**
 * Options class.
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

class Options {
	const PREFIX = 'cs_';
	const OPTIONS = 'assets/opts';
	const BUFFER = 128;
	const AUTOLOAD = TRUE;

	private $options;
	private $pages;
	private $categories;
	private $mail_templates;

	public function get ($what = null, $opts = null) {
		switch ((string) $what) {
			case 'options':
				$out = [];

				$template_directory = get_template_directory ();

				$path = $template_directory . DIRECTORY_SEPARATOR . self::OPTIONS . DIRECTORY_SEPARATOR;

				if (!is_dir ($path)) return null;
				if (($dh = opendir ($path)) === FALSE) return null;
				while (($file = readdir ($dh)) !== FALSE) {
					if ($file == '.' || $file == '..') continue;
					if (strtolower (substr ($file, -3)) != 'php') continue;
					$header = $this->get ('header', $path . DIRECTORY_SEPARATOR . $file);
					if (is_null ($header)) continue;
					$out[] = [
						'path' => $template_directory . DIRECTORY_SEPARATOR . self::OPTIONS . DIRECTORY_SEPARATOR . $file,
						'slug' => self::PREFIX . strtolower (substr ($file, 0, -4)),
						'name' => $header['name'] ? : $file
						];
					}
				closedir ($dh);

				return $out;
				break;
			case 'header':
				$out = [];

				$state = 0;
				
				if (!file_exists ($opts)) return null;
				if (($fh = fopen ($opts, 'r')) === FALSE) return null;
				
				while ((($line = fgets ($fh, self::BUFFER)) !== FALSE) && ($state < 2)) {
					$line = trim ($line);
					if (strpos ($line, '/*') === 0) { $state = 1; continue; }
					if (strpos ($line, '*/') === 0) break;
					if ($state < 1) continue;
					list ($key, $value) = explode (':', $line);
					$out[str_replace (' ', '_', trim(strtolower($key)))] = trim($value);
					}

				fclose ($fh);
				return $out;
				break;
			case 'value':
				if (!is_array ($opts) && is_string ($opts) && (strpos ($opts, '.') !== FALSE)) {
					list ($page, $option) = explode ('.', $opts);
					$opts = [ 'page' => $page, 'option' => $option, 'echo' => TRUE ];
					}

				if (!isset ($this->options[$opts['page']])) $this->options[$opts['page']] = get_option (self::PREFIX . $opts['page'], []);
				if (empty ($this->options[$opts['page']]) || !isset($this->options[$opts['page']][$opts['option']])) return null;
				if (!$opts['echo']) return $this->options[$opts['page']][$opts['option']];
				echo $this->options[$opts['page']][$opts['option']];
				break;
			}
		}

	private function render ($field = null, $opts = null, $echo = false) {
		$out = '';

		switch ((string) $opts['type']) {
			case 'page':
				if (empty ($this->pages)) $this->pages = get_pages ();
				$out .= vsprintf ('<tr>
	<th scope="row"><label for="%s">%s</label></th>
	<td>
		<select id="%s" name="%s">
			<option value="0">%s</option>', [
						$opts['id'],
						$opts['label'],
						$opts['id'],
						$field,
						$opts['noopts']
						]);
				if (!empty ($this->pages))
				foreach ($this->pages as $page) {
					$out .= vsprintf ('
			<option value="%d"%s>%s</option>', [
						$page->ID,
						$page->ID == $opts['default'] ? ' selected' : '',
						$page->post_title
						]);
					}
				$out .= sprintf ('
		</select>
		%s
	</td>
</tr>',
						isset ($opts['description']) ? sprintf ('<p class="description">%s</p>', $opts['description']): ''
					);
				break;
			case 'mail_template':
				if (empty ($this->mail_templates)) $this->mail_templates = get_posts([
					'posts_per_page'	=> -1,
					'post_type'		=> 'mail_templates'
					]);
				$out .= vsprintf ('<tr>
	<th scope="row"><label for="%s">%s</label></th>
	<td>
		<select id="%s" name="%s">
			<option value="0">%s</option>', [
						$opts['id'],
						$opts['label'],
						$opts['id'],
						$field,
						$opts['noopts']
						]);
				if (!empty ($this->mail_templates))
				foreach ($this->mail_templates as $mail_template) {
					$out .= vsprintf ('
			<option value="%d"%s>%s</option>', [
						$mail_template->ID,
						$mail_template->ID == $opts['default'] ? ' selected' : '',
						$mail_template->post_title
						]);
					}
				$out .= sprintf ('
		</select>
		%s
	</td>
</tr>',
						isset ($opts['description']) ? sprintf ('<p class="description">%s</p>', $opts['description']): ''
					);
				break;
			case 'category':
				if (empty ($this->categories)) $this->categories = get_categories ();
				$out .= vsprintf ('<tr>
	<th scope="row"><label for="%s">%s</label></th>
	<td>
		<select id="%s" name="%s">
			<option value="0">%s</option>', [
						$opts['id'],
						$opts['label'],
						$opts['id'],
						$field,
						$opts['noopts']
						]);
				if (!empty ($this->categories))
				foreach ($this->categories as $category) {
					$out .= vsprintf ('
			<option value="%d"%s>%s</option>', [
						$category->term_id,
						$category->term_id == $opts['default'] ? ' selected' : '',
						$category->cat_name
						]);
					}
				$out .= sprintf ('
		</select>
		%s
	</td>
</tr>',
						isset ($opts['description']) ? sprintf ('<p class="description">%s</p>', $opts['description']): ''
					);
				break;
			case 'image':
				$out .= vsprintf ('<tr>
	<th scope="row"><label for="%s">%s</label></th>
	<td><input id="%s" type="text" maxlength="45" size="20" name="%s" value="%s" /> 
	<input id="%s_button" class="button custom-upload-button" type="button" value="Upload Image" /></td>
</tr>', [
						$opts['id'],
						$opts['label'],
						$opts['id'],
						$field,
						$opts['default'],
						$opts['id']
						]);
				break;
			case 'select':
				if (isset ($opts['noopts']))
					$out .= vsprintf ('<tr>
	<th scope="row"><label for="%s">%s</label></th>
	<td>
		<select name="%s">
			<option value="0">%s</option>', [
							$opts['id'],
							$opts['label'],
							$field,
							$opts['noopts']
							]);
				else
					$out .= vsprintf ('<tr>
	<th scope="row"><label for="%s">%s</label></th>
	<td>
		<select name="%s">', [
							$opts['id'],
							$opts['label'],
							$field
							]);
				if (!empty ($opts['options']))
				foreach ($opts['options'] as $opt_slug => $opt_name)
					$out .= vsprintf ('
			<option value="%s"%s>%s</option>', [
						$opt_slug,
						$opt_slug == $opts['default'] ? ' selected' : '',
						$opt_name
						]);
				$out .= sprintf ('
		</select>
		%s
	</td>
</tr>',
						isset ($opts['description']) ? sprintf ('<p class="description">%s</p>', $opts['description']): ''
					);
				break;
			default:
				$out .= vsprintf ('<tr>
	<th scope="row"><label for="%s">%s</label></th>
	<td>
		<input id="%s" type="text" maxlength="45" size="20" name="%s" value="%s" />
		%s
	</td>
</tr>', [
						$opts['id'],
						$opts['label'],
						$opts['id'],
						$field,
						$opts['default'],
						isset ($opts['description']) ? sprintf ('<p class="description">%s</p>', $opts['description']): ''
						]);
				break;
			}

		if (!$echo) return $out;
		echo $out;
		}

	public function page () {
		if (!isset ($_GET['page']) || (strpos ($_GET['page'], self::PREFIX) !== 0) || !preg_match ('/^[A-z_]+$/', $_GET['page'])) return;

		$page = substr ($_GET['page'], strlen (self::PREFIX));
		$file = get_template_directory () . DIRECTORY_SEPARATOR . self::OPTIONS . DIRECTORY_SEPARATOR . $page . '.php';
		if (!file_exists($file)) return;
		$header = $this->get ('header', $file);

		include ($file);

		$saved_options = get_option (self::PREFIX . $page, []);
		$changed_options = 0;
		$count_id = 1;

		if (!empty ($options))
		foreach ($options as $option_id => $option_data) {
			if (is_array ($option_data) && isset ($option_data['options']) && !empty ($option_data['options']))
				foreach ($option_data['options'] as $field_name => $field_options) {
					if (!isset ($saved_options[$field_name])) $saved_options[$field_name] = $field_options['default'];

					$value = isset ($_POST[$field_name]) ? $_POST[$field_name] : (isset ($saved_options[$field_name]) ? $saved_options[$field_name] : $field_options['default']);
					if ($value != $saved_options[$field_name]) {
						$saved_options[$field_name] = $value;
						$changed_options ++;
						}

					switch ((string) $field_options['type']) {
						case 'page':
						case 'category':
							$options[$option_id]['options'][$field_name]['default'] = (int) $value;
							break;
						case 'image':
						default:
							$options[$option_id]['options'][$field_name]['default'] = $value;
							break;
						}
					$options[$option_id]['options'][$field_name]['id'] = self::PREFIX . 'field_' . ($count_id++);
					}
			else
			if (is_string ($option_id) && is_array ($option_data)) {
				if (!isset ($saved_options[$option_id])) $saved_options[$option_id] = $option_data['default'];

				$value = isset ($_POST[$option_id]) ? $_POST[$option_id] : (isset ($saved_options[$option_id]) ? $saved_options[$option_id] : $option_data['default']);
				if ($value != $saved_options[$option_id]) {
					$saved_options[$option_id] = $value;
					$changed_options ++;
					}

				switch ((string) $option_data['type']) {
					case 'page':
					case 'category':
						$options[$option_id]['default'] = (int) $value;
						break;
					case 'image':
					default:
						$options[$option_id]['default'] = $value;
						break;
					}
				$options[$option_id]['options'][$option_id]['id'] = self::PREFIX . 'field_' . ($count_id++);
				}
			}
		if ($changed_options)
			update_option (self::PREFIX . $page, $saved_options, self::AUTOLOAD);


?><div class="wrap"><div class="icon32" id="<?php echo isset ($header['icon']) ? $header['icon'] : 'icon-tools'; ?>"></div><h2><?php echo isset ($header['name']) ? $header['name'] : ''; ?></h2><p><?php echo isset ($header['description']) ? $header['description'] : ''; ?></p><?php if ($changed_options) : ?><div class="updated"><?php Theme::_e (/*T[*/'Settings Saved'/*]*/); ?></div><?php endif; ?><form action="" method="post"><?php
		if (!empty ($options)) :
			foreach ($options as $option_id => $option_data) :
				if (is_array ($option_data) && isset ($option_data['options']) && is_array ($option_data['options']) && !empty ($option_data['options'])) :
?><div class="form-section"><h3><span><?php echo isset ($option_data['section']) ? $option_data['section'] : ''; ?></span></h3><table class="form-table"><tbody><?php
					foreach ($option_data['options'] as $field_name => $field_options) :
						$this->render ($field_name, $field_options, true);
					endforeach;
?></tbody></table></div><?php
				else :
					if (is_string ($option_id) && is_array ($option_data)) :
						$this->render ($field_name, $field_options, true);
					endif;
				endif;
			endforeach;
		endif;
?><p class="submit"><input type="submit" class="button-primary" value="<?php echo esc_attr__ (Theme::__ (/*T[*/'Save Changes'/*]*/)); ?>" /></p></form></div><?php
		}

	public function register ($parent_slug, $capability) {
		$options = $this->get ('options');
		if (sizeof ($options) > 1)
#		if (!empty ($options))
			foreach ($options as $option)
				add_submenu_page (self::PREFIX . $parent_slug, $option['name'], $option['name'], $capability, $option['slug'], [$this, 'page']);
		}

	public function __construct () {
		$this->options = [];
		}
	public function __destruct () {
		}
	}
?>
