<?php

	// Maintenance Mode
	if (settings::get('maintenance_mode')) {
		
		if (!in_array(route::$selected['resource'], [
			'f:invoice/edit',
		])) {
			
			if (administrator::check_login()) {

				notices::add('notices', strtr('%message [<a href="%link">%preview</a>]', [
					'%message' => language::translate('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'),
					'%preview' => language::translate('title_preview', 'Preview'),
					'%link' => document::href_ilink('maintenance_mode'),
				]), 'maintenance_mode');
				
			} else {
				http_response_code(503);
				include 'app://frontend/pages/maintenance_mode.inc.php';
				require_once 'app://includes/app_footer.inc.php';
				exit;
			}
		}
	}

	document::$head_tags['manifest'] = '<link rel="manifest" href="'. document::href_ilink('webmanifest.json') .'">';
