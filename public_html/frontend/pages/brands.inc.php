<?php
  document::$title[] = language::translate('brands:head_title', 'Brands');
  document::$description = language::translate('brands:meta_description', '');

  breadcrumbs::add(language::translate('title_brands', 'Brands'));

  $brands_cache_token = cache::token('brands', ['get', 'language'], 'file');
  if (cache::capture($brands_cache_token)) {

    $_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/brands.inc.php');

    $brands = database::query(
      "select b.id, b.name, b.image, bi.short_description, bi.link
      from ". DB_TABLE_PREFIX ."brands b
      left join ". DB_TABLE_PREFIX ."brands_info bi on (bi.brand_id = b.id and bi.language_code = '". language::$selected['code'] ."')
      where status
      order by name;"
    )->fetch_all();

    $_page->snippets['brands'] = [];

    foreach ($brands as $brand) {
      $_page->snippets['brands'][] = [
        'id' => $brand['id'],
        'name' => $brand['name'],
        'image' => $brand['image'] ? 'storage://images/' . $brand['image'] : '',
        'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
      ];
    }

    echo $_page->render();

    cache::end_capture($brands_cache_token);
  }
