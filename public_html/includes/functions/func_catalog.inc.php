<?php

  function catalog_category_trail($category_id=0, $language_code='') {

    if (empty($language_code)) $language_code = language::$selected['code'];

    $trail = array();

    if (empty($category_id)) $category_id = 0;

    $categories_query = database::query(
      "select c.id, c.parent_id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". database::input($language_code) ."')
      where c.id = ". (int)$category_id ."
      limit 1;"
    );

    if (!$category = database::fetch($categories_query)) array();

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
      where c.parent_id = ". (int)$category_id .";"
    );

    while ($category = database::fetch($categories_query)) {
      $subcategories[$category['id']] = $category['name'];
      $subcategories = $subcategories + catalog_category_descendants($category['id'], $language_code);
    }

    return $subcategories;
  }

  function catalog_categories_query($parent_id=0) {

    $categories_query = database::query(
      "select c.id, c.parent_id, c.image, ci.name, ci.short_description, c.priority, c.date_updated from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.status
      and c.parent_id = ". (int)$parent_id ."
      order by c.priority asc, ci.name asc;"
    );

    return $categories_query;
  }

// Filter function using AND syntax
  function catalog_products_query($filter=array()) {

    if (!is_array($filter)) trigger_error('Invalid array filter for products query', E_USER_ERROR);

    if (!empty($filter['categories'])) $filter['categories'] = array_filter($filter['categories']);
    if (!empty($filter['manufacturers'])) $filter['manufacturers'] = array_filter($filter['manufacturers']);
    if (!empty($filter['attributes'])) $filter['attributes'] = array_filter($filter['attributes']);
    if (!empty($filter['products'])) $filter['products'] = array_filter($filter['products']);
    if (!empty($filter['exclude_products'])) $filter['exclude_products'] = array_filter($filter['exclude_products']);

    if (empty($filter['sort'])) $filter['sort'] = 'popularity';

    $sql_inner_sort = array();
    $sql_outer_sort = array();

    if (!empty($filter['campaigns_first'])) {
      $sql_outer_sort[] = "if(pc.campaign_price, 0, 1)";
    }

    switch ($filter['sort']) {

      case 'name':
        $sql_outer_sort[] = "name asc";
        break;

      case 'price':
        $sql_outer_sort[] = "final_price asc";
        break;

      case 'date':
        $sql_inner_sort[] = "p.date_created desc";
        $sql_outer_sort[] = "p.date_created desc";
        break;

      case 'popularity':
        $sql_inner_sort[] = "(p.purchases / (datediff(now(), p.date_created)/7)) desc, (p.views / (datediff(now(), p.date_created)/7)) desc";
        $sql_outer_sort[] = "(p.purchases / (datediff(now(), p.date_created)/7)) desc, (p.views / (datediff(now(), p.date_created)/7)) desc";
        break;

      case 'products':
        if (empty($filter['products'])) break;
        $sql_inner_sort[] = "Field(p.id, '". implode("', '", $filter['products']) ."')";
        $sql_outer_sort[] = "Field(p.id, '". implode("', '", $filter['products']) ."')";
        break;

      case 'random':
        $sql_outer_sort[] = "rand()";
        break;

      default:
        trigger_error('Invalid sort method ('. $filter['sort'] .')', E_USER_WARNING);
        break;
    }

    $sql_where_categories = '';
    if (!empty($filter['categories'])) {
      $sql_where_categories = (
        "and p.id in (
          select product_id from ". DB_TABLE_PRODUCTS_TO_CATEGORIES ."
          where category_id in ('". implode("', '", database::input($filter['categories'])) ."')
        )"
      );
    }

    $sql_where_attributes = array();
    if (!empty($filter['attributes']) && is_array($filter['attributes'])) {
      foreach ($filter['attributes'] as $group => $values) {
        if (empty($values) || !is_array($values)) continue;
        foreach ($values as $value) {
          $sql_where_attributes[$group][] = "find_in_set('". database::input($group.'-'.$value) ."', pa.attributes)";
        }
        $sql_where_attributes[$group] = "(". implode(" or ", $sql_where_attributes[$group]) .")";
      }
      $sql_where_attributes = "and (". implode(" and ", $sql_where_attributes) .")";
    }

    $sql_where_prices = array();
    if (!empty($filter['price_ranges']) && is_array($filter['price_ranges'])) {
      foreach ($filter['price_ranges'] as $price_range) {
        list($min,$max) = explode('-', $price_range);
        $sql_where_prices[] = "(if(pc.campaign_price, pc.campaign_price, pp.price) >= ". (float)$min ." and if(pc.campaign_price, pc.campaign_price, pp.price) <= ". (float)$max .")";
      }
      $sql_where_prices = "and (". implode(" or ", $sql_where_prices) .")";
    }

    $query = (
      "select p.*, pi.name, pi.short_description, m.id as manufacturer_id, m.name as manufacturer_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price) as final_price

      from (
        select p.id, p.sold_out_status_id, p.code, p.sku, p.mpn, p.gtin, p.manufacturer_id, pa.attributes, p.keywords, p.image, p.tax_class_id, p.quantity, p.views, p.purchases, p.date_created

        from ". DB_TABLE_PRODUCTS ." p

        left join (
          select product_id, group_concat(concat(group_id, '-', if(custom_value != '', custom_value, value_id)) separator ',') as attributes
          from ". DB_TABLE_PRODUCTS_ATTRIBUTES ."
          group by product_id
        ) pa on (p.id = pa.product_id)

        left join ". DB_TABLE_SOLD_OUT_STATUSES ." ss on (p.sold_out_status_id = ss.id)

        where p.status
        ". (!empty($filter['products']) ? "and p.id in ('". implode("', '", database::input($filter['products'])) ."')" : null) ."
        ". (!empty($sql_where_categories) ? $sql_where_categories : null) ."
        ". (!empty($sql_where_attributes) ? $sql_where_attributes : null) ."
        ". (!empty($filter['manufacturers']) ? "and p.manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."')" : null) ."
        ". (!empty($filter['keywords']) ? "and (find_in_set('". implode("', p.keywords) or find_in_set('", database::input($filter['keywords'])) ."', p.keywords))" : null) ."
        and (p.quantity > 0 or ss.hidden != 1)
        and (p.date_valid_from <= '". date('Y-m-d H:i:s') ."')
        and (year(p.date_valid_to) < '1971' or p.date_valid_to >= '". date('Y-m-d H:i:s') ."')
        ". (!empty($filter['purchased']) ? "and p.purchases" : null) ."
        ". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : null) ."

        ". ((!empty($sql_inner_sort) && !empty($filter['limit'])) ? "order by " . implode(",", $sql_inner_sort) : null) ."
        ". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : null) ."". (int)$filter['limit'] : "") ."
      ) p

      left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". language::$selected['code'] ."')

      left join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)

      left join (
        select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
        from ". DB_TABLE_PRODUCTS_PRICES ."
      ) pp on (pp.product_id = p.id)

      left join (
        select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as campaign_price
        from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where (start_date <= '". date('Y-m-d H:i:s') ."')
        and (year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
        order by end_date asc
      ) pc on (pc.product_id = p.id)

      where (p.id
        ". (!empty($filter['sql_where']) ? "and (". $filter['sql_where'] .")" : null) ."
        ". (!empty($filter['product_name']) ? "and pi.name like '%". database::input($filter['product_name']) ."%'" : null) ."
        ". (!empty($filter['campaign']) ? "and campaign_price > 0" : null) ."
        ". (!empty($sql_where_prices) ? $sql_where_prices : null) ."
      )

      ". (!empty($sql_outer_sort) ? "order by ". implode(",", $sql_outer_sort) : "") ."
      ". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : null) ."". (int)$filter['limit'] : null) .";"
    );

    $products_query = database::query($query);

    return $products_query;
  }

