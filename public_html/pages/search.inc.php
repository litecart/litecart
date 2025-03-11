<?php

  if (empty($_GET['query'])) {
    $_GET['query'] = '';
  }

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
    $_GET['page'] = 1;
  }

  if (empty($_GET['sort'])) {
    $_GET['sort'] = 'relevance';
  }

  // Halt on invalid characters
  if (!in_array(language::$selected['code'], ['ja', 'zh', 'ko']) && preg_match('#[^\p{L}\p{N}\p{Zs}\p{P}\p{S}\p{M}]#u', $_GET['query'])) {
    http_response_code(400);
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  $_GET['query'] = trim($_GET['query']);

  document::$snippets['title'][] = !empty($_GET['query']) ? strtr(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), ['%s' => functions::escape_html($_GET['query'])]) : language::translate('title_search_results', 'Search Results');

  breadcrumbs::add(language::translate('title_search_results', 'Search Results'), document::ilink('search'));
  breadcrumbs::add(!empty($_GET['query']) ? strip_tags($_GET['query']) : language::translate('title_all_products', 'All Products'));

  functions::draw_lightbox();

  $_page = new ent_view();
  $_page->snippets = [
    'title' => !empty($_GET['query']) ? strtr(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), ['%s' => functions::escape_html($_GET['query'])]) : language::translate('text_displaying_all_products', 'Displaying all products'),
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

  $products_query = functions::catalog_products_search_query([
    'query' => $_GET['query'],
  ]);

  $num_rows = database::num_rows($products_query);
  $num_pages = ceil($num_rows / settings::get('items_per_page'));

  if ($num_rows == 1) {
    $product = database::fetch($products_query);
    header('Location: '. document::ilink('product', ['product_id' => $product['id']]), true, 302);
    exit;
  }

  if ($num_rows) {

    if ($_GET['page'] > 1) database::seek($products_query, (settings::get('items_per_page') * ($_GET['page'] - 1)));

    $page_items = 0;
    while ($listing_item = database::fetch($products_query)) {
      $_page->snippets['products'][] = $listing_item;

      if (++$page_items == settings::get('items_per_page')) break;
    }
  }

  $_page->snippets['pagination'] = functions::draw_pagination($num_pages);

  echo $_page->stitch('pages/search_results');
