<?php

  function catalog_category_trail($category_id=0, $language_code='') {

    trigger_error('catalog_category_trail() is deprecated. Use reference::category(id)->path', E_USER_DEPRECATED);

    if (empty($language_code)) $language_code = language::$selected['code'];

    $trail = [];

    foreach (reference::category($category_id, $language_code)->path as $category) {
      $trail[$category->id] = $category->name;
    }

    return $trail;
  }

  function catalog_category_descendants($category_id=0, $language_code='') {

    trigger_error('catalog_category_descendants() is deprecated. Use reference::category(id)->descendants', E_USER_DEPRECATED);

    $descendants = [];

    foreach (reference::category($category_id, $language_code)->path as $category) {
      $descendants[$category->id] = $category->name;
    }

    return $descendants;
  }

  function catalog_categories_query($parent_id=0) {

    $categories_query = database::query(
      "select c.id, c.parent_id, c.image, ci.name, ci.short_description, c.priority, c.date_updated from ". DB_TABLE_PREFIX ."categories c
      left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.status
      and c.parent_id = ". (int)$parent_id ."
      order by c.priority asc, ci.name asc;"
    );

    return $categories_query;
  }

// Filter function using AND syntax
  function catalog_products_query($filter=[]) {

    if (!is_array($filter)) trigger_error('Invalid array filter for products query', E_USER_ERROR);

    if (!empty($filter['categories'])) $filter['categories'] = array_filter($filter['categories']);
    if (!empty($filter['manufacturers'])) $filter['manufacturers'] = array_filter($filter['manufacturers']);
    if (!empty($filter['attributes'])) $filter['attributes'] = array_filter($filter['attributes']);
    if (!empty($filter['products'])) $filter['products'] = array_filter($filter['products']);
    if (!empty($filter['exclude_products'])) $filter['exclude_products'] = array_filter($filter['exclude_products']);

    if (empty($filter['sort'])) $filter['sort'] = 'popularity';

    $sql_inner_sort = [];
    $sql_outer_sort = [];

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
        $sql_inner_sort[] = "(p.purchases / ceil(datediff(now(), p.date_created)/7)) desc, (p.views / ceil(datediff(now(), p.date_created)/7)) desc";
        $sql_outer_sort[] = "(p.purchases / ceil(datediff(now(), p.date_created)/7)) desc, (p.views / ceil(datediff(now(), p.date_created)/7)) desc";
        break;

      case 'products':
        if (empty($filter['products'])) break;
        $sql_inner_sort[] = "Field(p.id, '". implode("', '", $filter['products']) ."')";
        $sql_outer_sort[] = "Field(p.id, '". implode("', '", $filter['products']) ."')";
        break;

      case 'random':
        $sql_outer_sort[] = "rand()";
        break;
    }

    $sql_where_categories = '';
    if (!empty($filter['categories'])) {
      $sql_where_categories = (
        "and p.id in (
          select product_id from ". DB_TABLE_PREFIX ."products_to_categories
          where category_id in ('". implode("', '", database::input($filter['categories'])) ."')
        )"
      );
    }

    $sql_where_attributes = [];
    if (!empty($filter['attributes']) && is_array($filter['attributes'])) {
      foreach ($filter['attributes'] as $group_id => $values) {
        $sql_where_attributes[] =
          "and p.id in (
            select distinct product_id from ". DB_TABLE_PREFIX ."products_attributes
            where (group_id = ". (int)$group_id ." and (value_id in ('". implode("', '", database::input($values)) ."') or custom_value in ('". implode("', '", database::input($values)) ."')))
          )";
      }
      $sql_where_attributes = implode(PHP_EOL, $sql_where_attributes);
    }

    $sql_where_prices = [];
    if (!empty($filter['price_ranges']) && is_array($filter['price_ranges'])) {
      foreach ($filter['price_ranges'] as $price_range) {
        list($min,$max) = explode('-', $price_range);
        $sql_where_prices[] = "(if(pc.campaign_price, pc.campaign_price, pp.price) >= ". (float)$min ." and if(pc.campaign_price, pc.campaign_price, pp.price) <= ". (float)$max .")";
      }
      $sql_where_prices = "and (". implode(" or ", $sql_where_prices) .")";
    }

    $query = (
      "select p.*, pi.name, pi.short_description, m.name as manufacturer_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price) as final_price, pa.attributes

      from (
        select p.id, p.sold_out_status_id, p.code, p.sku, p.mpn, p.gtin, p.manufacturer_id, p.keywords, p.image, p.recommended_price, p.tax_class_id, p.quantity, p.views, p.purchases, p.date_created

        from ". DB_TABLE_PREFIX ."products p

        left join ". DB_TABLE_PREFIX ."sold_out_statuses ss on (p.sold_out_status_id = ss.id)

        where p.status
        ". (!empty($filter['products']) ? "and p.id in ('". implode("', '", database::input($filter['products'])) ."')" : "") ."
        ". (!empty($sql_where_categories) ? $sql_where_categories : "") ."
        ". (!empty($sql_where_attributes) ? $sql_where_attributes : "") ."
        ". (!empty($filter['manufacturers']) ? "and p.manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."')" : "") ."
        ". (!empty($filter['keywords']) ? "and (find_in_set('". implode("', p.keywords) or find_in_set('", database::input($filter['keywords'])) ."', p.keywords))" : "") ."
        and (p.quantity > 0 or ss.hidden != 1)
        and (p.date_valid_from is null or p.date_valid_from <= '". date('Y-m-d H:i:s') ."')
        and (p.date_valid_to is null or year(p.date_valid_to) < '1971' or p.date_valid_to >= '". date('Y-m-d H:i:s') ."')
        ". (!empty($filter['purchased']) ? "and p.purchases" : "") ."
        ". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : "") ."

        ". ((!empty($sql_inner_sort) && !empty($filter['limit'])) ? "order by " . implode(",", $sql_inner_sort) : "") ."
        ". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") ."
      ) p

      left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

      left join ". DB_TABLE_PREFIX ."manufacturers m on (m.id = p.manufacturer_id)

      left join (
        select product_id, group_concat(concat(group_id, '-', if(custom_value != '', custom_value, value_id)) separator ',') as attributes
        from ". DB_TABLE_PREFIX ."products_attributes
        group by product_id
        order by id
      ) pa on (p.id = pa.product_id)

      left join (
        select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
        from ". DB_TABLE_PREFIX ."products_prices
      ) pp on (pp.product_id = p.id)

      left join (
        select product_id, min(if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`)) as campaign_price
        from ". DB_TABLE_PREFIX ."products_campaigns
        where (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
        and (end_date is null or year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
        group by product_id
      ) pc on (pc.product_id = p.id)

      where (p.id
        ". (!empty($filter['sql_where']) ? "and (". $filter['sql_where'] .")" : "") ."
        ". (!empty($filter['product_name']) ? "and pi.name like '%". database::input($filter['product_name']) ."%'" : "") ."
        ". (!empty($filter['campaign']) ? "and campaign_price > 0" : "") ."
        ". (!empty($sql_where_prices) ? $sql_where_prices : "") ."
      )

      group by p.id

      ". (!empty($sql_outer_sort) ? "order by ". implode(",", $sql_outer_sort) : "") ."
      ". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") .";"
    );

    $products_query = database::query($query);

    return $products_query;
  }

// Search function using OR syntax
  function catalog_products_search_query($filter=[]) {

    if (!is_array($filter)) trigger_error('Invalid array filter for products query', E_USER_ERROR);

    if (!empty($filter['categories'])) $filter['categories'] = array_filter($filter['categories']);
    if (!empty($filter['manufacturers'])) $filter['manufacturers'] = array_filter($filter['manufacturers']);
    if (!empty($filter['products'])) $filter['products'] = array_filter($filter['products']);
    if (!empty($filter['exclude_products'])) $filter['exclude_products'] = array_filter($filter['exclude_products']);
    if (empty($filter['sort'])) $filter['sort'] = 'relevance';

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
        $sql_inner_sort[] = "(p.purchases / ceil(datediff(now(), p.date_created)/7)) desc, (p.views / ceil(datediff(now(), p.date_created)/7)) desc";
        $sql_outer_sort[] = "(p.purchases / ceil(datediff(now(), p.date_created)/7)) desc, (p.views / ceil(datediff(now(), p.date_created)/7)) desc";
        break;

      case 'products':
        if (empty($filter['products'])) break;
        $sql_inner_sort[] = "Field(p.id, '". implode("', '", $filter['products']) ."')";
        $sql_outer_sort[] = "Field(p.id, '". implode("', '", $filter['products']) ."')";
        break;

      case 'random':
        $sql_outer_sort[] = "rand()";
        break;

      case 'relevance':
      default:
        $sql_outer_sort[] = "relevance desc";
        break;
    }

    if (!empty($filter['query'])) {
      $code_regex = functions::format_regex_code($filter['query']);
    }

    $sql_where_prices = "";
    if (!empty($filter['price_ranges'])) {

      foreach ($filter['price_ranges'] as $price_range) {
        list($min,$max) = explode('-', $price_range);
        $sql_where_prices .= " or (if(pc.campaign_price, pc.campaign_price, pp.price) >= ". (float)$min ." and if(pc.campaign_price, pc.campaign_price, pp.price) <= ". (float)$max .")";
      }

      $sql_where_prices = "or (". ltrim($sql_where_prices, " or ") .")";
    }

    $query = (
      "select p.*, pi.name, pi.short_description, m.name as manufacturer_name, pp.price, pc.campaign_price, pc.end_date as campaign_end_date, if(pc.campaign_price, pc.campaign_price, pp.price) as final_price, pa.attributes, (0
        ". (!empty($filter['product_name']) ? "+ if(pi.name like '%". database::input($filter['product_name']) ."%', 1, 0)" : "") ."
        ". (!empty($filter['sql_where']) ? "+ if(". $filter['sql_where'] .", 1, 0)" : "") ."
        ". (!empty($filter['categories']) ? "+ if(find_in_set('". implode("', categories), 1, 0) + if(find_in_set('", database::input($filter['categories'])) ."', categories), 1, 0)" : "") ."
        ". (!empty($filter['manufacturers']) ? "+ if(p.manufacturer_id and p.manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."'), 1, 0)" : "") ."
        ". (!empty($filter['keywords']) ? "+ if(find_in_set('". implode("', p.keywords), 1, 0) + if(find_in_set('", database::input($filter['keywords'])) ."', p.keywords), 1, 0)" : "") ."
        ". (!empty($filter['products']) ? "+ if(p.id in ('". implode("', '", database::input($filter['products'])) ."'), 1, 0)" : "") ."
        ". (!empty($filter['query']) ? "
          + if(p.id = '". database::input($filter['query']) ."', 10, 0)
          + (match(pi.name) against ('". database::input_fulltext($filter['query']) ."' in boolean mode))
          + (match(pi.short_description) against ('". database::input_fulltext($filter['query']) ."' in boolean mode) / 2)
          + (match(pi.description) against ('". database::input_fulltext($filter['query']) ."' in boolean mode) / 3)
          + if(pi.name like '%". database::input($filter['query']) ."%', 3, 0)
          + if(pi.short_description like '%". database::input_like($filter['query']) ."%', 2, 0)
          + if(pi.description like '%". database::input_like($filter['query']) ."%', 1, 0)
          + if(p.keywords like '%". database::input_like($filter['query']) ."%', 1, 0)
          + if(p.code regexp '^". database::input($code_regex) ."$', 10, 0)
          + if(p.sku regexp '^". database::input($code_regex) ."$', 10, 0)
          + if(p.mpn regexp '^". database::input($code_regex) ."$', 10, 0)
          + if(p.gtin regexp '^". database::input($code_regex) ."$', 10, 0)
          + if(p.code regexp '". database::input($code_regex) ."', 5, 0)
          + if(p.sku regexp '". database::input($code_regex) ."', 5, 0)
          + if(p.mpn regexp '". database::input($code_regex) ."', 5, 0)
          + if(p.gtin regexp '". database::input($code_regex) ."', 5, 0)
          + if(p.id in (
            select product_id from ". DB_TABLE_PREFIX ."products_options_stock
            where sku regexp '". database::input($code_regex) ."'
          ), 5, 0)
        " : "") ."
      ) as relevance

      from (
        select p.id, p.sold_out_status_id, p.code, p.sku, p.mpn, p.gtin, p.manufacturer_id, group_concat(ptc.category_id separator ',') as categories, p.keywords, p.image, p.recommended_price, p.tax_class_id, p.quantity, p.views, p.purchases, p.date_created
        from ". DB_TABLE_PREFIX ."products p
        left join ". DB_TABLE_PREFIX ."products_to_categories ptc on (p.id = ptc.product_id)
        left join ". DB_TABLE_PREFIX ."sold_out_statuses ss on (p.sold_out_status_id = ss.id)
        where p.status
          and (p.id
          ". (!empty($filter['products']) ? "or p.id in ('". implode("', '", database::input($filter['products'])) ."')" : "") ."
          ". (!empty($filter['categories']) ? "or ptc.category_id in (". implode(",", database::input($filter['categories'])) .")" : "") ."
          ". (!empty($filter['manufacturers']) ? "or manufacturer_id in ('". implode("', '", database::input($filter['manufacturers'])) ."')" : "") ."
          ". (!empty($filter['keywords']) ? "or (find_in_set('". implode("', p.keywords) or find_in_set('", database::input($filter['keywords'])) ."', p.keywords))" : "") ."
        )
        and (p.quantity > 0 or ss.hidden != 1)
        and (p.date_valid_from is null or p.date_valid_from <= '". date('Y-m-d H:i:s') ."')
        and (p.date_valid_to is null or year(p.date_valid_to) < '1971' or p.date_valid_to >= '". date('Y-m-d H:i:s') ."')
        ". (!empty($filter['purchased']) ? "and p.purchases" : "") ."
        ". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : "") ."
        group by ptc.product_id
        ". ((!empty($sql_inner_sort) && !empty($filter['limit'])) ? "order by " . implode(",", $sql_inner_sort) : "") ."
        ". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") ."
      ) p

      left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

      left join ". DB_TABLE_PREFIX ."manufacturers m on (m.id = p.manufacturer_id)

      left join (
        select product_id, group_concat(concat(group_id, '-', if(custom_value != '', custom_value, value_id)) separator ',') as attributes
        from ". DB_TABLE_PREFIX ."products_attributes
        group by product_id
        order by id
      ) pa on (p.id = pa.product_id)

      left join (
        select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
        from ". DB_TABLE_PREFIX ."products_prices
      ) pp on (pp.product_id = p.id)

      left join (
        select product_id, min(if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`)) as campaign_price, start_date, end_date
        from ". DB_TABLE_PREFIX ."products_campaigns
        where (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
        and (end_date is null or year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
        group by product_id
      ) pc on (pc.product_id = p.id)

      where (p.id
        ". (!empty($filter['sql_where']) ? "or (". $filter['sql_where'] .")" : "") ."
        ". (!empty($filter['product_name']) ? "or pi.name like '%". database::input($filter['product_name']) ."%'" : "") ."
        ". (!empty($filter['campaign']) ? "or campaign_price > 0" : "") ."
        ". (!empty($sql_where_prices) ? $sql_where_prices : "") ."
      )

      group by p.id
      having relevance > 0

      ". (!empty($sql_outer_sort) ? "order by ". implode(",", $sql_outer_sort) : "") ."
      ". (!empty($filter['limit']) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") .";"
    );

    $products_query = database::query($query);

    return $products_query;
  }

  function catalog_stock_adjust($product_id, $combination, $quantity) {
    trigger_error('catalog_stock_adjust() is deprecated. Use $ent_product->adjust_quantity()', E_USER_DEPRECATED);
    $product = new ent_product($product_id);
    return $product->adjust_quantity($quantity, $combination);
  }