// Search function using OR syntax
  function catalog_products_search_query($filter=array()) {

    if (!is_array($filter)) trigger_error('Invalid array filter for products query', E_USER_ERROR);

    if (!empty($filter['categories'])) $filter['categories'] = array_filter($filter['categories']);
    if (!empty($filter['manufacturers'])) $filter['manufacturers'] = array_filter($filter['manufacturers']);
    if (!empty($filter['products'])) $filter['products'] = array_filter($filter['products']);
    if (!empty($filter['exclude_products'])) $filter['exclude_products'] = array_filter($filter['exclude_products']);

    $sql_where_prices = "";
    if (!empty($filter['price_ranges'])) {

      foreach ($filter['price_ranges'] as $price_range) {
        list($min,$max) = explode('-', $price_range);
        $sql_where_prices .= " or (if(pc.campaign_price, pc.campaign_price, pp.price) >= ". (float)$min ." and if(pc.campaign_price, pc.campaign_price, pp.price) <= ". (float)$max .")";
      }

      $sql_where_prices = "or (". ltrim($sql_where_prices, " or ") .")";
    }

    $query = (
      "select p.*, pi.name, pi.short_description, m.id as manufacturer_id, m.name as manufacturer_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price) as final_price, (0
        ". (!empty($filter['product_name']) ? "+ if(pi.name like '%". database::input($filter['product_name']) ."%', 1, 0)" : false) ."
        ". (!empty($filter['sql_where']) ? "+ if(". $filter['sql_where'] .", 1, 0)" : false) ."
        ". (!empty($filter['categories']) ? "+ if(find_in_set('". implode("', categories), 1, 0) + if(find_in_set('", database::input($filter['categories'])) ."', categories), 1, 0)" : false) ."
        ". (!empty($filter['manufacturers']) ? "+ if(p.manufacturer_id and p.manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."'), 1, 0)" : false) ."
        ". (!empty($filter['keywords']) ? "+ if(find_in_set('". implode("', p.keywords), 1, 0) + if(find_in_set('", database::input($filter['keywords'])) ."', p.keywords), 1, 0)" : false) ."
        ". (!empty($filter['products']) ? "+ if(p.id in ('". implode("', '", database::input($filter['products'])) ."'), 1, 0)" : false) ."
      ) as occurrences

      from (
        select p.id, p.sold_out_status_id, p.code, p.sku, p.mpn, p.gtin, p.manufacturer_id, group_concat(ptc.category_id separator ',') as categories, p.keywords, p.image, p.tax_class_id, p.quantity, p.views, p.purchases, p.date_created
        from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_PRODUCTS_TO_CATEGORIES ." ptc on (p.id = ptc.product_id)
        left join ". DB_TABLE_SOLD_OUT_STATUSES ." ss on (p.sold_out_status_id = ss.id)
        where p.status
          and (p.id
          ". (!empty($filter['products']) ? "or p.id in ('". implode("', '", database::input($filter['products'])) ."')" : null) ."
          ". (!empty($filter['categories']) ? "or ptc.category_id in (". implode(",", database::input($filter['categories'])) .")" : null) ."
          ". (!empty($filter['manufacturers']) ? "or manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."')" : null) ."
          ". (!empty($filter['keywords']) ? "or (find_in_set('". implode("', p.keywords) or find_in_set('", database::input($filter['keywords'])) ."', p.keywords))" : null) ."
        )
        and (p.quantity > 0 or ss.hidden != 1)
        and (p.date_valid_from <= '". date('Y-m-d H:i:s') ."')
        and (year(p.date_valid_to) < '1971' or p.date_valid_to >= '". date('Y-m-d H:i:s') ."')
        ". (!empty($filter['purchased']) ? "and p.purchases" : null) ."
        ". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : null) ."
        group by ptc.product_id
        ". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : null) ."". (int)$filter['limit'] : "") ."
      ) p

      left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". language::$selected['code'] ."')

      left join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)

      left join (
        select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
        from ". DB_TABLE_PRODUCTS_PRICES ."
      ) pp on (pp.product_id = p.id)

      left join (
        select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as campaign_price
        from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where start_date <= '". date('Y-m-d H:i:s') ."'
        and (year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
        order by end_date asc
      ) pc on (pc.product_id = p.id)

      where (p.id
        ". (!empty($filter['sql_where']) ? "or (". $filter['sql_where'] .")" : null) ."
        ". (!empty($filter['product_name']) ? "or pi.name like '%". database::input($filter['product_name']) ."%'" : null) ."
        ". (!empty($filter['campaign']) ? "or campaign_price > 0" : null) ."
        ". (!empty($sql_where_prices) ? $sql_where_prices : null) ."
      )

      order by occurrences desc
      ". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : null) ."". (int)$filter['limit'] : null) .";"
    );

    $products_query = database::query($query);

    return $products_query;
  }

  function catalog_stock_adjust($product_id, $option_stock_combination, $quantity) {

    if (empty($product_id)) return;

    if (!empty($option_stock_combination)) {
      $products_options_stock_query = database::query(
        "select id from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
        where product_id = ". (int)$product_id ."
        and combination = '". database::input($option_stock_combination) ."';"
      );

      if (database::num_rows($products_options_stock_query) > 0) {

        if (empty($option_stock_combination)) {
          trigger_error('Invalid option stock combination ('. $option_stock_combination .') for product id '. $product_id, E_USER_ERROR);

        } else {
          database::query(
            "update ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
            set quantity = quantity + ". (float)$quantity ."
            where product_id = ". (int)$product_id ."
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
      where id = ". (int)$product_id ."
      limit 1;"
    );
  }

  function catalog_purchase_count_adjust($product_id, $quantity) {

    database::query(
      "update ". DB_TABLE_PRODUCTS ."
      set purchases = purchases + ". (int)$quantity ."
      where id = ". (int)$product_id ."
      limit 1;"
    );
  }
