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

  function catalog_categories_query($parent_id=0) {

    $categories_query = database::query(
      "select c.id, c.image, ci.name, ci.short_description from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.status
      and c.parent_id = '". (int)$parent_id ."'
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

    if (empty($filter['sort'])) $filter['sort'] = 'popularity';

    switch ($filter['sort']) {
      case 'name':
        $sql_local_sort = "";
        $sql_global_sort = "order by name asc";
        break;
      case 'price':
        $sql_local_sort = "";
        $sql_global_sort = "order by final_price asc";
        break;
      case 'date':
        $sql_local_sort = "order by date_created desc";
        $sql_global_sort = "order by p.date_created desc";
        break;
      case 'occurrences':
        $sql_local_sort = "";
        $sql_global_sort = "order by occurrences desc";
        break;
      case 'rand':
        $sql_local_sort = "";
        $sql_global_sort = "order by rand()";
        break;
      case 'popularity':
      default:
        //$sql_local_sort = "order by (p.purchases / p.views) desc";
        //$sql_local_sort = "order by (views / ((unix_timestamp() - unix_timestamp(p.date_created)) / 86400)) desc, p.date_created desc";
        $sql_local_sort = "";
        $sql_global_sort = "order by (views / timestampdiff(day, now(), from_unixtime(p.date_created))) desc";
        break;
    }

    if (!empty($filter['exclude_products']) && !is_array($filter['exclude_products'])) $filter['exclude_products'] = array($filter['exclude_products']);

    $filter = database::input($filter);

    $sql_select_occurrences = "";
    $sql_andor = "and";

  // Define match points
    if ($filter['sort'] == 'occurrences') {
      $sql_select_occurrences = "(0
        ". (isset($filter['product_name']) ? "+ if(pi.name like '%". database::input($filter['product_name']) ."%', 1, 0)" : false) ."
        ". (isset($filter['sql_where']) ? "+ if(". $filter['sql_where'] .", 1, 0)" : false) ."
        ". (!empty($filter['keywords']) ? "+ if(find_in_set('". implode("', p.keywords), 1, 0) + if(find_in_set('", $filter['keywords']) ."', p.keywords), 1, 0)" : false) ."
        ". (!empty($filter['manufacturers']) ? "+ if(p.manufacturer_id and p.manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."'), 1, 0)" : false) ."
        ". (!empty($filter['product_groups']) ? "+ if(find_in_set('". implode("', p.product_groups), 1, 0) + if(find_in_set('", $filter['product_groups']) ."', p.product_groups), 1, 0)" : false) ."
        ". (isset($filter['products']) ? "+ if(p.id in ('". implode("', '", database::input($filter['products'])) ."'), 1, 0)" : false) ."
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
      $sql_campaign_price_column = "if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`)";
  
   if(count($filter['categories']) == 0 || array_values($filter['categories'])[0] == ""){  
      $query = "
        select p.*, pi.name, pi.short_description, m.name as manufacturer_name, ". $sql_price_column ." as price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, ". $sql_price_column .")) as final_price". (($filter['sort'] == 'occurrences') ? ", " . $sql_select_occurrences : false) ." from (
          select id, manufacturer_id, default_category_id, keywords, product_groups, image, tax_class_id, quantity, views, purchases, date_created from ". DB_TABLE_PRODUCTS ."
          where status
            and (id
            ". (isset($filter['products']) ? "$sql_andor id in ('". implode("', '", $filter['products']) ."')" : false) ."
            ". (!empty($filter['manufacturers']) ? "$sql_andor manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."')" : false) ."
            ". (!empty($filter['keywords']) ? "$sql_andor (find_in_set('". implode("', keywords) or find_in_set('", $filter['keywords']) ."', keywords))" : false) ."
            ". (!empty($sql_where_product_groups) ? $sql_where_product_groups : false) ."
            ". (!empty($filter['purchased']) ? "$sql_andor purchases" : false) ."
          )
          and (date_valid_from <= '". date('Y-m-d H:i:s') ."')
          and (year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."')
          ". (isset($filter['exclude_products']) ? "and id not in ('". implode("', '", $filter['exclude_products']) ."')" : false) ."
          ". $sql_local_sort ."
          ". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : false) ."". (int)$filter['limit'] : "") ."
        ) p
        left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". language::$selected['code'] ."')
        left join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)
        left join ". DB_TABLE_PRODUCTS_PRICES ." pp on (pp.product_id = p.id)
        left join (
          select product_id, ". $sql_campaign_price_column ." as campaign_price
          from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
          where (start_date <= '". date('Y-m-d H:i:s') ."')
          and (year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
          order by end_date asc
        ) pc on (pc.product_id = p.id)
        where (p.id
          ". (isset($filter['sql_where']) ? "$sql_andor (". $filter['sql_where'] .")" : false) ."
          ". (isset($filter['product_name']) ? "$sql_andor pi.name like '%". database::input($filter['product_name']) ."%'" : false) ."
          ". (!empty($filter['campaign']) ? "$sql_andor campaign_price > 0" : false) ."
          ". (!empty($sql_where_prices) ? $sql_where_prices : false) ."
        )
        ". $sql_global_sort ."
        ". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : false) ."". (int)$filter['limit'] : false) .";
      ";
    }else {
        $sql_price_column = "if(pp.`". database::input(currency::$selected['code']) ."`, pp.`". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .", pp.`". database::input(settings::get('store_currency_code')) ."`)";
        $sql_campaign_price_column = "if(pp.`". database::input(currency::$selected['code']) ."`, pcg.`". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .",pp.`". database::input(settings::get('store_currency_code')) ."`)";
        $sql_final_price_column = "if(pcg.id IS NULL,
        if(pp.`". database::input(currency::$selected['code']) ."`, pp.`". database::input(currency::$selected['code']) ."` /". (float)currency::$selected['value'] .", pp.`".database::input(settings::get('store_currency_code'))."`),
        if(pp.`". database::input(currency::$selected['code']) ."`, pcg.`". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .", pp.`".database::input(settings::get('store_currency_code'))."`) )";

        $query = "select p.*, pi.name, pi.short_description, m.name as manufacturer_name, ".$sql_price_column ." as price, " . $sql_campaign_price_column." as campaign_price, "
        .$sql_final_price_column." as final_price";
        $query .=  (($filter['sort'] == 'occurrences') ? ", " . $sql_select_occurrences : false);

        $query .= "
        from ". DB_TABLE_PRODUCTS_TO_CATEGORIES ." pc
        left join ". DB_TABLE_PRODUCTS ." p on (pc.product_id = p.id)
        left join ". DB_TABLE_PRODUCTS_INFO ."  pi on (pi.product_id = p.id and pi.language_code = 'en')
        left join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)
        left join ". DB_TABLE_PRODUCTS_PRICES ." pp on (pp.product_id = p.id)
        left join ". DB_TABLE_PRODUCTS_CAMPAIGNS ." pcg on(pcg.product_id = p.id) and ((pcg.start_date <= NOW() and pcg.end_date >= NOW()) or (UNIX_TIMESTAMP(pcg.start_date) = 0 and UNIX_TIMESTAMP(pcg.end_date) = 0))";

        $query .= " where (p.id
          ". (isset($filter['sql_where']) ? "$sql_andor (". $filter['sql_where'] .")" : false) ."
          ". (isset($filter['product_name']) ? "$sql_andor pi.name like '%". database::input($filter['product_name']) ."%'" : false) ."
          ". (!empty($filter['campaign']) ? "$sql_andor campaign_price > 0" : false) ."
          ". (!empty($sql_where_prices) ? $sql_where_prices : false) ."
        ) and pc.category_id = ". array_values($filter['categories'])[0]."
        ". $sql_global_sort ."
        ". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : false) ."". (int)$filter['limit'] : false) .";
        ";
    }

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