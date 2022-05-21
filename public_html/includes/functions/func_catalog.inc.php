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

      left join (
        select category_id, count(product_id) as num_products
        from lc_products_to_categories
        group by category_id
      ) p2c on (p2c.category_id = c.id)

      left join (
        select parent_id, count(id) as num_subcategories
        from lc_categories
        where status
        group by parent_id
      ) c2 on (c2.parent_id = c.id)

      where c.status
      and c.parent_id = ". (int)$parent_id ."
      and (p2c.num_products > 0 or c2.num_subcategories > 0)

      order by c.priority asc, ci.name asc;"
    );

    return $categories_query;
  }

// Filter function using AND syntax
  function catalog_products_query($filter=[]) {

    if (!is_array($filter)) trigger_error('Invalid array filter for products query', E_USER_ERROR);

    if (!empty($filter['categories'])) $filter['categories'] = array_filter($filter['categories']);
    if (!empty($filter['brands'])) $filter['brands'] = array_filter($filter['brands']);
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
      $sql_where_categories =
        "and p.id in (
          select distinct product_id from ". DB_TABLE_PREFIX ."products_to_categories
          where category_id in ('". implode("', '", database::input($filter['categories'])) ."')
        )";
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
      "select p.*, pi.name, pi.short_description, b.id as brand_id, b.name as brand_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price) as final_price, pa.attributes

      from (
        select p.id, p.delivery_status_id, p.sold_out_status_id, p.code, p.brand_id, p.keywords, p.image, p.recommended_price, p.tax_class_id, p.quantity, p.quantity_unit_id, p.views, p.purchases, p.date_created

        from ". DB_TABLE_PREFIX ."products p

        left join ". DB_TABLE_PREFIX ."sold_out_statuses ss on (p.sold_out_status_id = ss.id)

        where p.status
        ". (!empty($filter['products']) ? "and p.id in ('". implode("', '", database::input($filter['products'])) ."')" : null) ."
        ". fallback($sql_where_categories) ."
        ". fallback($sql_where_attributes) ."
        ". (!empty($filter['brands']) ? "and p.brand_id in ('". implode("', '", database::input($filter['brands'])) ."')" : null) ."
        ". (!empty($filter['keywords']) ? "and (". implode(" or ", array_map(function($s){ return "find_in_set('$s', p.keywords)"; }, database::input($filter['keywords']))) .")" : null) ."
        and (p.quantity > 0 or ss.hidden != 1)
        and (p.date_valid_from is null or p.date_valid_from <= '". date('Y-m-d H:i:s') ."')
        and (p.date_valid_to is null or year(p.date_valid_to) < '1971' or p.date_valid_to >= '". date('Y-m-d H:i:s') ."')
        ". (!empty($filter['purchased']) ? "and p.purchases" : null) ."
        ". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : null) ."

        ". ((!empty($sql_inner_sort) && !empty($filter['limit'])) ? "order by " . implode(",", $sql_inner_sort) : null) ."
        ". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : null) . (int)$filter['limit'] : "") ."
      ) p

      left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

      left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)

      left join (
        select product_id, group_concat(concat(group_id, '-', if(custom_value != '', custom_value, value_id)) separator ',') as attributes
        from ". DB_TABLE_PREFIX ."products_attributes
        group by product_id
        order by id
      ) pa on (p.id = pa.product_id)

      left join (
        select product_id, coalesce(
          ". implode(", ", array_map(function($currency){ return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)"; }, currency::$currencies)) ."
        ) as price
        from ". DB_TABLE_PREFIX ."products_prices
      ) pp on (pp.product_id = p.id)

      left join (
        select product_id, min(
          coalesce(
            ". implode(", ", array_map(function($currency){ return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)"; }, currency::$currencies)) ."
          )
        ) as campaign_price
        from ". DB_TABLE_PREFIX ."products_campaigns
        where (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
        and (end_date is null or year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
        group by product_id
      ) pc on (pc.product_id = p.id)

      where (p.id
        ". (!empty($filter['sql_where']) ? "and (". $filter['sql_where'] .")" : null) ."
        ". (!empty($filter['product_name']) ? "and pi.name like '%". database::input($filter['product_name']) ."%'" : null) ."
        ". (!empty($filter['campaign']) ? "and campaign_price > 0" : null) ."
        ". fallback($sql_where_prices) ."
      )

      group by p.id

      ". (!empty($sql_outer_sort) ? "order by ". implode(",", $sql_outer_sort) : "") ."
      ". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : null) . (int)$filter['limit'] : null) .";"
    );

    $products_query = database::query($query);

    return $products_query;
  }

