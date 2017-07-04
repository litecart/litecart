<?php
  if (empty($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'relevance';

  $_GET['query'] = trim($_GET['query']);

  document::$snippets['title'][] = !empty($_GET['query']) ? sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), htmlspecialchars($_GET['query'])) : language::translate('title_search_results', 'Search Results');

  breadcrumbs::add(language::translate('title_search_results', 'Search Results'), document::ilink('search'));
  breadcrumbs::add(!empty($_GET['query']) ? strip_tags($_GET['query']) : language::translate('title_all_products', 'All Products'));

  functions::draw_lightbox();

  $_page = new view();
  $_page->snippets = array(
    'title' => sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), htmlspecialchars($_GET['query'])),
    'products' => array(),
    'sort_alternatives' => array(
      'relevance' => language::translate('title_relevance', 'Relevance'),
      'name' => language::translate('title_name', 'Name'),
      'price' => language::translate('title_price', 'Price'),
      'popularity' => language::translate('title_popularity', 'Popularity'),
      'date' => language::translate('title_date', 'Date'),
    ),
    'pagination' => null,
  );

  $query =
    "select p.*, pi.name, pi.short_description, m.name as manufacturer_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price)) as final_price,
    match(pi.name, pi.short_description, pi.description) against ('". database::input($_GET['query']) ."' in boolean mode) as relevance

    from (
      select id, code, gtin, sku, manufacturer_id, default_category_id, keywords, product_groups, image, tax_class_id, quantity, views, purchases, date_updated, date_created
      from ". DB_TABLE_PRODUCTS ."
      where status
      and (date_valid_from <= '". date('Y-m-d H:i:s') ."')
      and (year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."')
    ) p

    left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". language::$selected['code'] ."')

    left join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)

    left join (
      select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
      from ". DB_TABLE_PRODUCTS_PRICES ."
    ) pp on (pp.product_id = p.id)

    left join (
      select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as campaign_price
      from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
      where (start_date <= '". date('Y-m-d H:i:s') ."')
      and (year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
      order by end_date asc
    ) pc on (pc.product_id = p.id)

    having relevance > 0
    or p.code regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'
    or p.gtin regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'
    or p.sku regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'

    order by %sql_sort;
  ";

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
    header('Location: '. document::ilink('product', array('product_id' => $product['id'])), 302);
    exit;
  }

  if (database::num_rows($products_query) > 0) {

    if ($_GET['page'] > 1) database::seek($products_query, (settings::get('items_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($listing_item = database::fetch($products_query)) {
      $_page->snippets['products'][] = $listing_item;

      if (++$page_items == settings::get('items_per_page')) break;
    }
  }

  $_page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($products_query)/settings::get('items_per_page')));

  echo $_page->stitch('pages/search_results');
