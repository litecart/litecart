<?php

	spl_autoload_register(function($class) {

		switch (true) {

			// Abstract classes
			case (preg_match('#^abs_#', $class)):

				require 'app://shared/abstracts/' . $class . '.inc.php';
				break;

			// Clients and wrappers
			case (preg_match('#_client$#', $class)):

				require 'app://shared/clients/' . $class . '.inc.php';
				break;

			// Entities
			case (preg_match('#^ent_#', $class)):

				require 'app://shared/entities/' . $class . '.inc.php';
				break;

			// Modules
			case (preg_match('#^mod_#', $class)):

				require 'app://shared/modules/' . $class . '.inc.php';
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
					'chk' => 'app://shared/modules/checkout/' . $class . '.inc.php',
					'cm' => 'app://shared/modules/customer/' . $class . '.inc.php',
					'job' => 'app://shared/modules/jobs/' . $class . '.inc.php',
					'om' => 'app://shared/modules/order/' . $class . '.inc.php',
					'ot' => 'app://shared/modules/order_total/' . $class . '.inc.php',
					'pm' => 'app://shared/modules/payment/' . $class . '.inc.php',
					'sm' => 'app://shared/modules/shipping/' . $class . '.inc.php',
					'tm' => 'app://shared/modules/translation/' . $class . '.inc.php',
				};

				require $file;
				break;

			// References
			case (preg_match('#^ref_#', $class)):

				require 'app://shared/references/' . $class . '.inc.php';
				break;

			// Routing modules
			case (preg_match('#^url_#', $class)):

				if (is_file($file = 'app://backend/routes/' . $class . '.inc.php')) require $file;
				if (is_file($file = 'app://frontend/routes/' . $class . '.inc.php')) require $file;
				break;

			// Stream wrappers
			case (preg_match('#^stream_#', $class)):

				require 'app://shared/streams/' . $class . '.inc.php';
				break;

			// Types
			case (preg_match('#^type_#', $class)):

				require 'app://shared/types/' . $class . '.inc.php';
				break;

			// System nodes
			default:

				if (is_file($file = 'app://shared/nodes/nod_' . $class . '.inc.php')) {
					require $file;

					if (method_exists($class, 'init')) {
						call_user_func([$class, 'init']); // As static classes do not have a __construct() (PHP #62860)
					}
				}

				break;
		}
	}, true, false);