// Search function using OR syntax
  function catalog_products_search_query($filter=[]) {

    if (!is_array($filter)) trigger_error('Invalid array filter for products query', E_USER_ERROR);

    if (!empty($filter['categories'])) $filter['categories'] = array_filter($filter['categories']);
    if (!empty($filter['brands'])) $filter['brands'] = array_filter($filter['brands']);
    if (!empty($filter['products'])) $filter['products'] = array_filter($filter['products']);
    if (!empty($filter['exclude_products'])) $filter['exclude_products'] = array_filter($filter['exclude_products']);

    $sql_where_categories = '';
    if (!empty($filter['categories'])) {
      $sql_where_categories = (
        "and p.id in (
          select distinct product_id from ". DB_TABLE_PREFIX ."products_to_categories
          where category_id in ('". implode("', '", database::input($filter['categories'])) ."')
        )"
      );
    }

    $sql_where_attributes = [];
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

    $sql_where_prices = [];
    if (!empty($filter['price_ranges']) && is_array($filter['price_ranges'])) {
      foreach ($filter['price_ranges'] as $price_range) {
        list($min,$max) = explode('-', $price_range);
        $sql_where_prices[] = "(if(pc.campaign_price, pc.campaign_price, pp.price) >= ". (float)$min ." and if(pc.campaign_price, pc.campaign_price, pp.price) <= ". (float)$max .")";
      }
      $sql_where_prices = "and (". implode(" or ", $sql_where_prices) .")";
    }

    $currencies = currency::$currencies;
    uasort($currencies, function($a, $b){
      if ($a['code'] == settings::get('site_currency_code')) return -3;
      if ($a['code'] == currency::$selected['code']) return -2;
    });

    $query = (
      "select p.*, pi.name, pi.short_description, b.id as brand_id, b.name as brand_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price) as final_price, pa.attributes, (0
        ". (!empty($filter['product_name']) ? "+ if(pi.name like '%". database::input($filter['product_name']) ."%', 1, 0)" : false) ."
        ". (!empty($filter['sql_where']) ? "+ if(". $filter['sql_where'] .", 1, 0)" : false) ."
        ". (!empty($filter['keywords']) ? "+ if(find_in_set('". implode("', p.keywords), 1, 0) + if(find_in_set('", database::input($filter['keywords'])) ."', p.keywords), 1, 0)" : false) ."
        ". (!empty($filter['products']) ? "+ if(p.id in ('". implode("', '", database::input($filter['products'])) ."'), 1, 0)" : false) ."
      ) as occurrences

      from (
        select p.id, p.delivery_status_id, p.sold_out_status_id, p.code, p.brand_id, group_concat(ptc.category_id separator ',') as categories, p.keywords, p.image, p.recommended_price, p.tax_class_id, p.quantity, p.quantity_unit_id, p.views, p.purchases, p.date_created
        from ". DB_TABLE_PREFIX ."products p
        left join ". DB_TABLE_PREFIX ."products_to_categories ptc on (p.id = ptc.product_id)
        left join ". DB_TABLE_PREFIX ."sold_out_statuses ss on (p.sold_out_status_id = ss.id)
        where p.status
          and (p.id
          ". (!empty($filter['products']) ? "or p.id in ('". implode("', '", database::input($filter['products'])) ."')" : null) ."
          ". fallback($sql_where_categories) ."
          ". (!empty($filter['brands']) ? "or brand_id in ('". implode("', '", database::input($filter['brands'])) ."')" : null) ."
          ". fallback($sql_where_attributes) ."
          ". (!empty($filter['keywords']) ? "or (". implode(" or ", array_map(function($s){ return "find_in_set('$s', p.keywords)"; }, database::input($filter['keywords']))) .")" : null) ."
        )
        and (p.quantity > 0 or ss.hidden != 1)
        and (p.date_valid_from is null or p.date_valid_from <= '". date('Y-m-d H:i:s') ."')
        and (p.date_valid_to is null or year(p.date_valid_to) < '1971' or p.date_valid_to >= '". date('Y-m-d H:i:s') ."')
        ". (!empty($filter['purchased']) ? "and p.purchases" : null) ."
        ". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : null) ."
        group by ptc.product_id
        ". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : null) . (int)$filter['limit'] : "") ."
      ) p

      left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

      left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)

      left join (
        select product_id, group_concat(concat(group_id, '-', if(custom_value != '', custom_value, value_id)) separator ',') as attributes
        from ". DB_TABLE_PREFIX ."products_attributes
        group by product_id
        order by id
      ) pa on (p.id = pa.product_id)

      left join (
        select product_id, coalesce(
          ". implode(", ", array_map(function($currency){ return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)"; }, currency::$currencies)) ."
        ) as price
        from ". DB_TABLE_PREFIX ."products_prices
      ) pp on (pp.product_id = p.id)

      left join (
        select product_id, min(
          coalesce(
            ". implode(", ", array_map(function($currency){ return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)"; }, currency::$currencies)) ."
          )
        ) as campaign_price
        from ". DB_TABLE_PREFIX ."products_campaigns
        where (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
        and (end_date is null or year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
        group by product_id
      ) pc on (pc.product_id = p.id)

      where (p.id
        ". (!empty($filter['sql_where']) ? "or (". $filter['sql_where'] .")" : null) ."
        ". (!empty($filter['product_name']) ? "or pi.name like '%". database::input($filter['product_name']) ."%'" : null) ."
        ". (!empty($filter['campaign']) ? "or campaign_price > 0" : null) ."
        ". fallback($sql_where_prices) ."
      )

      group by p.id

      order by occurrences desc
      ". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : null) . (int)$filter['limit'] : null) .";"
    );

    $products_query = database::query($query);

    return $products_query;
  }
