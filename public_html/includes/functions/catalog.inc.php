<?php

  function catalog_category_trail($category_id=0, $language_code='') {
    global $system;
    
    if (empty($language_code)) $language_code = $system->language->selected['code'];
    
    $trail = array();
    
    if (empty($category_id)) $category_id = 0;
    
    $categories_query = $system->database->query(
      "select c.id, c.parent_id, ci.name
      from ". DB_TABLE_CATEGORIES ." c 
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->database->input($language_code) ."')
      where c.id = '". (int)$category_id ."'
      limit 1;"
    );
    $category = $system->database->fetch($categories_query);
    
    if (!empty($category['parent_id'])) {
      $trail = $system->functions->catalog_category_trail($category['parent_id']);
      $trail[$category['id']] = $category['name'];
    } else if (isset($category['id'])) {
      $trail = array($category['id'] => $category['name']);
    }
    
    return $trail;
  }
  
  function catalog_category_descendants($category_id=0, $language_code='') {
    global $system;
    
    if (empty($language_code)) $language_code = $system->language->selected['code'];
    
    $subcategories = array();
    
    if (empty($category_id)) $category_id = 0;
    
    $categories_query = $system->database->query(
      "select c.id, c.parent_id, ci.name
      from ". DB_TABLE_CATEGORIES ." c 
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->database->input($language_code) ."')
      where c.parent_id = '". (int)$category_id ."';"
    );
    while ($category = $system->database->fetch($categories_query)) {
      $subcategories[$category['id']] = $category['name'];
      $subcategories = $subcategories + catalog_category_descendants($category['id'], $language_code);
    }
    
    return $subcategories;
  }
  
  function catalog_categories_query($parent_id=0) {
    global $system;
    
    $categories_query = $system->database->query(
      "select c.id, c.image, ci.name, ci.short_description
      from ". DB_TABLE_CATEGORIES ." c, ". DB_TABLE_CATEGORIES_INFO ." ci
      where c.status
      and parent_id = '". (int)$parent_id ."'
      and (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
      order by c.priority asc, ci.name asc;"
    );
    
    return $categories_query;
  }
  
  function catalog_products_query($filter=array()) {
    global $system;
    
    if (!is_array($filter)) trigger_error('Invalid array filter for products query', E_USER_ERROR);
    
    if (empty($filter['sort'])) $filter['sort'] = 'popularity';
    
    switch ($filter['sort']) {
      case 'name':
        $sql_sort = "pi.name asc, p.date_created desc";
        break;
      case 'price':
        $sql_sort = "final_price asc, pi.name asc";
        break;
      case 'date':
        $sql_sort = "p.date_valid_from desc, p.date_created desc, pi.name asc";
        break;
      case 'occurrences':
        $sql_sort = "occurrences desc, rand()";
        break;
      case 'rand':
        $sql_sort = "rand()";
        break;
      case 'popularity':
      default:
        $sql_sort = "(p.purchases / p.views) desc, (p.views / ((unix_timestamp() - unix_timestamp(p.date_created)) / 86400000)) desc, p.date_created desc";
        break;
    }
    
    if (!empty($filter['exclude_products']) && !is_array($filter['exclude_products'])) $filter['exclude_products'] = array($filter['exclude_products']);
    
    $filter = $system->database->input($filter);
    
    $sql_select_occurrences = "";
    $sql_andor = "and";

    
    if ($filter['sort'] == 'occurrences') {
      $sql_select_occurrences = "(0
        ". (isset($filter['product_name']) ? "+ if(pi.name = '". $system->database->input($filter['product_name']) ."', 1, 0)" : false) ."
        ". (isset($filter['sql_where']) ? "+ if(". $filter['sql_where'] .", 1, 0)" : false) ."
        ". (isset($filter['category_id']) ? "+ if(find_in_set('". (int)$filter['category_id'] ."', p.categories), 1, 0)" : false) ."
        ". (!empty($filter['categories']) ? "+ if(find_in_set('". implode("', p.categories), 1, 0) + if(find_in_set('", $filter['categories']) ."', p.categories), 1, 0)" : false) ."
        ". (isset($filter['designer_id']) ? "+ if(p.designer_id = '". (int)$filter['designer_id'] ."', 1, 0)" : false) ."
        ". (!empty($filter['designers']) ? "+ if(p.designer_id in ('". implode("', '", $system->database->input($filter['designers'])) ."'), 1, 0)" : false) ."
        ". (!empty($filter['keywords']) ? "+ if(find_in_set('". implode("', p.keywords), 1, 0) + if(find_in_set('", $filter['keywords']) ."', p.keywords), 1, 0)" : false) ."
        ". (isset($filter['manufacturer_id']) ? "+ if(p.manufacturer_id = '". (int)$filter['manufacturer_id'] ."', 1, 0)" : false) ."
        ". (!empty($filter['manufacturers']) ? "+ if(p.manufacturer_id in ('". implode("', '", $system->database->input($filter['manufacturers'])) ."'), 1, 0)" : false) ."
        ". (isset($filter['product_group_id']) ? "+ if(find_in_set('". (int)$filter['product_group_id'] ."', p.product_groups), 1, 0)" : false) ."
        ". (isset($filter['products']) ? "+ if(p.id in ('". implode("', '", $system->database->input($filter['products'])) ."'), 1, 0)" : false) ."
        ". (!empty($filter['product_groups']) ? "+ if(find_in_set('". implode("', p.product_groups), 1, 0) + if(find_in_set('", $filter['product_groups']) ."', p.product_groups), 1, 0)" : false) ."
      ) as occurrences";
      $sql_andor = "or";
    }
    
  // Create levels of product groups
    $sql_where_product_groups = "";
    if (!empty($filter['product_groups'])) {
      $product_groups = array();
      foreach ($filter['product_groups'] as $group_value) {
        list($group,) = explode('-', $group_value);
        $product_groups[$group][] = $group_value;
      }
      foreach ($product_groups as $group_value) {
        $sql_where_product_groups .= "$sql_andor (find_in_set('". implode("', p.product_groups) or find_in_set('", $group_value) ."', p.product_groups))";
      }
    }
    
    $sql_where_prices = "";
    if (!empty($filter['price_ranges'])) {
      foreach ($filter['price_ranges'] as $price_range) {
        list($min,$max) = explode('-', $price_range);
        $sql_where_prices .= " or (if(pc_tmp.campaign_price, pc_tmp.campaign_price, pp_tmp.price) >= ". (float)$min ." and if(pc_tmp.campaign_price, pc_tmp.campaign_price, pp_tmp.price) <= ". (float)$max .")";
      }
      $sql_where_prices = "$sql_andor (". ltrim($sql_where_prices, " or ") .")";
    }
    
    $query =
      "select p.id, p.product_groups, p.image, p.tax_class_id, p.quantity, p.date_created, pi.name, pi.short_description, pp_tmp.price, pc_tmp.campaign_price, if(pc_tmp.campaign_price, pc_tmp.campaign_price, pp_tmp.price) as final_price, p.manufacturer_id, m.name as manufacturer_name
      ". (($filter['sort'] == 'occurrences') ? ", " . $sql_select_occurrences : false) ."
      from ". DB_TABLE_PRODUCTS ." p
      left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and language_code = '". $system->database->input($system->language->selected['code']) ."')
      left join ". DB_TABLE_PRODUCTS_PRICES ." pp on (pp.product_id = p.id)
      left join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)
      left join (
        select pp.product_id, if(pp.". $system->database->input($system->currency->selected['code']) .", pp.". $system->database->input($system->currency->selected['code']) ."  / ". $system->currency->selected['value'] .", pp.". $system->database->input($system->settings->get('store_currency_code')) .") as price
        from ". DB_TABLE_PRODUCTS_PRICES ." pp
      ) pp_tmp on (pp_tmp.product_id = p.id)
      left join (
        select pc.product_id, if(pc.". $system->database->input($system->currency->selected['code']) .", pc.". $system->database->input($system->currency->selected['code']) ." / ". $system->currency->selected['value'] .", pc.". $system->database->input($system->settings->get('store_currency_code')) .") as campaign_price
        from ". DB_TABLE_PRODUCTS_CAMPAIGNS ." pc
        where (pc.start_date = '0000-00-00 00:00:00' or pc.start_date <= '". date('Y-m-d H:i:s') ."')
        and (pc.end_date = '0000-00-00 00:00:00' or pc.end_date >= '". date('Y-m-d H:i:s') ."')
        order by pc.end_date asc
      ) pc_tmp on (pc_tmp.product_id = p.id)
      where p.status
      and (p.date_valid_from = '0000-00-00 00:00:00' or p.date_valid_from <= '". date('Y-m-d H:i:00') ."')
      and (p.date_valid_to = '0000-00-00 00:00:00' or p.date_valid_to >= '". date('Y-m-d H:i:59') ."')
      ". (isset($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : false) ."
      and (p.id
        ". (isset($filter['product_name']) ? "$sql_andor pi.name = '". $system->database->input($filter['product_name']) ."'" : false) ."
        ". (isset($filter['sql_where']) ? "$sql_andor (". $filter['sql_where'] .")" : false) ."
        ". (isset($filter['category_id']) ? "$sql_andor find_in_set('". (int)$filter['category_id'] ."', p.categories)" : false) ."
        ". (!empty($filter['categories']) ? "$sql_andor (find_in_set('". implode("', p.categories) or find_in_set('", $filter['categories']) ."', p.categories))" : false) ."
        ". (isset($filter['designer_id']) ? "$sql_andor p.designer_id = '". (int)$filter['designer_id'] ."'" : false) ."
        ". (!empty($filter['designers']) ? "$sql_andor p.designer_id in ('". implode("', '", $system->database->input($filter['designers'])) ."')" : false) ."
        ". (!empty($filter['campaign']) ? "$sql_andor campaign_price" : false) ."
        ". (!empty($filter['keywords']) ? "$sql_andor (find_in_set('". implode("', p.keywords) or find_in_set('", $filter['keywords']) ."', p.keywords))" : false) ."
        ". (isset($filter['manufacturer_id']) ? "$sql_andor p.manufacturer_id = '". (int)$filter['manufacturer_id'] ."'" : false) ."
        ". (!empty($filter['manufacturers']) ? "$sql_andor p.manufacturer_id in ('". implode("', '", $system->database->input($filter['manufacturers'])) ."')" : false) ."
        ". (isset($filter['product_group_id']) ? "$sql_andor find_in_set('". (int)$filter['product_group_id'] ."', p.product_groups)" : false) ."
        ". (isset($filter['products']) ? "$sql_andor p.id in ('". implode("', '", $filter['products']) ."')" : false) ."
        ". (!empty($sql_where_product_groups) ? $sql_where_product_groups : false) ."
        ". (!empty($sql_where_prices) ? $sql_where_prices : false) ."
        ". (!empty($filter['purchased']) ? "$sql_andor purchases" : false) ."
      )
      order by $sql_sort
      ". (!empty($filter['limit']) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : false) ."". (int)$filter['limit'] : false) .";"
    ;
    
    if (!empty($_GET['debug'])) die($query);
    $products_query = $system->database->query($query);
    
    return $products_query;
  }
  
  function catalog_stock_adjust($product_id, $option_stock_combination, $quantity) {
    global $system;
    
    $products_options_stock_query = $system->database->query(
      "select id from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
      where product_id = '". (int)$product_id ."'
      and combination = '". $system->database->input($option_stock_combination) ."';"
    );
    if ($system->database->num_rows($products_options_stock_query) > 0) {
      if (empty($option_stock_combination)) {
        trigger_error('Invalid option stock combination ('. $option_stock_combination .') for product id '. $product_id, E_USER_ERROR);
      } else {
        $system->database->query(
          "update ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
          set quantity = quantity + ". (int)$quantity ."
          where product_id = '". (int)$product_id ."'
          and combination =  '". $system->database->input($option_stock_combination) ."'
          limit 1;"
        );
      }
    } else {
      $option_id = 0;
    }
    
    $system->database->query(
      "update ". DB_TABLE_PRODUCTS ."
      set quantity = quantity + ". (int)$quantity ."
      where id = '". (int)$product_id ."'
      limit 1;"
    );
  }
  
  function catalog_purchase_count_adjust($product_id, $quantity) {
    global $system;
    
    $products_options_query = $system->database->query(
      "update ". DB_TABLE_PRODUCTS ."
      set purchases = purchases + ". (int)$quantity ."
      where id = '". (int)$product_id ."'
      limit 1;"
    );
  }

?>