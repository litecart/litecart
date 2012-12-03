<?php
  require_once('../includes/app_header.inc.php');
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'product.inc.php');
  
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
  
  $output = csv_output_row(array(
    'name',
    'category',
    'price',
    'description',
    'url',
    'image',
    'stock_status',
    'id',
  ));
  
  $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
  while ($product = $system->database->fetch($products_query)) {
    $product = new ref_product($product['id'], $system->currency->selected['code']);
    
    $category = $system->database->fetch($system->database->query("select name from ". DB_TABLE_CATEGORIES_INFO ." where category_id = '". (int)$product->categories[0] ."' and language_code = '". $system->language->selected['code'] ."' limit 1;"));
    
    $output .= csv_output_row(array(
      'name' => htmlspecialchars($product->name[$system->language->selected['code']]),
      'category' => htmlspecialchars($category['name']),
      'price' => $system->currency->calculate(!empty($product->campaign) ? $product->campaign['price'] : $product->price, $system->currency->selected['code']),
      'description' => htmlspecialchars(strip_tags($product->description[$system->language->selected['code']])),
      'url' => htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product->id))),
      'image' => $product->image ? htmlspecialchars($system->document->link(WS_DIR_IMAGES . $product->image)) : '',
      'stock_status' => 1,
      'id' => (int)$product->id,
    ));
  }
  
  if (strtolower($system->language->selected['charset']) == 'utf-8') {
    $output = utf8_decode($output);
  }
  
  header('Content-type: application/csv; charset=iso-8859-1');
  header('Content-Disposition: attachment; filename=affiliator-'. date('Ymd-His') .'.csv');
  
  echo $output;
?>