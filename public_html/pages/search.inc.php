<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
    $_GET['page'] = 1;
  }

  if (empty($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['sort'])) $_GET['sort'] = 'relevance';

  $_GET['query'] = trim($_GET['query']);

  document::$snippets['title'][] = !empty($_GET['query']) ? sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), functions::escape_html($_GET['query'])) : language::translate('title_search_results', 'Search Results');

  breadcrumbs::add(language::translate('title_search_results', 'Search Results'), document::ilink('search'));
  breadcrumbs::add(!empty($_GET['query']) ? strip_tags($_GET['query']) : language::translate('title_all_products', 'All Products'));

  functions::draw_lightbox();

  $_page = new ent_view();
  $_page->snippets = [
    'title' => !empty($_GET['query']) ? sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), functions::escape_html($_GET['query'])) : language::translate('text_displaying_all_products', 'Displaying all products'),
    'products' => [],
    'sort_alternatives' => [
      'relevance' => language::translate('title_relevance', 'Relevance'),
      'name' => language::translate('title_name', 'Name'),
      'price' => language::translate('title_price', 'Price'),
      'popularity' => language::translate('title_popularity', 'Popularity'),
      'date' => language::translate('title_date', 'Date'),
    ],
    'pagination' => null,
  ];

  $code_regex = functions::format_regex_code($_GET['query']);

  $query =
    "select p.*, pi.name, pi.short_description, m.name as manufacturer_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price)) as final_price,

    ". (!empty($_GET['query']) ? "(
      if(p.id = '". database::input($_GET['query']) ."', 10, 0)
      + (match(pi.name) against ('". database::input_fulltext($_GET['query']) ."' in boolean mode))
      + (match(pi.short_description) against ('". database::input_fulltext($_GET['query']) ."' in boolean mode) / 2)
      + (match(pi.description) against ('". database::input_fulltext($_GET['query']) ."' in boolean mode) / 3)
      + if(pi.name like '%". database::input($_GET['query']) ."%', 3, 0)
      + if(pi.short_description like '%". database::input_like($_GET['query']) ."%', 2, 0)
      + if(pi.description like '%". database::input_like($_GET['query']) ."%', 1, 0)
      + if(p.keywords like '%". database::input_like($_GET['query']) ."%', 1, 0)
      + if(p.code regexp '". database::input($code_regex) ."', 5, 0)
      + if(p.sku regexp '". database::input($code_regex) ."', 5, 0)
      + if(p.mpn regexp '". database::input($code_regex) ."', 5, 0)
      + if(p.gtin regexp '". database::input($code_regex) ."', 5, 0)
      + if (p.id in (
        select product_id from ". DB_TABLE_PREFIX ."products_options_stock
        where sku regexp '". database::input($code_regex) ."'
      ), 5, 0)
    )" : "1") ." as relevance

    from (
      select id, code, mpn, gtin, sku, manufacturer_id, default_category_id, keywords, image, recommended_price, tax_class_id, quantity, sold_out_status_id, views, purchases, date_updated, date_created
      from ". DB_TABLE_PREFIX ."products
      where status
      and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
      and (date_valid_to is null or year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."')
    ) p

    left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

    left join ". DB_TABLE_PREFIX ."manufacturers m on (m.id = p.manufacturer_id)

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

    left join ". DB_TABLE_PREFIX ."sold_out_statuses ss on (p.sold_out_status_id = ss.id)

    where (p.quantity > 0 or ss.hidden != 1)

    having relevance > 0

    order by %sql_sort;";

  switch($_GET['sort']) {
    case 'name':
      $query = str_replace("%sql_sort", "name asc", $query);
      break;
    case 'price':
      $query = str_replace("%sql_sort", "final_price asc", $query);
      break;
    case 'date':
      $query = str_replace("%sql_sort", "date_created desc", $query);
      break;
    case 'rand':
      $query = str_replace("%sql_sort", "rand()", $query);
      break;
    case 'popularity':
      $query = str_replace("%sql_sort", "(p.purchases / (datediff(now(), p.date_created)/7)) desc, (p.views / (datediff(now(), p.date_created)/7)) desc", $query);
      break;
    default:
      $query = str_replace("%sql_sort", "relevance desc", $query);
      break;
  }

  $products_query = database::query($query);

  if (database::num_rows($products_query) == 1) {
    $product = database::fetch($products_query);
    header('Location: '. document::ilink('product', ['product_id' => $product['id']]), true, 302);
    exit;
  }

  if (database::num_rows($products_query) > 0) {

    if ($_GET['page'] > 1) database::seek($products_query, (settings::get('items_per_page') * ($_GET['page'] - 1)));

    $page_items = 0;
    while ($listing_item = database::fetch($products_query)) {
      $_page->snippets['products'][] = $listing_item;

      if (++$page_items == settings::get('items_per_page')) break;
    }
  }

  $_page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($products_query)/settings::get('items_per_page')));

  echo $_page->stitch('pages/search_results');
