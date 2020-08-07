<?php

// Template
  document::$settings = settings::get('store_template');

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
      include vmod::check(FS_DIR_APP . 'frontend/pages/maintenance_mode.inc.php');
      require_once vmod::check(FS_DIR_APP . 'system/app_footer.inc.php');
      exit;
    }
  }

// Load routes
  route::load(FS_DIR_APP . 'frontend/routes/url_*.inc.php');

// Append default route
  route::add('#^([0-9a-zA-Z_/\.]+)$#', 'frontend', '$1');

  route::identify();

// Run operations before capture
  event::fire('before_capture');

// Go
  route::process();
