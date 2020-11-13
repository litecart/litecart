<?php
/*!
 * LiteCart® 2.3
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

  route::load(FS_DIR_APP . 'frontend/routes/url_*.inc.php');
  route::load(FS_DIR_APP . 'backend/routes/url_*.inc.php');

// Append last destination route
  route::add('#^([0-9a-zA-Z_/\.]+)$#', 'frontend', '$1');

  route::identify();

// Set template
  if (!empty(route::$route['endpoint']) && route::$route['endpoint'] == 'backend') {
    require vmod::check(FS_DIR_APP . 'backend/bootstrap.inc.php');
  } else {
    require vmod::check(FS_DIR_APP . 'frontend/bootstrap.inc.php');
  }

// Run operations before capture
  event::fire('before_capture');

// Go capture the content
  route::process();

  require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
