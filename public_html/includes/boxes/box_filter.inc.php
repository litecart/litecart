<?php
  if (!in_array(link::relpath($_SERVER['SCRIPT_NAME']), array('category.php', 'manufacturer.php'))) return;
  
  $box_filter_cache_id = cache::cache_id('box_customer_service_links', array('language'));
  if (cache::capture($box_filter_cache_id, 'file')) {
  
    $box_filter = new view();

    $box_fitler->snippets = array(
      'manufacturers' => array(),
      'product_groups' => array(),
    );
    
  // Manufacturers
    if (empty($_GET['manufacturer_id'])) {
      $manufacturers_query = database::query(
        "select distinct m.id, m.name from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_MANUFACTURERS ." m on m.id = p.manufacturer_id
        where p.status
        ". (!empty($_GET['category_id']) ? "and find_in_set('". (int)$_GET['category_id'] ."', categories)" : "") ."
        ;"
      );
      if (database::num_rows($manufacturers_query) > 1) {
        
        while($manufacturer = database::fetch($manufacturers_query)) {
          $box_fitler->snippets['manufacturers'][] = array(
            'id' => $manufacturer['id'],
            'name' => $manufacturer['name'],
            'href' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
          );
        }
      }
    }
    
  // Product Groups
    $product_groups_query = database::query(
      "select distinct product_groups from ". DB_TABLE_PRODUCTS ."
      where status
      and product_groups != ''
      ". (!empty($_GET['manufacturer_id']) ? "and manufacturer_id = '". (int)$_GET['manufacturer_id'] ."'" : "") ."
      ". (!empty($_GET['manufacturers']) ? "and (find_in_set('". implode("', manufacturer_id) or find_in_set('", database::input($_GET['manufacturers'])) ."', manufacturer_id))" : "") ."
      ". (!empty($_GET['category_id']) ? "and find_in_set('". (int)$_GET['category_id'] ."', categories)" : "") ."
      ;"
    );
    
    while ($product = database::fetch($product_groups_query)) {
      $sets = explode(',', $product['product_groups']);
      foreach ($sets as $set) {
        list($group_id, $value_id) = explode('-', $set);
        $product_groups[(int)$group_id][(int)$value_id] = (int)$value_id;
      }
    }
    
    $has_multiple_product_groups = false;
    if (!empty($product_groups)) {
      foreach ($product_groups as $group) {
        if (count($group) > 1) {
          $has_multiple_product_groups = true;
          break;
        }
      }
    }
    
    if ($has_multiple_product_groups) {
      
      $product_groups_query = database::query(
        "select product_group_id as id, name from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
        where product_group_id in ('". implode("', '", array_keys($product_groups)) ."')
        and language_code = '". database::input(language::$selected['code']) ."'
        order by name;"
      );
      
      while ($group = database::fetch($product_groups_query)) {
        
        $box_filter->snippets['product_groups'][$group['id']] = array(
          'id' => $group['id'],
          'name' => $group['name'],
          'values' => array(),
        );
        
        $product_group_values_query = database::query(
          "select product_group_value_id as id, name from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id in ('". implode("', '", $product_groups[$group['id']]) ."')
          and language_code = '". database::input(language::$selected['code']) ."'
          order by name;"
        );
      
        while ($value = database::fetch($product_group_values_query)) {
          $box_filter->snippets['product_groups'][$group['id']]['values'][$value['id']] = array(
            'id' => $value['id'],
            'name' => $value['name'],
          );
        }
      }
    }
    
    echo $box_filter->stitch('box_filter');
    
    cache::end_capture($box_customer_service_links_cache_id);
  }
?>