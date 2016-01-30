<?php

  function catalog_category_trail($category_id=0, $language_code='') {
    
    if (empty($language_code)) $language_code = language::$selected['code'];
    
    $trail = array();

    if (empty($category_id)) $category_id = 0;
    
    $categories_query = database::query(
      "select c.id, c.parent_id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". database::input($language_code) ."')
      where c.id = '". (int)$category_id ."'
      limit 1;"
    );
    $category = database::fetch($categories_query);
    
    if (!empty($category['parent_id'])) {
      $trail = functions::catalog_category_trail($category['parent_id']);
      $trail[$category['id']] = $category['name'];
    } else if (isset($category['id'])) {
      $trail = array($category['id'] => $category['name']);
    }
    
    return $trail;
  }
  
  function catalog_category_descendants($category_id=0, $language_code='') {
    
    if (empty($language_code)) $language_code = language::$selected['code'];
    
    $subcategories = array();
    
    if (empty($category_id)) $category_id = 0;
    
    $categories_query = database::query(
      "select c.id, c.parent_id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". database::input($language_code) ."')
      where c.parent_id = '". (int)$category_id ."';"
    );
    while ($category = database::fetch($categories_query)) {
      $subcategories[$category['id']] = $category['name'];
      $subcategories = $subcategories + catalog_category_descendants($category['id'], $language_code);
    }
    
    return $subcategories;
  }
  
  function catalog_categories_query($parent_id=0, $dock=null) {
    
    $categories_query = database::query(
      "select c.id, c.image, ci.name, ci.short_description, c.date_updated from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.status
      and c.parent_id = '". (int)$parent_id ."'
      ". (!empty($dock) ? "and find_in_set('". database::input($dock) ."', c.dock)" : null) ."
      order by c.priority asc, ci.name asc;"
    );
    
    return $categories_query;
  }
  
  function catalog_products_query($filter=array()) {
    
    if (!is_array($filter)) trigger_error('Invalid array filter for products query', E_USER_ERROR);
    
    if (empty($filter['categories'])) $filter['categories'] = array();
    if (empty($filter['manufacturers'])) $filter['manufacturers'] = array();
    if (empty($filter['product_groups'])) $filter['product_groups'] = array();
    
    if (!empty($filter['category_id'])) $filter['categories'][] = $filter['category_id'];
    if (!empty($filter['manufacturer_id'])) $filter['manufacturers'][] = $filter['manufacturer_id'];
    if (!empty($filter['product_group_id'])) $filter['product_groups'][] = $filter['product_group_id'];
    
    $filter['categories'] = array_filter($filter['categories']);
    $filter['manufacturers'] = array_filter($filter['manufacturers']);
    $filter['product_groups'] = array_filter($filter['product_groups']);
    
    if (empty($filter['sort'])) $filter['sort'] = 'popularity';
    
    switch ($filter['sort']) {
      case 'name':
        $sql_inner_sort = "";
        $sql_outer_sort = "order by name asc";
        break;
      case 'price':
        $sql_inner_sort = "";
        $sql_outer_sort = "order by final_price asc";
        break;
      case 'date':
        $sql_inner_sort = "order by p.date_created desc";
        $sql_outer_sort = "";
        break;
      case 'occurrences':
        $sql_inner_sort = "";
        $sql_outer_sort = "order by occurrences desc";
        break;
      case 'rand':
        $sql_inner_sort = "";
        $sql_outer_sort = "order by rand()";
        break;
      case 'popularity':
      default:
        $sql_inner_sort = "order by (p.purchases / (datediff(now(), p.date_created)/7)) desc, (p.views / (datediff(now(), p.date_created)/7)) desc";
        $sql_outer_sort = "order by (p.purchases / (datediff(now(), p.date_created)/7)) desc, (p.views / (datediff(now(), p.date_created)/7)) desc";
        break;
    }
    
    if (!empty($filter['exclude_products']) && !is_array($filter['exclude_products'])) $filter['exclude_products'] = array($filter['exclude_products']);
    
    $sql_andor = "and";
    
  // Define match points
    if ($filter['sort'] == 'occurrences') {
      $sql_select_occurrences = "(0
        ". (!empty($filter['product_name']) ? "+ if(pi.name like '%". database::input($filter['product_name']) ."%', 1, 0)" : false) ."
        ". (!empty($filter['sql_where']) ? "+ if(". $filter['sql_where'] .", 1, 0)" : false) ."
        ". (!empty($filter['categories']) ? "+ if(find_in_set('". implode("', categories), 1, 0) + if(find_in_set('", database::input($filter['categories'])) ."', categories), 1, 0)" : false) ."
        ". (!empty($filter['keywords']) ? "+ if(find_in_set('". implode("', p.keywords), 1, 0) + if(find_in_set('", database::input($filter['keywords'])) ."', p.keywords), 1, 0)" : false) ."
        ". (!empty($filter['manufacturers']) ? "+ if(p.manufacturer_id and p.manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."'), 1, 0)" : false) ."
        ". (!empty($filter['product_groups']) ? "+ if(find_in_set('". implode("', p.product_groups), 1, 0) + if(find_in_set('", database::input($filter['product_groups'])) ."', p.product_groups), 1, 0)" : false) ."
        ". (!empty($filter['products']) ? "+ if(p.id in ('". implode("', '", database::input($filter['products'])) ."'), 1, 0)" : false) ."
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
        $sql_where_product_groups .= "$sql_andor (find_in_set('". implode("', product_groups) or find_in_set('", $group_value) ."', product_groups))";
      }
    }
    
    $sql_where_prices = "";
    if (!empty($filter['price_ranges'])) {
      foreach ($filter['price_ranges'] as $price_range) {
        list($min,$max) = explode('-', $price_range);
        $sql_where_prices .= " or (if(pc.campaign_price, pc.campaign_price, pp.price) >= ". (float)$min ." and if(pc.campaign_price, pc.campaign_price, pp.price) <= ". (float)$max .")";
      }
      $sql_where_prices = "$sql_andor (". ltrim($sql_where_prices, " or ") .")";
    }
    
    $sql_price_column = "if(pp.`". database::input(currency::$selected['code']) ."`, pp.`". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .", pp.`". database::input(settings::get('store_currency_code')) ."`)";
    
    $query = "
      select p.*, pi.name, pi.short_description, m.name as manufacturer_name, ". $sql_price_column ." as price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, ". $sql_price_column .")) as final_price". (($filter['sort'] == 'occurrences') ? ", " . $sql_select_occurrences : false) ." from (
        select p.id, p.code, p.manufacturer_id, group_concat(ptc.category_id separator ',') as categories, p.keywords, p.product_groups, p.image, p.tax_class_id, p.quantity, p.views, p.purchases, p.date_created
        from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_PRODUCTS_TO_CATEGORIES ." ptc on (p.id = ptc.product_id)
        where p.status
          and (id
          ". (!empty($filter['products']) ? "$sql_andor p.id in ('". implode("', '", database::input($filter['products'])) ."')" : false) ."
          ". (!empty($filter['categories']) ? "$sql_andor ptc.category_id in (". implode(",", database::input($filter['categories'])) .")" : false) ."
          ". (!empty($filter['manufacturers']) ? "$sql_andor manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."')" : false) ."
          ". (!empty($filter['keywords']) ? "$sql_andor (find_in_set('". implode("', p.keywords) or find_in_set('", database::input($filter['keywords'])) ."', p.keywords))" : false) ."
          ". (!empty($sql_where_product_groups) ? $sql_where_product_groups : false) ."
          ". (!empty($filter['purchased']) ? "$sql_andor p.purchases" : false) ."
        )
        and (p.date_valid_from <= '". date('Y-m-d H:i:s') ."')
        and (year(p.date_valid_to) < '1971' or p.date_valid_to >= '". date('Y-m-d H:i:s') ."')
        ". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : false) ."
        group by ptc.product_id
        ". ((!empty($sql_inner_sort) && !empty($filter['limit'])) ? $sql_inner_sort : '') ."
        ". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : false) ."". (int)$filter['limit'] : "") ."
      ) p
      left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". language::$selected['code'] ."')
      left join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)
      left join ". DB_TABLE_PRODUCTS_PRICES ." pp on (pp.product_id = p.id)
      left join (
        select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as campaign_price
        from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where (start_date <= '". date('Y-m-d H:i:s') ."')
        and (year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
        order by end_date asc
      ) pc on (pc.product_id = p.id)
      where (p.id
        ". (!empty($filter['sql_where']) ? "$sql_andor (". $filter['sql_where'] .")" : false) ."
        ". (!empty($filter['product_name']) ? "$sql_andor pi.name like '%". database::input($filter['product_name']) ."%'" : false) ."
        ". (!empty($filter['campaign']) ? "$sql_andor campaign_price > 0" : false) ."
        ". (!empty($sql_where_prices) ? $sql_where_prices : false) ."
      )
      ". $sql_outer_sort ."
      ". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : false) ."". (int)$filter['limit'] : false) .";
    ";
    
    $products_query = database::query($query);
    
    return $products_query;
  }
  
  function catalog_stock_adjust($product_id, $option_stock_combination, $quantity) {
    
    if (empty($product_id)) return;
    
    if (!empty($option_stock_combination)) {
      $products_options_stock_query = database::query(
        "select id from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
        where product_id = '". (int)$product_id ."'
        and combination = '". database::input($option_stock_combination) ."';"
      );
      if (database::num_rows($products_options_stock_query) > 0) {
        if (empty($option_stock_combination)) {
          trigger_error('Invalid option stock combination ('. $option_stock_combination .') for product id '. $product_id, E_USER_ERROR);
        } else {
          database::query(
            "update ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
            set quantity = quantity + ". (int)$quantity ."
            where product_id = '". (int)$product_id ."'
            and combination =  '". database::input($option_stock_combination) ."'
            limit 1;"
          );
        }
      } else {
        $option_id = 0;
      }
    }
    
    database::query(
      "update ". DB_TABLE_PRODUCTS ."
      set quantity = quantity + ". (int)$quantity ."
      where id = '". (int)$product_id ."'
      limit 1;"
    );
  }
  
  function catalog_purchase_count_adjust($product_id, $quantity) {

    $products_options_query = database::query(
      "update ". DB_TABLE_PRODUCTS ."
      set purchases = purchases + ". (int)$quantity ."
      where id = '". (int)$product_id ."'
      limit 1;"
    );
  }
  
?>