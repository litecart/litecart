<?php
  require_once('../includes/app_header.inc.php');
  
  $language_code = 'en';
  $currency_code = 'EUR';
  
  $fields = array(
    'category_id',
    'category_name',
    'product_id',
    'product_name',
    'product_short_description',
    'product_image',
    'product_price',
    'product_specials_price',
    'language_code',
    'currency_code',
  );
  
  function csv_output_row($array) {
    global $system;
    
    $delimiter = ';';
    
    foreach ($array as $key => $value) {
      $array[$key] = html_entity_decode($value, ENT_QUOTES, $system->language->selected['charset']).'"';
      if (strpos($array[$key], ';') !== false || strpos($array[$key], "\r") !== false || strpos($array[$key], "\n") !== false) {
        $array[$key] = '"'. str_replace('"', '', $value) .'"';
      } else {
        $array[$key] = str_replace('"', '', $value);
      }
    }
    return implode($delimiter, $array) . PHP_EOL;
  }
  
  $output = csv_output_row($fields);

  $categories_query = $system->functions->catalog_categories_query();
  while ($category = $system->database->fetch($categories_query)) {
  
    $products_query = $system->functions->catalog_products_query(array('category_id' => $category['id'], 'sort' => 'name'));
    while ($product = $system->database->fetch($products_query)) {
      $product = new ref_product($product['id'], $currency_code);
      
      $output .= csv_output_row(array(
        $category['id'],
        $category['name'],
        $product->id,
        $product->name[$language_code],
        $product->short_description[$language_code],
        $product->image ? $system->document->link(WS_DIR_IMAGES . $product->image) : '',
        $system->currency->calculate($product->price, $currency_code),
        (!empty($product->campaign) ? $system->currency->calculate($product->campaign['price'], $currency_code) : ''),
        $language_code,
        $system->settings->get('store_currency_code'),
      ));
    }
  }
  
  if (strtolower($system->language->selected['charset']) != 'utf-8') {
    $output = utf8_encode($output);
  }
  
  //header('Content-type: application/csv; charset='. $system->language->selected['charset']);
  header('Content-type: text/plain; charset=iso-8859-1');
  
  echo $output;
  
?>