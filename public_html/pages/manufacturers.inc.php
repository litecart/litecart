<?php
  document::$snippets['title'][] = language::translate('manufacturers:head_title', 'Manufacturers');
  document::$snippets['description'] = language::translate('manufacturers:meta_description', '');

  breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'));

  $manufacturers_cache_id = cache::cache_id('manufacturers', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if (cache::capture($manufacturers_cache_id, 'file')) {

    $_page = new view();

    $manufacturers_query = database::query(
      "select m.id, m.name, m.image, mi.short_description, mi.link
      from ". DB_TABLE_MANUFACTURERS ." m
      left join ". DB_TABLE_MANUFACTURERS_INFO ." mi on (mi.manufacturer_id = m.id and mi.language_code = '". language::$selected['code'] ."')
      where status
      order by name;"
    );

    $_page->snippets['manufacturers'] = array();

    while($manufacturer = database::fetch($manufacturers_query)) {
      $_page->snippets['manufacturers'][] = array(
        'id' => $manufacturer['id'],
        'name' => $manufacturer['name'],
        'image' => array(
          'original' => WS_DIR_IMAGES . $manufacturer['image'],
          'thumbnail' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], 320, 100, 'FIT_ONLY_BIGGER_USE_WHITESPACING'),
          'thumbnail_2x' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], 640, 200, 'FIT_ONLY_BIGGER_USE_WHITESPACING'),
        ),
        'link' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
      );
    }

    echo $_page->stitch('pages/manufacturers');

    cache::end_capture($manufacturers_cache_id);
  }
