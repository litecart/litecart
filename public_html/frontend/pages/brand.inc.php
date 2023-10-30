<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  if (empty($_GET['sort'])) $_GET['sort'] = 'price';
  if (empty($_GET['list_style'])) $_GET['list_style'] = 'columns';

  if (empty($_GET['brand_id'])) {
    header('Location: '. document::ilink('brands'));
    exit;
  }

  functions::draw_lightbox();

  $brand = reference::brand($_GET['brand_id']);

  if (empty($brand->id)) {
    http_response_code(410);
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }

  if (empty($brand->status)) {
    http_response_code(404);
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }

  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('brand', ['brand_id' => (int)$brand->id], false) .'" />';
  document::$snippets['title'][] = $brand->head_title ? $brand->head_title : $brand->name;
  document::$snippets['description'] = $brand->meta_description ? $brand->meta_description : strip_tags($brand->short_description);

  breadcrumbs::add(language::translate('title_brands', 'Brands'), document::ilink('brands'));
  breadcrumbs::add($brand->name);

  $_page = new ent_view();

  $brand_cache_token = cache::token('box_brand', ['get', 'language', 'currency', 'prices'], 'file');
  if (!$_page->snippets = cache::get($brand_cache_token, ($_GET['sort'] == 'popularity') ? 0 : 3600)) {

    $_page->snippets = [
      'id' => $brand->id,
      'title' => $brand->h1_title ? $brand->h1_title : $brand->name,
      'name' => $brand->name,
      'description' => $brand->description,
      'link' => $brand->link,
      'image' => [
        'original' => 'storage://images/' . $brand->image,
        'thumbnail' => functions::image_thumbnail('storage://images/' . $brand->image, 200, 75),
        'thumbnail_2x' => functions::image_thumbnail('storage://images/' . $brand->image, 400, 150),
      ],
      'products' => [],
      'sort_alternatives' => [
        'name' => language::translate('title_name', 'Name'),
        'price' => language::translate('title_price', 'Price'),
        'popularity' => language::translate('title_popularity', 'Popularity'),
        'date' => language::translate('title_date', 'Date'),
      ],
      'pagination' => null,
    ];


    $_page->snippets['products'] = functions::catalog_products_query([
      'brands' => [$brand->id],
      'product_name' => fallback($_GET['product_name']),
      'sort' => $_GET['sort'],
      'campaigns_first' => true,
    ])->fetch_page($_GET['page'], 20, $num_rows, $num_pages);

    $_page->snippets['pagination'] = functions::draw_pagination($num_pages);

    cache::set($brand_cache_token, $_page->snippets);
  }

  echo $_page->render(FS_DIR_TEMPLATE . 'pages/brand.inc.php');
