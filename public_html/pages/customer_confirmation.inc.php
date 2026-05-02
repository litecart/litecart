<?php
/*!
# description: bestätigung der e-Mail adresse wenn ein kundenkonto angelegt wird
*/

  document::$layout = 'blank';

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';

  try {

    if (empty($_GET['public_key'])) {
      throw new Exception('Public Key is unknown', 404);
    }

    // averlon; hier muss jetzt der DB-datensatz für den kunden gesucht werden
    $customer_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."customers
      where public_key = '". $_GET['public_key'] ."'
      limit 1;"
    );
    
    // $avcustomer = new ent_customer();
    $avcustomer = database::fetch($customer_query);
    $customer = new ent_customer($avcustomer['id']);

    // $customer = new ent_customer($_GET['public_key']);
    $customer->data['status'] = 1;
    $customer->save();

  } catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  document::$snippets['title'][] = language::translate('title_customer', 'Customer') .' #'. (int)$customer->data['id'];

  $_page = new ent_view();

  echo $_page->stitch('pages/customer_confirmation');
