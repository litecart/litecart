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

  $_page = new ent_view();

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

  $products = functions::catalog_products_search_query([
    'query' => $_GET['query'],
    'sort' => $_GET['sort'],
  ])->fetch_page($_GET['page'], null, $num_rows, $num_pages);

  if (count($products) == 1) {
    $product = current($products);
    header('Location: '. document::ilink('product', ['product_id' => $product['id']]), true, 302);
    exit;
  }

  $_page->snippets['products'] = $products;
  $_page->snippets['pagination'] = functions::draw_pagination($num_pages);

  echo $_page->render(FS_DIR_TEMPLATE . 'pages/search_results.inc.php');
