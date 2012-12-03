<?php
  require_once('../includes/app_header.inc.php');
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'product.inc.php');
  
  switch ($system->language->selected['code']) {
    case 'da':
      $cid = '3865967';
      $subid = '121762';
      $aid = '11200923';
      break;
    case 'nb':
      $cid = '3865967';
      $subid = '121763';
      $aid = '11200921';
      break;
    case 'sv':
      $cid = '3865967';
      $subid = '121761';
      $aid = '11200916';
      break;
  }
  
  $output = '<?xml version="1.0" encoding="ISO-8859-1"?>' . PHP_EOL
          . '<produkter>' . PHP_EOL;
  
  $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
  while ($product = $system->database->fetch($products_query)) {
    $product = new ref_product($product['id'], $system->currency->selected['code']);
    
    $category = $system->database->fetch($system->database->query("select name from ". DB_TABLE_CATEGORIES_INFO ." where category_id = '". (int)$product->categories[0] ."' and language_code = '". $system->language->selected['code'] ."' limit 1;"));
    
    $output .= '  <produkt>' . PHP_EOL
             . '    <kategorinavn>'. htmlspecialchars($category['name']) .'</kategorinavn>' . PHP_EOL
             . '    <produktid>'. (int)$product->id .'</produktid>' . PHP_EOL
             . '    <produktnavn>'. htmlspecialchars($product->name[$system->language->selected['code']]) .'</produktnavn>' . PHP_EOL
             . '    <produktbeskrivelse>'. (!empty($product->description[$system->language->selected['code']]) ? htmlspecialchars(str_replace(array("\r", "\n"), array('', ''), strip_tags($product->description[$system->language->selected['code']]))) : '') .'</produktbeskrivelse>' . PHP_EOL
             . '    <pris>'. $system->currency->calculate(!empty($product->campaign) ? $product->campaign['price'] : $product->price, $system->currency->selected['code']) .'</pris>' . PHP_EOL
             . '    <billedurl>'. ($product->image ? htmlspecialchars(str_replace(' ', '%20', $system->document->link(WS_DIR_IMAGES . $product->image))) : '') .'</billedurl>' . PHP_EOL
             . '    <vareurl>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product->id))) .'</vareurl>' . PHP_EOL
             . '  </produkt>' . PHP_EOL;
  }
  
  $output .= '</produkter>';
  
  if (strtolower($system->language->selected['charset']) == 'utf-8') {
    $output = utf8_decode($output);
  }
  
  header('Content-type: application/xml; charset=iso-8859-1');
  
  echo $output;
?>