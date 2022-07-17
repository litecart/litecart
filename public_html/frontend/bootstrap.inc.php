<?php

// Define template paths
  define('FS_DIR_TEMPLATE', 'app://frontend/templates/'. settings::get('template') .'/');
  define('WS_DIR_TEMPLATE', WS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/');

// Development Mode
  if (settings::get('development_mode')) {
    user::require_login();
  }

// Maintenance Mode
  if (settings::get('maintenance_mode')) {
    if (!empty(user::$data['id'])) {
      notices::add('notices', strtr('%message [<a href="%link">%preview</a>]', [
        '%message' => language::translate('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'),
        '%preview' => language::translate('title_preview', 'Preview'),
        '%link' => document::href_ilink('maintenance_mode'),
      ]));
    } else {
      http_response_code(503);
      include 'app://frontend/pages/maintenance_mode.inc.php';
      require_once 'app://includes/app_footer.inc.php';
      exit;
    }
  }
