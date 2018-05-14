<?php
/*!
 * LiteCart® 2.1.2
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
      notices::add('notices', strtr('%message [<a href="%link">%preview</a>]', array(
        '%message' => language::translate('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'),
        '%preview' => language::translate('title_preview', 'Preview'),
        '%link' => document::href_ilink('maintenance_mode'),
      )));
    } else {
      http_response_code(503);
      include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'pages/maintenance_mode.inc.php');
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
      exit;
    }
  }

  if (!empty(route::$route) && is_file(FS_DIR_HTTP_ROOT . WS_DIR_PAGES . route::$route['page'] .'.inc.php')) {

    include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_PAGES . route::$route['page'] .'.inc.php');

  } else {

    http_response_code(404);

    if (preg_match('#\.[a-z]{2,4}$#', route::$request)) exit;

    $not_found_file = FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'not_found.log';

    $lines = is_file($not_found_file) ? file($not_found_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : array();
    $lines[] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $lines = array_unique($lines);

    sort($lines);

    if (count($lines) >= 100) {
      $email = new email();
      $email->add_recipient(settings::get('store_email'))
            ->set_subject('[Not Found Report] '. settings::get('store_name'))
            ->add_body(PLATFORM_NAME .' '. PLATFORM_VERSION ."\r\n\r\n". implode("\r\n", $lines))
            ->send();
      file_put_contents($not_found_file, '');
    } else {
      file_put_contents($not_found_file, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    echo '<div>'
       . '  <h1>HTTP 404 - Not Found</h1>'
       . '  <p>Could not find a matching reference for '. parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) .'.</p>'
       . '</div>';
  }

  require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
