<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
    $_GET['page'] = 1;
  }

  if (empty($_GET['sort'])) {
    $_GET['sort'] = 'price';
  }

  if (empty($_GET['category_id'])) {
    header('Location: '. document::ilink('categories'));
    exit;
  }

  if (!empty($_GET['attributes'])) {
    $_GET['attributes'] = array_map('array_filter', $_GET['attributes']);
    $_GET['attributes'] = array_filter($_GET['attributes']);
  }

  $category = reference::category($_GET['category_id']);

  if (empty($_GET['list_style'])) {
    $_GET['list_style'] = !empty($category->list_style) ? $category->list_style : 'columns';
  }

  if (empty($category->id)) {
    http_response_code(410);
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  if (empty($category->status)) {
    http_response_code(404);
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('category', ['category_id' => $category->id], false) .'">';
  document::$snippets['title'][] = $category->head_title ? $category->head_title : $category->name;
  document::$snippets['description'] = $category->meta_description ? $category->meta_description : strip_tags($category->short_description);

  if (!empty($category->image)) {
    $og_image = functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category->image, 1200, 630, 'FIT_USE_WHITESPACING');
    document::$snippets['head_tags'][] = '<meta property="og:image" content="'. document::href_rlink(FS_DIR_STORAGE . $og_image) .'">';
  }

  breadcrumbs::add(language::translate('title_categories', 'Categories'), document::ilink('categories'));
  foreach (array_slice($category->path, 0, -1, true) as $category_crumb) {
    breadcrumbs::add($category_crumb->name, document::ilink('category', ['category_id' => $category_crumb->id]));
  }
  breadcrumbs::add($category->name);

  functions::draw_lightbox();

  $_page = new ent_view();

  $box_category_cache_token = cache::token('box_category', ['get', 'language', 'currency'], 'file');
  if (!$_page->snippets = cache::get($box_category_cache_token, ($_GET['sort'] == 'popularity') ? 0 : 3600)) {

    $_page->snippets = [
      'id' => $category->id,
      'parent_id' => $category->parent_id,
      'name' => $category->name,
      'short_description' => $category->short_description,
      'description' => (!empty($category->description) && trim(strip_tags($category->description))) ? $category->description : '',
      'h1_title' => $category->h1_title ? $category->h1_title : $category->name,
      'head_title' => $category->head_title ? $category->head_title : $category->name,
      'meta_description' => $category->meta_description ? $category->meta_description : $category->short_description,
      'image' => [],
      'subcategories' => [],
      'products' => [],
      'list_style' => $category->list_style,
      'sort_alternatives' => [
        'name' => language::translate('title_name', 'Name'),
        'price' => language::translate('title_price', 'Price'),
        'popularity' => language::translate('title_popularity', 'Popularity'),
        'date' => language::translate('title_date', 'Date'),
      ],
    ];

    if ($category->image) {
      list($width, $height) = functions::image_scale_by_width(480, settings::get('category_image_ratio'));
      $_page->snippets['image'] = [
        'original' => $category->image ? 'images/' . $category->image : '',
        'thumbnail_1x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category->image, $width, $height, settings::get('category_image_clipping')),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category->image, $width*2, $height*2, settings::get('category_image_clipping')),
        'ratio' => str_replace(':', '/', settings::get('category_image_ratio')),
        'viewport' => [
          'width' => $width,
          'height' => $height,
        ],
      ];
    }

  // Subcategories
    $subcategories_query = functions::catalog_categories_query($category->id);
    while ($subcategory = database::fetch($subcategories_query)) {
      $_page->snippets['subcategories'][] = $subcategory;
    }

  // Products
    $products_query = functions::catalog_products_query([
      'categories' => [$category->id],
      'manufacturers' => !empty($_GET['manufacturers']) ? $_GET['manufacturers'] : null,
      'attributes' => !empty($_GET['attributes']) ? $_GET['attributes'] : null,
      'product_name' => isset($_GET['product_name']) ? $_GET['product_name'] : null,
      'sort' => $_GET['sort'],
      'campaigns_first' => true,
    ]);

    if (database::num_rows($products_query)) {
      if ($_GET['page'] > 1) database::seek($products_query, settings::get('items_per_page') * ($_GET['page'] - 1));

      $page_items = 0;
      while ($listing_product = database::fetch($products_query)) {
        $_page->snippets['products'][] = $listing_product;
        if (++$page_items == settings::get('items_per_page')) break;
      }
    }

    $_page->snippets['num_products_page'] = count($_page->snippets['products']);
    $_page->snippets['num_products_total'] = (int)database::num_rows($products_query);
    $_page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($products_query)/settings::get('items_per_page')));

    cache::set($box_category_cache_token, $_page->snippets);
  }

  echo $_page->stitch('pages/category');
