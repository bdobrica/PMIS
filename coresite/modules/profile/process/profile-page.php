<?php
namespace CoreSite\Core;

if (defined ('CS_AJAX')) {
	if (!empty ($_FILES)) {
		foreach ($_FILES as $key => $value) {
			try {
				$file = new File ($key);
				$file->save ();
				$_POST[$key] = $file->get ('Theme');
				}
			catch (Fault $e) {
				}
			}
		}

	switch ($object) {
		case 'user':
			switch ($action) {
				case 'update':
					try {
						$cs_user->set ($_POST);

						foreach ($_POST as $key => $value) {
							$return[$key] = $cs_user->get ($key);
							if ($return[$key] instanceof File)
								$return[$key] = $return[$key]->get ('url', '76x76');
							}

						if (isset ($return['first_name']) && isset ($return['last_name']))
							$return['full_name'] = $cs_user->get ('full_name');
						}
					catch (Fault $e) {
						$return['error'] = 1;
						}
					break;
				default:
					$return['error'] = 1;
					break;
				}
			break;
		case 'resume':
			switch ($action) {
				case 'create':
					$data = [
						'period'	=> Theme::r ('period', 'interval'),
						'type'		=> Theme::r ('type'),
						'category'	=> Theme::r ('category'),
						'entity'	=> Theme::r ('entity'),
						'name'		=> Theme::r ('name'),
						'description'	=> Theme::r ('description'),
						'level'		=> Theme::r ('level')
						];

					var_dump ($data);
/*
					try {
						$resume = new \CoreSite\Module\Profile\Resume ($data);
						$resume->save ();
						}
					catch (Fault $e) {
						$return['error'] = 1;
						}
*/
					break;
				default:
					$return['error'] = 1;
					break;
				}
			break;
		default:
			$return['error'] = 1;
			break;
		}
	}
?>
