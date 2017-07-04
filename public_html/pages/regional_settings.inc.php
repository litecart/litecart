<?php
  header('X-Robots-Tag: noindex');

  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_header.inc.php');
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
    document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  }

  document::$snippets['title'][] = language::translate('regional_settings:head_title', 'Regional Settings');

  breadcrumbs::add(language::translate('title_regional_settings', 'Regional Settings'));

  if (isset($_POST['save'])) {

    $_POST['language_code'] = !empty($_POST['language_code']) ? $_POST['language_code'] : '';
    $_POST['currency_code'] = !empty($_POST['currency_code']) ? $_POST['currency_code'] : '';
    $_POST['country_code'] = !empty($_POST['country_code']) ? $_POST['country_code'] : '';
    $_POST['zone_code'] = !empty($_POST['zone_code']) ? $_POST['zone_code'] : '';
    $_POST['display_prices_including_tax'] = isset($_POST['display_prices_including_tax']) ? (int)$_POST['display_prices_including_tax'] : (int)settings::get('default_display_prices_including_tax');

    language::set($_POST['language_code']);

    currency::set($_POST['currency_code']);

    customer::$data['country_code'] = $_POST['country_code'];
    customer::$data['zone_code'] = $_POST['zone_code'];

    customer::$data['shipping_address']['country_code'] = $_POST['country_code'];
    customer::$data['shipping_address']['zone_code'] = $_POST['zone_code'];

    customer::$data['display_prices_including_tax'] = $_POST['display_prices_including_tax'];

    setcookie('language_code', $_POST['language_code'], time() + (60*60*24*365), WS_DIR_HTTP_HOME);
    setcookie('currency_code', $_POST['currency_code'], time() + (60*60*24*365), WS_DIR_HTTP_HOME);
    setcookie('country_code', $_POST['country_code'], time() + (60*60*24*365), WS_DIR_HTTP_HOME);
    setcookie('zone_code', $_POST['zone_code'], time() + (60*60*24*365), WS_DIR_HTTP_HOME);
    setcookie('display_prices_including_tax', $_POST['display_prices_including_tax'], time() + (60*60*24*365), WS_DIR_HTTP_HOME);

    if (empty($_GET['redirect'])) $_GET['redirect'] = document::ilink('', array(), null, null, $_POST['language_code']);

    header('Location: '. $_GET['redirect']);
    exit;
  }

  $_page = new view();
  echo $_page->stitch('pages/regional_settings');
