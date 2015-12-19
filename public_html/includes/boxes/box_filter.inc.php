<?php
  
  if (!in_array(route::$route['page'], array('category', 'manufacturer'))) return;
  
  $box_filter_cache_id = cache::cache_id('box_filter', array('language', 'get'));
  if (cache::capture($box_filter_cache_id, 'file')) {
  
    $box_filter = new view();

    $box_filter->snippets = array(
      'manufacturers' => array(),
      'product_groups' => array(),
    );
    
  // Manufacturers
    if (empty($_GET['manufacturer_id'])) {
      $manufacturers_query = database::query(
        "select distinct m.id, m.name from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_MANUFACTURERS ." m on m.id = p.manufacturer_id ".
        (!empty($_GET['category_id']) ? " left join " . DB_TABLE_PRODUCTS_TO_CATEGORIES . " pc on pc.product_id = p.id " : "")."
        where p.status
        and manufacturer_id
        ". (!empty($_GET['category_id']) ? "and pc.category_id = " . (int)$_GET['category_id']  : "") ."
        order by m.name asc;"
      );
      if (database::num_rows($manufacturers_query)) {
        
        while($manufacturer = database::fetch($manufacturers_query)) {
          $box_filter->snippets['manufacturers'][] = array(
            'id' => $manufacturer['id'],
            'name' => $manufacturer['name'],
            'href' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
          );
        }
      }
    }
    
  // Product Groups
    $products_query = database::query(
      "select distinct product_groups from ". DB_TABLE_PRODUCTS .
      (!empty($_GET['category_id']) ? " left join " . DB_TABLE_PRODUCTS_TO_CATEGORIES . " pc on pc.product_id = id " : "").
      "where status
      and product_groups != ''
      ". (!empty($_GET['manufacturer_id']) ? "and manufacturer_id = '". (int)$_GET['manufacturer_id'] ."'" : "") ."
      ". (!empty($_GET['manufacturers']) ? "and (find_in_set('". implode("', manufacturer_id) or find_in_set('", database::input($_GET['manufacturers'])) ."', manufacturer_id))" : "") ."
      ". (!empty($_GET['category_id']) ? "and pc.category_id = " . (int)$_GET['category_id']  : "") ."
      ;"
    );
    
    $product_groups = array();
    while ($product = database::fetch($products_query)) {
      $sets = explode(',', $product['product_groups']);
      foreach ($sets as $set) {
        list($group_id, $value_id) = explode('-', $set);
        $product_groups[(int)$group_id][(int)$value_id] = (int)$value_id;
      }
    }
    
    if (!empty($product_groups)) {
      
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
    
    echo $box_filter->stitch('views/box_filter');
    
    cache::end_capture($box_filter_cache_id);
  }
?>