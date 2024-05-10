<?php

// Development Mode
  if (settings::get('development_mode')) {
    if (empty(administrator::$data['id']) && (!isset(route::$selected['endpoint']) || route::$selected['endpoint'] != 'backend')) {
      if (!in_array(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), [WS_DIR_APP . 'manifest.json'])) {
        http_response_code(403);
        include 'app://pages/development_mode.inc.php';
        include 'app://includes/app_footer.inc.php';
        exit;
      }
    }
  }

// Maintenance Mode
  if (settings::get('maintenance_mode')) {
    if (!empty(administrator::$data['id'])) {
      notices::add('notices', strtr('%message [<a href="%link">%preview</a>]', [
        '%message' => language::translate('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'),
        '%preview' => language::translate('title_preview', 'Preview'),
        '%link' => document::href_ilink('maintenance_mode'),
      ]));
    } else {
      if (!in_array(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), [WS_DIR_APP . 'manifest.json'])) {
        http_response_code(503);
        include 'app://frontend/pages/maintenance_mode.inc.php';
        include 'app://includes/app_footer.inc.php';
        exit;
      }
    }
  }

  document::$head_tags['manifest'] = '<link rel="manifest" href="'. document::href_ilink('webmanifest.json') .'">';
