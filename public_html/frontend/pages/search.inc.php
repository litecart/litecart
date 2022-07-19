<?php
  if (empty($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'relevance';

  $_GET['query'] = trim($_GET['query']);

  if (empty($_GET['query'])) {
    header('Location: '. document::ilink(''));
    exit;
  }

  document::$snippets['title'][] = !empty($_GET['query']) ? sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), functions::escape_html($_GET['query'])) : language::translate('title_search_results', 'Search Results');

  breadcrumbs::add(language::translate('title_search_results', 'Search Results'), document::ilink('search'));
  breadcrumbs::add(!empty($_GET['query']) ? strip_tags($_GET['query']) : language::translate('title_all_products', 'All Products'));

  functions::draw_lightbox();

  $_page = new ent_view(FS_DIR_TEMPLATE . 'pages/search_results.inc.php');
  $_page->snippets = [
    'title' => sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), functions::escape_html($_GET['query'])),
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
  $query_fulltext = functions::format_mysql_fulltext($_GET['query']);

  switch($_GET['sort']) {
    case 'name':
      $sql_order_by = "name asc";
      break;
    case 'price':
      $sql_order_by = "final_price asc";
      break;
    case 'date':
      $sql_order_by = "date_created desc";
      break;
    case 'rand':
      $sql_order_by = "rand()";
      break;
    case 'popularity':
      $sql_order_by = "(p.purchases / (datediff(now(), p.date_created)/7)) desc, (p.views / (datediff(now(), p.date_created)/7)) desc";
      break;
    default:
      $sql_order_by = "relevance desc";
      break;
  }

  $products_query = database::query(
    "select p.*, pi.name, pi.short_description, b.name as brand_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price)) as final_price, (
      if(p.id = '". database::input($_GET['query']) ."', 10, 0)
      + (match(pi.name) against ('". database::input($query_fulltext) ."' in boolean mode))
      + (match(pi.short_description) against ('". database::input($query_fulltext) ."' in boolean mode) / 2)
      + (match(pi.description) against ('". database::input($query_fulltext) ."' in boolean mode) / 3)
      + if(pi.name like '%". database::input($_GET['query']) ."%', 3, 0)
      + if(pi.short_description like '%". database::input($_GET['query']) ."%', 2, 0)
      + if(pi.description like '%". database::input($_GET['query']) ."%', 1, 0)
      + if(p.keywords like '%". database::input($_GET['query']) ."%', 1, 0)
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
      + if(b.name like '%". database::input($_GET['query']) ."%', 3, 0)
    ) as relevance

    from (
      select id, code, mpn, gtin, sku, brand_id, default_category_id, keywords, image, recommended_price, tax_class_id, quantity, sold_out_status_id, views, purchases, date_updated, date_created
      from ". DB_TABLE_PREFIX ."products
      where status
      and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
      and (date_valid_to is null or year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."')
    ) p

    left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

    left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)

    left join (
      select product_id,
        case
          when `". database::input(currency::$selected['code']) ."` != 0 then `". database::input(currency::$selected['code']) ."` * ". currency::$selected['value'] ."
          when ". implode(" when ", array_map(function($currency){ return "`". database::input($currency['code']) ."` != 0 then `". database::input($currency['code']) ."` * ". $currency['value'] . PHP_EOL; }, array_diff_key(currency::$currencies, array_flip([currency::$selected['code'], settings::get('store_currency_code')])))) ."
          else `". database::input(settings::get('store_currency_code')) ."`
        end
      as price
      from ". DB_TABLE_PREFIX ."products_prices
    ) pp on (pp.product_id = p.id)

    left join (
      select product_id, min(
        case
          when `". database::input(currency::$selected['code']) ."` != 0 then `". database::input(currency::$selected['code']) ."` * ". currency::$selected['value'] ."
          when ". implode(" when ", array_map(function($currency){ return "`". database::input($currency['code']) ."` != 0 then `". database::input($currency['code']) ."` * ". $currency['value'] . PHP_EOL; }, array_diff_key(currency::$currencies, array_flip([currency::$selected['code'], settings::get('store_currency_code')])))) ."
          else `". database::input(settings::get('store_currency_code')) ."`
        end
      ) as campaign_price
      from ". DB_TABLE_PREFIX ."products_campaigns
      where (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
      and (end_date is null or year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
      group by product_id
    ) pc on (pc.product_id = p.id)

    left join ". DB_TABLE_PREFIX ."sold_out_statuses ss on (p.sold_out_status_id = ss.id)

    where (p.quantity > 0 or ss.hidden != 1)

    having relevance > 0

    order by $sql_order_by;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

  if (count($products) == 1) {
    $product = current($products);
    header('Location: '. document::ilink('product', ['product_id' => $product['id']]), true, 302);
    exit;
  }

  $_page->snippets['pagination'] = functions::draw_pagination($num_pages);

  echo $_page;
