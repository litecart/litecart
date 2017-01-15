<?php
/**
 * LiteCart�
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

  if (!empty(route::$route) && is_file(FS_DIR_HTTP_ROOT . WS_DIR_PAGES . route::$route['page'] .'.inc.php')) {

    include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_PAGES . route::$route['page'] .'.inc.php');

  } else {

    http_response_code(404);

    if (preg_match('#\.(css|js|gif|jpg|png|svg)$#', route::$request)) exit;

    echo '<h1>HTTP 404 - File Not Found</h1>';
    echo '<p>Could not find a matching reference for '. parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) .'.</p>';
  }

  require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>