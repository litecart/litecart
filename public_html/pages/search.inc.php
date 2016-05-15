<?php
  if (empty($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'occurrences';

  $_GET['query'] = trim($_GET['query']);

  document::$snippets['title'][] = !empty($_GET['query']) ? sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), htmlspecialchars($_GET['query'])) : language::translate('title_search_results', 'Search Results');

  breadcrumbs::add(language::translate('title_search_results', 'Search Results'), document::ilink('search'));
  breadcrumbs::add(!empty($_GET['query']) ? strip_tags($_GET['query']) : language::translate('title_all Products', 'All Products'));

  functions::draw_fancybox("a.fancybox[data-fancybox-group='product-listing']");

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');

  $_page = new view();
  $_page->snippets = array(
    'title' => sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), htmlspecialchars($_GET['query'])),
    'products' => array(),
    'sort_alternatives' => array(
      'name' => language::translate('title_name', 'Name'),
      'price' => language::translate('title_price', 'Price'),
      'popularity' => language::translate('title_popularity', 'Popularity'),
      'date' => language::translate('title_date', 'Date'),
    ),
    'pagination' => null,
  );

  if (empty($_GET['query'])) {
    $_page->snippets['title'] = language::translate('title_all_products', 'All Products');
  }

  $manufacturers_info_query = database::query(
    "select m.id
    from ". DB_TABLE_MANUFACTURERS ." m, ". DB_TABLE_MANUFACTURERS_INFO ." mi
    where m.status
    and (mi.manufacturer_id = m.id and mi.language_code = '". database::input(language::$selected['code']) ."')
    and (
      m.name like '%". database::input($_GET['query']) ."%'
      or mi.description like '%". database::input($_GET['query']) ."%'
    );"
  );

  $manufacturer_ids = array();
  while ($manufacturer = database::fetch($manufacturers_info_query)) {
    $manufacturer_ids[] = (int)$manufacturer['id'];
  }

  $sql_select_occurrences = "(0
    + if(p.gtin like '%". database::input($_GET['query']) ."%', 3, 0)
    + if(p.sku like '%". database::input($_GET['query']) ."%', 3, 0)
    + if(p.code like '%". database::input($_GET['query']) ."%', 3, 0)
    + if(p.keywords like '%". database::input($_GET['query']) ."%', 3, 0)
    + if(pi.name like '%". database::input($_GET['query']) ."%', 3, 0)
    + if(pi.short_description like '%". database::input($_GET['query']) ."%', 1, 0)
    + if(pi.description like '%". database::input($_GET['query']) ."%', 1, 0)
    + if(p.manufacturer_id and p.manufacturer_id in ('". implode("', '", database::input($manufacturer_ids)) ."'), 2, 0)
  ) as occurrences";

  $sql_price_column = "if(pp.`". database::input(currency::$selected['code']) ."`, pp.`". database::input(currency::$selected['code']) ."` / ". (float)currency::$selected['value'] .", pp.`". database::input(settings::get('store_currency_code')) ."`)";

  $query =
    "select p.*, pi.name, pi.short_description, m.name as manufacturer_name, ". $sql_price_column ." as price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, ". $sql_price_column .")) as final_price, " . $sql_select_occurrences ."

    from (
      select id, code, gtin, sku, manufacturer_id, default_category_id, keywords, product_groups, image, tax_class_id, quantity, views, purchases, date_updated, date_created
      from ". DB_TABLE_PRODUCTS ."
      where status
      and (date_valid_from <= '". date('Y-m-d H:i:s') ."')
      and (year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."')
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
    where (
      p.code like '%". database::input($_GET['query']) ."%'
      or p.sku like '%". database::input($_GET['query']) ."%'
      or p.gtin like '%". database::input($_GET['query']) ."%'
      or p.keywords like '%". database::input($_GET['query']) ."%'
      ". (!empty($manufacturer_ids) ? "or p.manufacturer_id in (". implode(", ", $manufacturer_ids) .")" : false) ."
      or pi.name like '%". database::input($_GET['query']) ."%'
      or pi.short_description like '%". database::input($_GET['query']) ."%'
      or pi.description like '%". database::input($_GET['query']) ."%'
    )

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
    case 'occurrences':
      $query = str_replace("%sql_sort", "occurrences desc", $query);
      break;
    case 'rand':
      $query = str_replace("%sql_sort", "rand()", $query);
      break;
    case 'popularity':
    default:
      $query = str_replace("%sql_sort", "(p.purchases / (datediff(now(), p.date_created)/7)) desc, (p.views / (datediff(now(), p.date_created)/7)) desc", $query);
      $sql_global_sort = "";
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

  echo $_page->stitch('views/box_search_results');
?>