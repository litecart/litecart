<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'price';
  if (empty($_GET['manufacturer_id'])) {
    header('Location: '. document::ilink('manufacturers'));
    exit;
  }

  functions::draw_lightbox();

  $manufacturer = reference::manufacturer($_GET['manufacturer_id']);

  if (empty($manufacturer->id)) {
    http_response_code(410);
    echo language::translate('error_410_gone', 'The requested file is no longer available');
    return;
  }

  if (empty($manufacturer->status)) {
    http_response_code(404);
    echo language::translate('error_404_not_found', 'The requested file could not be found');
    return;
  }

  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('manufacturer', array('manufacturer_id' => (int)$manufacturer->id), false) .'" />';
  document::$snippets['title'][] = $manufacturer->head_title ? $manufacturer->head_title : $manufacturer->name;
  document::$snippets['description'] = $manufacturer->meta_description ? $manufacturer->meta_description : strip_tags($manufacturer->short_description);

  breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'), document::ilink('manufacturers'));
  breadcrumbs::add($manufacturer->name);

  $_page = new ent_view();

  $manufacturer_cache_token = cache::token('box_manufacturer', array('basename', 'get', 'language', 'currency', 'account', 'prices'), 'file');
  if (!$_page->snippets = cache::get($manufacturer_cache_token, 'file', ($_GET['sort'] == 'popularity') ? 0 : 3600)) {

    $_page->snippets = array(
      'id' => $manufacturer->id,
      'title' => $manufacturer->h1_title ? $manufacturer->h1_title : $manufacturer->name,
      'name' => $manufacturer->name,
      'description' => $manufacturer->description,
      'link' => $manufacturer->link,
      'image' => array(
        'original' => 'images/' . $manufacturer->image,
        'thumbnail' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $manufacturer->image, 200, 0, 'FIT_ONLY_BIGGER'),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $manufacturer->image, 200*2, 0, 'FIT_ONLY_BIGGER'),
      ),
      'products' => array(),
      'sort_alternatives' => array(
        'name' => language::translate('title_name', 'Name'),
        'price' => language::translate('title_price', 'Price'),
        'popularity' => language::translate('title_popularity', 'Popularity'),
        'date' => language::translate('title_date', 'Date'),
      ),
      'pagination' => null,
    );

    $products_query = functions::catalog_products_query(array(
      'manufacturers' => array($manufacturer->id),
      'sort' => $_GET['sort'],
      'campaigns_first' => true,
    ));

    if (database::num_rows($products_query) > 0) {
      if ($_GET['page'] > 1) database::seek($products_query, (settings::get('items_per_page', 20) * ($_GET['page']-1)));

      $page_items = 0;
      while ($listing_item = database::fetch($products_query)) {
        $_page->snippets['products'][] = $listing_item;

        if (++$page_items == settings::get('items_per_page', 20)) break;
      }
    }

    $_page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($products_query)/settings::get('items_per_page', 20)));

    cache::set($manufacturer_cache_token, $_page->snippets);
  }

  echo $_page->stitch('pages/manufacturer');
