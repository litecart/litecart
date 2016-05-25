<?php
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'price';
  if (empty($_GET['category_id'])) {
    header('Location: '. document::ilink('categories'));
    exit;
  }

  $category = catalog::category($_GET['category_id']);

  if (empty($category->id)) {
    notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
    http_response_code(410);
    header('Refresh: 0; url='. document::ilink('categories'));
    exit;
  }

  if (empty($category->status)) {
    notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
    http_response_code(404);
    header('Refresh: 0; url='. document::ilink('categories'));
    exit;
  }

  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('category', array('category_id' => $category->id), false) .'" />';
  document::$snippets['title'][] = $category->head_title[language::$selected['code']] ? $category->head_title[language::$selected['code']] : $category->name[language::$selected['code']];
  document::$snippets['description'] = $category->meta_description[language::$selected['code']] ? $category->meta_description[language::$selected['code']] : strip_tags($category->short_description[language::$selected['code']]);

  breadcrumbs::add(language::translate('title_categories', 'Categories'), document::ilink('categories'));
  foreach (array_slice(functions::catalog_category_trail($category->id), 0, -1, true) as $category_id => $category_name) {
    breadcrumbs::add($category_name, document::ilink('category', array('category_id' => $category_id)));
  }
  breadcrumbs::add($category->name[language::$selected['code']]);

  functions::draw_fancybox("a.fancybox[data-fancybox-group='product-listing']");

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');

  $box_category_cache_id = cache::cache_id('box_category', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if (cache::capture($box_category_cache_id, 'file', ($_GET['sort'] == 'popularity') ? 0 : 3600)) {

    $page = new view();

    $page->snippets = array(
      'id' => $category->id,
      'name' => $category->name[language::$selected['code']],
      'short_description' => $category->short_description[language::$selected['code']],
      'description' => $category->description[language::$selected['code']],
      'h1_title' => $category->h1_title[language::$selected['code']] ? $category->h1_title[language::$selected['code']] : $category->name[language::$selected['code']],
      'head_title' => $category->head_title[language::$selected['code']] ? $category->head_title[language::$selected['code']] : $category->name[language::$selected['code']],
      'meta_description' => $category->meta_description[language::$selected['code']] ? $category->meta_description[language::$selected['code']] : $category->short_description[language::$selected['code']],
      'image' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category->image, 1024, 0, 'FIT_ONLY_BIGGER'),
      'subcategories' => array(),
      'products' => array(),
      'sort_alternatives' => array(
        'name' => language::translate('title_name', 'Name'),
        'price' => language::translate('title_price', 'Price'),
        'popularity' => language::translate('title_popularity', 'Popularity'),
        'date' => language::translate('title_date', 'Date'),
      ),
    );

  // Subcategories
    $subcategories_query = functions::catalog_categories_query($category->id);
    if (database::num_rows($subcategories_query)) {
      while ($subcategory = database::fetch($subcategories_query)) {
        $page->snippets['subcategories'][] = $subcategory;
      }
    }

  // Products
    switch ($category->list_style) {
      case 'rows':
        $items_per_page = 10;
        break;
      case 'columns':
      default:
        $items_per_page = settings::get('items_per_page');
        break;
    }

    $products_query = functions::catalog_products_query(
      array(
        'category_id' => $category->id,
        'manufacturers' => !empty($_GET['manufacturers']) ? $_GET['manufacturers'] : null,
        'product_groups' => !empty($_GET['product_groups']) ? $_GET['product_groups'] : null,
        'sort' => $_GET['sort'],
      )
    );

    if (database::num_rows($products_query)) {
      if ($_GET['page'] > 1) database::seek($products_query, $items_per_page * ($_GET['page'] - 1));

      $page_items = 0;
      while ($listing_product = database::fetch($products_query)) {
        switch($category->list_style) {
          case 'rows':
            $listing_product['listing_type'] = 'row';
            $page->snippets['products'][] = $listing_product;
            break;
          default:
          case 'columns':
            $listing_product['listing_type'] = 'column';
            $page->snippets['products'][] = $listing_product;
            break;
        }
        if (++$page_items == $items_per_page) break;
      }
    }

    $page->snippets['num_products_page'] = count($page->snippets['products']);
    $page->snippets['num_products_total'] = (int)database::num_rows($products_query);
    $page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($products_query)/$items_per_page));

    echo $page->stitch('views/box_category');

    cache::end_capture($box_category_cache_id);
  }

?>