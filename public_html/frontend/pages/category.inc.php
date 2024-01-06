<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  if (empty($_GET['sort'])) $_GET['sort'] = 'price';
  if (empty($_GET['list_style'])) $_GET['list_style'] = 'columns';

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
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }

  if (empty($category->status)) {
    http_response_code(404);
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }

  document::$title[] = $category->head_title ? $category->head_title : $category->name;
  document::$description = $category->meta_description ? $category->meta_description : strip_tags($category->short_description);

  document::$head_tags['canonical'] = '<link rel="canonical" href="'. document::href_ilink('category', ['category_id' => $category->id]) .'">';

  breadcrumbs::add(language::translate('title_categories', 'Categories'), document::ilink('categories'));
  foreach (array_slice($category->path, 0, -1, true) as $category_crumb) {
    breadcrumbs::add($category_crumb->name, document::ilink('category', ['category_id' => $category_crumb->id]));
  }
  breadcrumbs::add($category->name);

  functions::draw_lightbox();

  $_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/category.inc.php');

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
      'main_category' => [],
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
        'original' => $category->image ? 'storage://images/' . $category->image : '',
        'thumbnail' => functions::image_thumbnail('storage://images/' . $category->image, $width, $height),
        'thumbnail_2x' => functions::image_thumbnail('storage://images/' . $category->image, $width*2, $height*2),
        'viewport' => [
          'width' => $width,
          'height' => $height,
          'ratio' => str_replace(':', '/', settings::get('category_image_ratio')),
          'clipping' => strtolower(settings::get('category_image_clipping')),
        ],
      ];
    }

    // Main Category
    if (!empty($category->id)) {
      $_page->snippets['main_category'] = [
        'id' => $category->main_category->id,
        'name' => $category->main_category->name,
        'image' => [],
        'link' => document::ilink('category', ['category_id' => $category->main_category->id]),
      ];

      list($width, $height) = functions::image_scale_by_width(480, settings::get('category_image_ratio'));

      if (!empty($category->main_category->image)) {
        $_page->snippets['main_category']['image'] = [
          'original' => 'images/' . $category->main_category->image,
          'thumbnail' => functions::image_thumbnail('storage://images/' . $category->main_category->image, $width, $height),
          'thumbnail_2x' => functions::image_thumbnail('storage://images/' . $category->main_category->image, $width, $height),
          'viewport' => [
            'width' => $width,
            'height' => $height,
            'ratio' => str_replace(':', '/', settings::get('category_image_ratio')),
            'clipping' => strtolower(settings::get('category_image_clipping')),
          ],
        ];
      }
    }

  // Subcategories
    $_page->snippets['subcategories'] = functions::catalog_categories_query($category->id)->fetch_all();

  // Products
    $_page->snippets['products'] = functions::catalog_products_query([
      'categories' => [$category->id] + array_keys($category->descendants),
      'brands' => fallback($_GET['brands']),
      'attributes' => fallback($_GET['attributes']),
      'product_name' => fallback($_GET['product_name']),
      'sort' => $_GET['sort'],
      'campaigns_first' => true,
    ])->fetch_page($_GET['page'], null, $num_rows, $num_pages);

    $_page->snippets['num_products_page'] = count($_page->snippets['products']);
    $_page->snippets['num_products_total'] = $num_rows;
    $_page->snippets['pagination'] = functions::draw_pagination($num_pages);

    cache::set($box_category_cache_token, $_page->snippets);
  }

  echo $_page->render();
