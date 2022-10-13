<?php
  document::$snippets['title'][] = language::translate('manufacturers:head_title', 'Manufacturers');
  document::$snippets['description'] = language::translate('manufacturers:meta_description', '');

  breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'));

  $manufacturers_cache_token = cache::token('manufacturers', ['get', 'language'], 'file');
  if (cache::capture($manufacturers_cache_token)) {

    $_page = new ent_view();

    $manufacturers_query = database::query(
      "select m.id, m.name, m.image, mi.short_description, mi.link
      from ". DB_TABLE_PREFIX ."manufacturers m
      left join ". DB_TABLE_PREFIX ."manufacturers_info mi on (mi.manufacturer_id = m.id and mi.language_code = '". database::input(language::$selected['code']) ."')
      where status
      order by name;"
    );

    $_page->snippets['manufacturers'] = [];

    while ($manufacturer = database::fetch($manufacturers_query)) {
      $_page->snippets['manufacturers'][] = [
        'id' => $manufacturer['id'],
        'name' => $manufacturer['name'],
        'image' => [
          'original' => 'images/' . $manufacturer['image'],
          'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $manufacturer['image'], 320, 100, 'FIT_ONLY_BIGGER_USE_WHITESPACING'),
          'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $manufacturer['image'], 640, 200, 'FIT_ONLY_BIGGER_USE_WHITESPACING'),
        ],
        'link' => document::ilink('manufacturer', ['manufacturer_id' => $manufacturer['id']]),
      ];
    }

    echo $_page->stitch('pages/manufacturers');

    cache::end_capture($manufacturers_cache_token);
  }
