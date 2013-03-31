<?php
  require_once('includes/application_top.php');
  tep_session_unregister('navigation');
  
  $languages_query = tep_db_query("select * from ". TABLE_LANGUAGES ." where directory = '". tep_db_input($language) ."' limit 0, 1;");
  $languages = tep_db_fetch_array($languages_query);
  
  $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
       . '<catalog>' . PHP_EOL
       . '  <header>' . PHP_EOL
       . '    <store_name>'. htmlspecialchars(STORE_NAME, ENT_QUOTES, CHARSET) .'</store_name>' . PHP_EOL
       . '    <language_code>'. $languages['code'] .'</language_code>' . PHP_EOL
       . '    <currency_code>'. $currency .'</currency_code>' . PHP_EOL
       . '    <date>'. date('r') .'</date>' . PHP_EOL
       . '  </header>' . PHP_EOL
       . '  <manufacturers>' . PHP_EOL;
  
  $manufacturers_query = tep_db_query("SELECT * FROM ". TABLE_MANUFACTURERS ." order by manufacturers_id;");
  while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
    $xml .= '    <manufacturer>' . PHP_EOL
          . '      <status>1</status>' . PHP_EOL
          . '      <code>'. $manufacturers['manufacturers_id'] .'</code>' . PHP_EOL
          . '      <name>'. htmlspecialchars($manufacturers['manufacturers_name'], ENT_QUOTES, CHARSET) .'</name>' . PHP_EOL
          . '      <image>'. ($manufacturers['manufacturers_image'] ? htmlspecialchars(tep_href_link(DIR_WS_IMAGES . $manufacturers['manufacturers_image']), ENT_QUOTES, CHARSET) : '') .'</image>' . PHP_EOL
          . '    </manufacturer>' . PHP_EOL;
  }
  $xml .= '  </manufacturers>' . PHP_EOL
        . '  <categories>' . PHP_EOL;
       
  function build_categories($parent_id=0) {
    global $languages, $xml;
    $categories_query = tep_db_query("SELECT c.*, cd.categories_name FROM ". TABLE_CATEGORIES ." c LEFT JOIN ". TABLE_CATEGORIES_DESCRIPTION ." cd ON (c.categories_id = cd.categories_id and language_id = '". (int)$languages['languages_id'] ."') WHERE c.parent_id = '". (int)$parent_id ."';");
    while ($categories = tep_db_fetch_array($categories_query)) {
      $xml .= '    <category>' . PHP_EOL
            . '      <status>'. (isset($categories['categories_status']) ? (int)$categories['categories_status'] : 1) .'</status>' . PHP_EOL
            . '      <name>'. htmlspecialchars($categories['categories_name'], ENT_QUOTES, CHARSET) .'</name>' . PHP_EOL
            . '      <code>'. (int)$categories['categories_id'] .'</code>' . PHP_EOL
            . '      <parent_id>'. (int)$categories['parent_id'] .'</parent_id>' . PHP_EOL
            . '      <description>'. htmlspecialchars($categories['categories_description'], ENT_QUOTES, CHARSET) .'</description>' . PHP_EOL
            . '      <short_description></short_description>' . PHP_EOL
            . '      <keywords></keywords>' . PHP_EOL
            . '      <image>'. ($categories['categories_image'] ? htmlspecialchars(tep_href_link(DIR_WS_IMAGES . $categories['categories_image']), ENT_QUOTES, CHARSET) : '') .'</image>' . PHP_EOL
            . '    </category>' . PHP_EOL;
      build_categories($categories['categories_id']);
    }
  }
  build_categories();
  
  $xml .= '  </categories>' . PHP_EOL
        . '  <products>' . PHP_EOL;
  
  $products_query = tep_db_query("SELECT p.* FROM ". TABLE_PRODUCTS ." p ORDER BY p.products_id;");
  while ($products = tep_db_fetch_array($products_query)) {
    
    $products_description_query = tep_db_query("SELECT * FROM ". TABLE_PRODUCTS_DESCRIPTION ." WHERE products_id = '". $products['products_id'] ."' AND language_id = '". $languages['languages_id'] ."' limit 0, 1;");
    $products_description = tep_db_fetch_array($products_description_query);
    
    $manufacturers_query = tep_db_query("SELECT * FROM ". TABLE_MANUFACTURERS ." WHERE manufacturers_id = '". $products['manufacturers_id'] ."' limit 0, 1;");
    $manufacturers = tep_db_fetch_array($manufacturers_query);
    
    $categories_description_query = tep_db_query("SELECT * FROM categories_description WHERE categories_id = '". $products['categories_id'] ."' AND language_id = '". $languages['languages_id'] ."' limit 0, 1;");
    $categories_description = tep_db_fetch_array($categories_description_query);
    
    $xml .= '    <product>' . PHP_EOL
          . '      <status>'. (empty($products['products_status']) ? 0 : 1) .'</status>' . PHP_EOL
          . '      <name>'. htmlspecialchars($products_description['products_name']) .'</name>' . PHP_EOL
          . '      <code>'. (int)$products['products_id'] .'</code>' . PHP_EOL
          . '      <sku>'. $products['products_model'] .'</sku>' . PHP_EOL
          . '      <upc></upc>' . PHP_EOL
          . '      <ean></ean>' . PHP_EOL
          . '      <taric></taric>' . PHP_EOL
          . '      <description>'. htmlspecialchars($products_description['products_description']) .'</description>' . PHP_EOL
          . '      <short_description>'. (!empty($products_description['products_head_desc_tag']) ? htmlspecialchars($products_description['products_head_desc_tag']) : '') .'</short_description>' . PHP_EOL
          . '      <keywords>'. (!empty($products_description['products_head_keywords_tag']) ? htmlspecialchars($products_description['products_head_keywords_tag']) : '') .'</keywords>' . PHP_EOL
          . '      <attributes>'. (!empty($products_description['products_head_keywords_tag']) ? '<![CDATA['. htmlspecialchars($products_description['products_data_table']) .']]>' : '') .'</attributes>' . PHP_EOL
          . '      <manufacturer>' . PHP_EOL
          . '        <code>'. $manufacturers['manufacturers_id'] .'</code>' . PHP_EOL
          . '        <name>'. htmlspecialchars($manufacturers['manufacturers_name']) .'</name>' . PHP_EOL
          . '      </manufacturer>' . PHP_EOL
          . '      <categories>' . PHP_EOL;
    
    $categories_query = tep_db_query("SELECT ptc.categories_id, cd.categories_name FROM ". TABLE_PRODUCTS_TO_CATEGORIES ." ptc LEFT JOIN ". TABLE_CATEGORIES_DESCRIPTION ." cd ON (ptc.categories_id = cd.categories_id and language_id = '". (int)$languages['languages_id'] ."') WHERE ptc.products_id = '". (int)$products['products_id'] ."';");
    while ($categories = tep_db_fetch_array($categories_query)) {
      $xml .= '        <category>' . PHP_EOL
            . '          <code>'. (int)$categories['categories_id'] .'</code>' . PHP_EOL
            . '        </category>' . PHP_EOL;
    }
    $xml .= '      </categories>' . PHP_EOL;
    
    $products_options_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id = '" . (int)$products['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages['languages_id'] . "' order by popt.products_options_name");
    if (tep_db_num_rows($products_options_query)) {
      $xml .= '      <options>' . PHP_EOL;
      while ($products_options = tep_db_fetch_array($products_options_query)) {
        $xml .= '        <group>' . PHP_EOL
              . '          <name>'. $products_options['products_options_name'] .'</name>' . PHP_EOL
              . '          <values>' . PHP_EOL;
        $products_options_values_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$products['products_id'] . "' and pa.options_id = '" . (int)$products_options['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages['languages_id'] . "' and pov.products_options_values_id");
        while ($products_options_values = tep_db_fetch_array($products_options_values_query)) {  
          $xml .= '            <value>' . PHP_EOL
                . '              <name>'. $products_options_values['products_options_values_name'] .'</name>' . PHP_EOL
                . '              <price_adjust>'. (($products_options_values['options_values_price'] == '-') ? -$products_options_values['options_values_price'] : $products_options_values['options_values_price']) .'</price_adjust>' . PHP_EOL
                . '            </value>' . PHP_EOL;
        }
        $xml .= '          </values>' . PHP_EOL
              . '        </group>' . PHP_EOL;
      }
      
      $xml .= '      </options>' . PHP_EOL;
    }
    
    if (defined('TABLE_PRODUCTS_STOCK')) {
      $products_stock_query = tep_db_query("select * from ". TABLE_PRODUCTS_STOCK ." where products_id = '". (int)$product['products_id'] ."';");
      if (tep_db_num_rows($products_stock_query)) {
        $xml .= '      <options_stock>' . PHP_EOL;
        while ($products_stock = tep_db_fetch_array($products_stock_query)) {
          $xml .= '      <stock>' . PHP_EOL
                . '        <combinations>' . PHP_EOL;
          foreach (explode(',', $products_stock['products_stock_attributes']) as $pair) {
            list($option_id, $value_id) = explode('-', $pair);
            $products_options = tep_dc_fetch_array(tep_db_query("select products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '". (int)$option_id ."' and language_id = '" . (int)$languages['languages_id'] . "' limit 1;"));
            $products_options_values = tep_dc_fetch_array(tep_db_query("select products_options_values_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_values_id = '". (int)$value_id ."' and language_id = '" . (int)$languages['languages_id'] . "' limit 1;"));
            $xml . '        <combination>' . PHP_EOL
                 . '          <group>'. $products_options['products_options_name'] .'</group>' . PHP_EOL
                 . '          <value>'. $products_options_values['products_options_values_name'] .'</value>' . PHP_EOL
                 . '        </combination>' . PHP_EOL;
          }
          $xml . '        <combinations>' . PHP_EOL
               . '        <quantity>'. (int)$products_stock['products_stock_quantity'] .'</quantity>' . PHP_EOL
               . '      </stock>' . PHP_EOL;
        }
        $xml .= '      </options_stock>' . PHP_EOL;
      }
    }
    
    $xml .= '      <images>' . PHP_EOL;
    if (defined(TABLE_PRODUCTS_IMAGES)) {
      $products_images_query = tep_db_query("SELECT * FROM ". TABLE_PRODUCTS_IMAGES ." WHERE products_id = '". $products['products_id'] ."' ORDER BY sort_order;");
      while ($products_images = tep_db_fetch_array($products_images_query)) {
        $xml .= '        <image>'. ($products_images['image'] ? htmlspecialchars(tep_href_link(DIR_WS_IMAGES . $products_images['image']), ENT_QUOTES, CHARSET) : '') .'</image>' . PHP_EOL;
      }
    } else if (defined('TABLE_ADDITIONAL_IMAGES')) {
      if (!empty($products['products_image_pop'])) {
        $xml .= '        <image>'. htmlspecialchars(tep_href_link(DIR_WS_IMAGES . $products['products_image_pop']), ENT_QUOTES, CHARSET) .'</image>' . PHP_EOL;
      }
      $products_images_query = tep_db_query("SELECT * FROM ". TABLE_ADDITIONAL_IMAGES ." WHERE products_id = '". $products['products_id'] ."' ORDER BY additional_images_id;");
      while ($products_images = tep_db_fetch_array($products_images_query)) {
        $xml .= '        <image>'. ($products_images['popup_images'] ? htmlspecialchars(tep_href_link(DIR_WS_IMAGES . $products_images['popup_images']), ENT_QUOTES, CHARSET) : '') .'</image>' . PHP_EOL;
      }
    } else if (!empty($products['products_image'])) {
      $xml .= '        <image>'. htmlspecialchars(tep_href_link(DIR_WS_IMAGES . $products['products_image']), ENT_QUOTES, CHARSET) .'</image>' . PHP_EOL;
    }
    $xml .= '      </images>' . PHP_EOL;
    
    $xml .= '      <purchase_price></purchase_price>' . PHP_EOL
          . '      <price>'. ($currencies->get_value($currency) * $products['products_price']) .'</price>' . PHP_EOL;
    
    $specials_query = tep_db_query("SELECT specials_new_products_price FROM " . TABLE_SPECIALS . " WHERE products_id = '" . (int)$products['products_id'] . "' AND status");
    if (tep_db_num_rows($specials_query)) {
      $xml .= '      <campaigns>' . PHP_EOL;
      while ($products_special = tep_db_fetch_array($specials_query)) {
        $xml .= '        <campaign>' . PHP_EOL
              . '          <price>'. ($currencies->get_value($currency) * $products_special['specials_new_products_price']) .'</price>' . PHP_EOL
              . '          <start_date></start_date>' . PHP_EOL
              . '          <end_date>'. (!empty($products_special['expired_date']) ? date('r', strtotime($products_special['expired_date'])) : '') .'</end_date>' . PHP_EOL
              . '        </campaign>' . PHP_EOL;
      }
      $xml .= '      </campaigns>' . PHP_EOL;
    }
    $xml .= '      <width></width>' . PHP_EOL
          . '      <height></height>' . PHP_EOL
          . '      <length></length>' . PHP_EOL
          . '      <length_class></length_class>' . PHP_EOL
          . '      <weight>'. (float)$products['products_weight'] .'</weight>' . PHP_EOL
          . '      <weight_class>kg</weight_class>' . PHP_EOL
          . '      <tax_rate>25</tax_rate>' . PHP_EOL
          . '      <quantity>'. (int)$products['products_quantity'] .'</quantity>' . PHP_EOL
          . '      <link>'. htmlspecialchars(tep_href_link('product_info.php', 'products_id='. $products['products_id']), ENT_QUOTES, CHARSET) .'</link>' . PHP_EOL
          . '      <views>'. (int)$products_description['products_viewed'] .'</views>' . PHP_EOL
          . '      <purchases>'. (int)$products['products_ordered'] .'</purchases>' . PHP_EOL
          . '      <date_updated>'. date('r', strtotime($products['products_last_modified'])) .'</date_updated>' . PHP_EOL
          . '      <date_created>'. date('r', strtotime($products['products_date_added'])) .'</date_created>' . PHP_EOL
          . '    </product>' . PHP_EOL;
  }
  
  $xml .=  '  </products>' . PHP_EOL
         . '</catalog>';
  
  header('Content-Type: application/xml; charset=UTF-8');
  echo strtolower(CHARSET) == 'utf-8' ? $xml : utf8_encode($xml);

?>