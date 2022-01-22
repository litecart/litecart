<?php
/*!
 * LiteCart® 2.4.0
 *
 * Online Catalog and Shopping Cart Platform
 *
 * LiteCart is provided free without warranty. Use it at your own risk.
 *
 * @author    LiteCart Dev Team <development@litecart.net>
 * @license   http://creativecommons.org/licenses/by-nd/4.0/ CC BY-ND 4.0
 * @link      https://www.litecart.net Official Website
 *
 * LiteCart is a registered trademark, property of T. Almroth.
 */

  require_once('includes/app_header.inc.php');

  if (settings::get('maintenance_mode')) {
    if (!empty(user::$data['id'])) {
      notices::add('notices', strtr('%message [<a href="%link">%preview</a>]', [
        '%message' => language::translate('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'),
        '%preview' => language::translate('title_preview', 'Preview'),
        '%link' => document::href_ilink('maintenance_mode'),
      ]), 'maintenance_mode');
    } else {
      http_response_code(503);
      include vmod::check(FS_DIR_APP . 'pages/maintenance_mode.inc.php');
      require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
      exit;
    }
  }

// Load routes
  route::load(FS_DIR_APP . 'includes/routes/url_*.inc.php');

// Append default route
  route::add('#^([0-9a-zA-Z_\-/\.]+?)(?:\.php)?/?$#', '$1');

// Go
  route::process();

  require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
