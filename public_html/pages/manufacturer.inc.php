<?php
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'price';
  if (empty($_GET['manufacturer_id'])) {
    header('Location: '. document::ilink('manufacturers'));
    exit;
  }

  $manufacturer = catalog::manufacturer($_GET['manufacturer_id']);

  if (empty($manufacturer->id)) {
    notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
    http_response_code(410);
    header('Refresh: 0; url='. document::ilink('manufacturers'));
    exit;
  }

  if (empty($manufacturer->status)) {
    notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
    http_response_code(404);
    header('Refresh: 0; url='. document::ilink('manufacturers'));
    exit;
  }


  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('manufacturer', array('manufacturer_id' => (int)$manufacturer->id), false) .'" />';
  document::$snippets['title'][] = $manufacturer->head_title[language::$selected['code']] ? $manufacturer->head_title[language::$selected['code']] : $manufacturer->name;
  document::$snippets['description'] = $manufacturer->meta_description[language::$selected['code']] ? $manufacturer->meta_description[language::$selected['code']] : strip_tags($manufacturer->short_description[language::$selected['code']]);

  breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'), document::ilink('manufacturers'));
  breadcrumbs::add($manufacturer->name);

  functions::draw_fancybox("a.fancybox[data-fancybox-group='product-listing']");

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');

  $manufacturer_cache_id = cache::cache_id('box_manufacturer', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if (cache::capture($manufacturer_cache_id, 'file', ($_GET['sort'] == 'popularity') ? 0 : 3600)) {

    $page = new view();

    $page->snippets = array(
      'id' => $manufacturer->id,
      'title' => $manufacturer->h1_title[language::$selected['code']] ? $manufacturer->h1_title[language::$selected['code']] : $manufacturer->name,
      'name' => $manufacturer->name,
      'description' => $manufacturer->description[language::$selected['code']],
      'link' => $manufacturer->link[language::$selected['code']],
      'image' => array(
        'original' => WS_DIR_IMAGES . $manufacturer->image,
        'thumbnail' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->image, 200, 0, 'FIT_ONLY_BIGGER'),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->image, 200*2, 0, 'FIT_ONLY_BIGGER'),
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
      'manufacturer_id' => $manufacturer->id,
      'product_groups' => !empty($_GET['product_groups']) ? $_GET['product_groups'] : null,
      'sort' => $_GET['sort'],
    ));

    if (database::num_rows($products_query) > 0) {
      if ($_GET['page'] > 1) database::seek($products_query, (settings::get('items_per_page', 20) * ($_GET['page']-1)));

      $page_items = 0;
      while ($listing_item = database::fetch($products_query)) {
        $page->snippets['products'][] = $listing_item;

        if (++$page_items == settings::get('items_per_page', 20)) break;
      }
    }

    $page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($products_query)/settings::get('items_per_page', 20)));


    echo $page->stitch('views/box_manufacturer');

    cache::end_capture($manufacturer_cache_id);
  }
?>