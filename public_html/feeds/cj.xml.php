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
  
  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<!DOCTYPE product_catalog_data SYSTEM "http://www.cj.com/downloads/tech/dtd/product_catalog_data_1_1.dtd">' . PHP_EOL
          . '<product_catalog_data>' . PHP_EOL
          . '  <header>' . PHP_EOL
          . '    <cid>'. $cid .'</cid>' . PHP_EOL
          . '    <subid>'. $subid .'</subid>' . PHP_EOL
          . '    <datefmt>YYYY-MM-DD HH24:MI:SS</datefmt>' . PHP_EOL
          . '    <processtype>OVERWRITE</processtype>' . PHP_EOL
          . '    <aid>'. $aid .'</aid>' . PHP_EOL
          . '  </header>' . PHP_EOL;
  
  $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
  while ($product = $system->database->fetch($products_query)) {
    $product = new ref_product($product['id'], $system->currency->selected['code']);
    
    $category = $system->database->fetch($system->database->query("select name from ". DB_TABLE_CATEGORIES_INFO ." where category_id = '". (int)$product->categories[0] ."' and language_code = '". $system->language->selected['code'] ."' limit 1;"));
    
    $output .= '  <product>' . PHP_EOL
             . '    <name>'. htmlspecialchars($product->name[$system->language->selected['code']]) .'</name>' . PHP_EOL
             . '    <keywords>'. (empty($product->meta_keywords[$system->language->selected['code']]) == false ? htmlspecialchars($product->meta_keywords[$system->language->selected['code']]) : htmlspecialchars($product->name[$system->language->selected['code']])) .'</keywords>' . PHP_EOL
             . '    <description>'. (!empty($product->description[$system->language->selected['code']]) ? htmlspecialchars(str_replace(array("\r", "\n"), array('', ''), strip_tags($product->description[$system->language->selected['code']]))) : htmlspecialchars($product->name[$system->language->selected['code']])) .'</description>' . PHP_EOL
             //. '    <sku>'. $product->sku .'</sku>' . PHP_EOL
             . '    <sku>'. (!empty($product->sku) ? $product->sku : $product->code) .'</sku>' . PHP_EOL
             . '    <buyurl>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product->id))) .'</buyurl>' . PHP_EOL
             . '    <available>'. (!empty($product->status) ? 'YES' : 'NO') .'</available>' . PHP_EOL
             . '    <imageurl>'. ($product->image ? htmlspecialchars(str_replace(' ', '%20', $system->document->link(WS_DIR_IMAGES . $product->image))) : '') .'</imageurl>' . PHP_EOL
             . '    <price>'. $system->currency->calculate($product->price, $system->currency->selected['code']) .'</price>' . PHP_EOL
             . '    <saleprice>'. $system->currency->calculate(!empty($product->campaign) ? $product->campaign['price'] : $product->price, $system->currency->selected['code']) .'</saleprice>' . PHP_EOL
             . '    <currency>'. $system->currency->selected['code'] .'</currency>' . PHP_EOL
             . '    <advertisercategory>'. htmlspecialchars($category['name']) .'</advertisercategory>' . PHP_EOL
             . '    <manufacturer>'. htmlspecialchars($product->manufacturer['name']) .'</manufacturer>' . PHP_EOL
             . '    <manufacturerid>'. htmlspecialchars($product->manufacturer_id) .'</manufacturerid>' . PHP_EOL
             . '    <offline>NO</offline>' . PHP_EOL // Optional
             . '    <online>YES</online>' . PHP_EOL // Optional
             //. '    <instock>'. (!empty($product->quantity) ? 'YES' : 'NO') .'</instock>' . PHP_EOL
             . '    <instock>YES</instock>' . PHP_EOL
             . '  </product>' . PHP_EOL;
  }
  
  $output .= '</product_catalog_data>';
  
  if (strtolower($system->language->selected['charset']) != 'utf-8') {
    $output = utf8_encode($output);
  }
  
  header('Content-type: application/xml; charset='. $system->language->selected['charset']);
  
  echo $output;
?>