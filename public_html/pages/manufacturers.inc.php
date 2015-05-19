<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_header.inc.php');
  
  breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'));
  
  document::$snippets['title'][] = language::translate('manufacturers.php:head_title', 'Manufacturers');
  document::$snippets['keywords'] = language::translate('manufacturers.php:meta_keywords', '');
  document::$snippets['description'] = language::translate('manufacturers.php:meta_description', '');
  
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');
  
  $box_manufacturers_cache_id = cache::cache_id('box_manufacturers', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if (cache::capture($box_manufacturers_cache_id, 'file')) {
    
    $page = new view();
    
    $manufacturers_query = database::query(
      "select m.id, m.name, m.image, mi.short_description, mi.link
      from ". DB_TABLE_MANUFACTURERS ." m
      left join ". DB_TABLE_MANUFACTURERS_INFO ." mi on (mi.manufacturer_id = m.id and mi.language_code = '". language::$selected['code'] ."')
      where status
      order by name;"
    );
    
    while($manufacturer = database::fetch($manufacturers_query)) {
      $page->snippets['manufacturers'][] = array(
        'id' => $manufacturer['id'],
        'name' => $manufacturer['name'],
        'image' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], 220, 60, 'FIT_ONLY_BIGGER_USE_WHITESPACING'),
        'link' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
      );
    }
    
    echo $page->stitch('views/box_manufacturers');
    
    cache::end_capture($box_manufacturers_cache_id);
  }
?>
