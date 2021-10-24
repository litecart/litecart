<?php

  $result = [
    'name' => language::translate('title_products', 'Products'),
    'results' => [],
  ];

  $code_regex = functions::format_regex_code($query);
  $query_fulltext = functions::format_mysql_fulltext($_GET['query']);

  $products_query = database::query(
    "select p.id, p.default_category_id, pi.name,
    (
        if(p.id = '". database::input($query) ."', 10, 0)
        + (match(pi.name) against ('". database::input($query_fulltext) ."' in boolean mode))
        + (match(pi.short_description) against ('". database::input($query_fulltext) ."' in boolean mode) / 2)
        + (match(pi.description) against ('". database::input($query_fulltext) ."' in boolean mode) / 3)
        + if(pi.name like '%". database::input($query) ."%', 3, 0)
        + if(pi.short_description like '%". database::input($query) ."%', 2, 0)
        + if(pi.description like '%". database::input($query) ."%', 1, 0)
        + if(p.code regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.sku regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.mpn regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.gtin regexp '". database::input($code_regex) ."', 5, 0)
        + if (p.id in (
          select product_id from ". DB_TABLE_PREFIX ."products_to_stock_items
          where stock_item_id in (
            select id from ". DB_TABLE_PREFIX ."stock_items
            where sku regexp '". database::input($code_regex) ."'
          )
        ), 5, 0)
    ) as relevance

    from ". DB_TABLE_PREFIX ."products p

    left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

    having relevance > 0
    order by relevance desc, id asc
    limit 5;"
  );

  if (!database::num_rows($products_query)) return;

  while ($product = database::fetch($products_query)) {
    $result['results'][] = [
        'id' => $product['id'],
        'title' => $product['name'],
        'description' => $product['default_category_id'] ? reference::category($product['default_category_id'])->name : '['.language::translate('title_root', 'Root').']',
        'link' => document::ilink($app.'/edit_product', ['product_id' => $product['id']]),
    ];
  }

  return [$result];
