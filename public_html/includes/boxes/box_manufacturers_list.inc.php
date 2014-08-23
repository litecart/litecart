<?php
  if (in_array(basename(route::$route), array('index', 'categories', 'manufacturers', 'product', 'search'))) return;
  
  $box_manufacturers_list_cache_id = cache::cache_id('box_manufacturers_list', array('language'));
  if (cache::capture($box_manufacturers_list_cache_id, 'file')) {

    $manufacturers_query = database::query(
      "select id, name from ". DB_TABLE_MANUFACTURERS ." m
      where status
      order by name asc;"
    );
    
    if (database::num_rows($manufacturers_query)) {
    
      $box_manufacturers_list = new view();
      
      $options = array(
        array(language::translate('option_select', '-- Select --'), ''),
      );
      
      while($manufacturer = database::fetch($manufacturers_query)) {
        $options[] = array($manufacturer['name'], $manufacturer['id']);
      }
      
      $box_manufacturers_list->snippets['options'] = $options;
      
      echo $box_manufacturers_list->stitch('views/box_manufacturers_list');
    }
    
    cache::end_capture($box_manufacturers_list_cache_id);
  }
?>
