<?php
  require_once('includes/application_top.php');
  tep_session_unregister('navigation');
  
  switch ($_SERVER['SERVER_NAME']) {
    case 'www.site.dk':
    case 'site.dk':
      $_GET['language'] = 'da';
      $_GET['currency'] = 'DKK';
      break;
    case 'www.site.no':
    case 'site.no':
      $_GET['language'] = 'nb';
      $_GET['currency'] = 'NOK';
      break;
    default:
      $_GET['language'] = 'en';
      $_GET['currency'] = 'EUR';
      break;
  }

######################################################################

// Get configuration settings
  $configuration_query = tep_db_query("select * from ". TABLE_CONFIGURATION ." where configuration_key in ('DEFAULT_CURRENCY', 'DEFAULT_LANGUAGE');");
  while ($configuration=tep_db_fetch_array($configuration_query)) {
    if (!defined($configuration['configuration_key'])) define($configuration['configuration_key'], $configuration['configuration_value']);
  }
  
  $languages_query = tep_db_query("select * from ". TABLE_LANGUAGES ." where ". (($_GET['language']) ? "code='". tep_db_input($_GET['language']) ."'" : "code='". DEFAULT_LANGUAGE ."'") ." limit 0, 1;");
  $languages = tep_db_fetch_array($languages_query);
  if (!$languages) die("select * from ". TABLE_LANGUAGES ." where ". (($_GET['language']) ? "code='". tep_db_input($_GET['language']) ."'" : "code='". DEFAULT_LANGUAGE ."'") ." limit 0, 1;");
  
  $currencies_query = tep_db_query("select * from ". TABLE_CURRENCIES ." where ". (($_GET['language']) ? "code='". tep_db_input($_GET['currency']) ."'" : "code='". DEFAULT_CURRENCY ."'") ." limit 0, 1;");
  $currencies = tep_db_fetch_array($currencies_query);
  if (!$currencies) die('Error: currency');
  
  header('Content-type: text/plain; charset=iso-8859-1');
  //header('Content-type: application/csv; charset=iso-8859-1');
  //header('Content-Disposition: attachment; filename=osc-products-'. $_GET['language'] .'.csv');
  
  function csv_output_row($array) {
    $delimiter = ';';
    
    foreach ($array as $key => $value) {
      $array[$key] = html_entity_decode($value, ENT_QUOTES, 'iso-8859-1').'"';
      $array[$key] = stripslashes($array[$key]);
      $array[$key] = '"'.str_replace('"', '', $value).'"';
    }
    return implode($delimiter, $array) . PHP_EOL;
  }

  echo csv_output_row(array(
    'manufacturer_name',
    'status',
    'code',
    'name',
    'short_description',
    'description',
    'keywords',
    'attributes',
    'images',
    'price',
    'quantity',
    'language_code',
    'currency_code',
  ));
  
  /*
  $products_query = tep_db_query(
    "SELECT p.*, ptc.categories_id FROM ". TABLE_PRODUCTS ." p
    LEFT JOIN ". TABLE_PRODUCTS_TO_CATEGORIES ." ptc ON (p.products_id = ptc.products_id AND ptc.products_id = p.products_id)
    ORDER BY ptc.categories_id ASC;"
  );
  */
  $products_query = tep_db_query(
    "SELECT distinct p.* FROM ". TABLE_PRODUCTS ." p
    LEFT JOIN ". TABLE_PRODUCTS_TO_CATEGORIES ." ptc ON (p.products_id = ptc.products_id AND ptc.products_id = p.products_id)
    ORDER BY ptc.categories_id ASC;"
  );
  $i = 0;
  while ($products = tep_db_fetch_array($products_query)) {
   // if (++$i == 10) break;
    
    $products_description_query = tep_db_query("SELECT * FROM ". TABLE_PRODUCTS_DESCRIPTION ." WHERE products_id='". $products['products_id'] ."' AND language_id = '". $languages['languages_id'] ."' limit 0, 1;");
    $products_description = tep_db_fetch_array($products_description_query);
    
    $manufacturers_query = tep_db_query("SELECT * FROM ". TABLE_MANUFACTURERS ." WHERE manufacturers_id='". $products['manufacturers_id'] ."' limit 0, 1;");
    $manufacturers = tep_db_fetch_array($manufacturers_query);
    
    $categories_description_query = tep_db_query("SELECT * FROM categories_description WHERE categories_id='". $products['categories_id'] ."' AND language_id='". $languages['languages_id'] ."' limit 0, 1;");
    $categories_description = tep_db_fetch_array($categories_description_query);
    
    $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$products['products_id'] . "' and status;");
    $specials = tep_db_fetch_array($specials_query);
    
    $attributes = array();
    
    echo csv_output_row(array(
      $manufacturers['manufacturers_name'],
      (($products['products_status']) ? '1' : '0'),
      $products['products_id'],
      $products_description['products_name'],
      '', //short_description
      $products_description['products_description'],
      (($products_description['products_head_keywords_tag']) ? $products_description['products_head_keywords_tag'] : ''),
      implode("\r\n", $attributes), //attributes
      'products_old/'.$products['products_image'],
      $products['products_price'],
      $products['products_quantity'],
      $_GET['language'],
      $_GET['currency'],
    ));
  }
  
?>