<?php

	spl_autoload_register(function($class) {

		switch (true) {

			// Abstract classes
			case (preg_match('#^abs_#', $class)):

				require 'app://includes/abstracts/' . $class . '.inc.php';
				break;

			// Clients and wrappers
			case (preg_match('#_client$#', $class)):

				require 'app://includes/clients/' . $class . '.inc.php';
				break;

			// DataTypes
			case (preg_match('#^type_#', $class)):

				require 'app://includes/datatypes/' . $class . '.inc.php';
				break;

			// Entities
			case (preg_match('#^ent_#', $class)):

				require 'app://includes/entities/' . $class . '.inc.php';
				break;

			// Modules
			case (preg_match('#^mod_#', $class)):

				require 'app://includes/modules/' . $class . '.inc.php';
				break;

			// Submodules
			case (preg_match('#^chk_#', $class)):
			case (preg_match('#^cm_#', $class)):
			case (preg_match('#^job_#', $class)):
			case (preg_match('#^om_#', $class)):
			case (preg_match('#^ot_#', $class)):
			case (preg_match('#^pm_#', $class)):
			case (preg_match('#^sm_#', $class)):
			case (preg_match('#^tm_#', $class)):

				$file = match(strtok($class, '_')) {
					'chk' => FS_DIR_APP . 'includes/modules/checkout/' . $class . '.inc.php',
					'cm' => FS_DIR_APP . 'includes/modules/customer/' . $class . '.inc.php',
					'job' => FS_DIR_APP . 'includes/modules/jobs/' . $class . '.inc.php',
					'om' => FS_DIR_APP . 'includes/modules/order/' . $class . '.inc.php',
					'ot' => FS_DIR_APP . 'includes/modules/order_total/' . $class . '.inc.php',
					'pm' => FS_DIR_APP . 'includes/modules/payment/' . $class . '.inc.php',
					'sm' => FS_DIR_APP . 'includes/modules/shipping/' . $class . '.inc.php',
					'tm' => FS_DIR_APP . 'includes/modules/translation/' . $class . '.inc.php',
				};

				// Patch modules for PHP 8.2 Compatibility
				if (version_compare(PHP_VERSION, 8.2, '>=') && is_file($file)) {
					$source = file_get_contents($file);
					if (!preg_match('#\#\[AllowDynamicProperties\]#', $source)) {
						$source = preg_replace('#([ \t]*)class [a-zA-Z0-9_-]+\s*?\{(\n|\r\n?)#', '$1#[AllowDynamicProperties]$2$0', $source);
						file_put_contents($file, $source);
					}
				}

				require $file;
				break;

			// References
			case (preg_match('#^ref_#', $class)):

				require 'app://includes/references/' . $class . '.inc.php';
				break;

			// Routing modules
			case (preg_match('#^url_#', $class)):

				if (is_file($file = 'app://backend/routes/' . $class . '.inc.php')) require $file;
				if (is_file($file = 'app://frontend/routes/' . $class . '.inc.php')) require $file;
				break;

			// Stream wrappers
			case (preg_match('#^stream_#', $class)):

				require 'app://includes/streams/' . $class . '.inc.php';
				break;

			// System nodes
			default:

				if (is_file($file = 'app://includes/nodes/nod_' . $class . '.inc.php')) {
					require $file;

					if (method_exists($class, 'init')) {
						call_user_func([$class, 'init']); // As static classes do not have a __construct() (PHP #62860)
					}
				}

				break;
		}
	}, true, false);
