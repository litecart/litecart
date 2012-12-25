<?php
  require_once('../includes/app_header.inc.php');
  $system->document->viewport = 'ajax';
  
  header('Content-type: text/plain; charset='. $system->language->selected['charset']);
  
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'popularity';
  
  $category_cache_id = $system->cache->cache_id('ajax_products', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if ($system->cache->capture($category_cache_id, 'file')) {
    
    if (!empty($_GET['category_id'])) $_GET['categories'] = array($_GET['category_id']);
    if (!empty($_GET['designer_id'])) $_GET['designers'] = array($_GET['designer_id']);
    if (!empty($_GET['manufacturer_id'])) $_GET['manufacturers'] = array($_GET['manufacturer_id']);
    if (!empty($_GET['product_group'])) $_GET['product_groups'] = array($_GET['product_group']);
    
    $filter = array();
    if (!empty($_GET['categories'])) $filter['categories'] = $_GET['categories'];
    if (!empty($_GET['designers'])) $filter['designers'] = $_GET['designers'];
    if (!empty($_GET['manufacturers'])) $filter['manufacturers'] = $_GET['manufacturers'];
    if (!empty($_GET['product_groups'])) $filter['product_groups'] = $_GET['product_groups'];
    if (!empty($_GET['page'])) $filter['page'] = (int)$_GET['page'];
    if (!empty($_GET['sort'])) $filter['sort'] = $_GET['sort'];
    
    $products_query = $system->functions->catalog_products_query($filter);
    if ($system->database->num_rows($products_query) > 0 && $system->database->num_rows($products_query) > ($_GET['page']-1) * $system->settings->get('data_table_rows_per_page', 20)) {
      
      echo '<h3 style="clear: both; padding-top: 10px;">'. $system->language->translate('title_page', 'Page') .' '.  $_GET['page'] .'</h3>' . PHP_EOL;
    
      if ($_GET['page'] > 1) $system->database->seek($products_query, ($system->settings->get('data_table_rows_per_page', 20) * ($_GET['page']-1)));
      
      $page_items = 0;
      while ($listing_product = $system->database->fetch($products_query)) {
      
        echo $system->functions->draw_listing_product($listing_product);
        
        if (++$page_items == $system->settings->get('data_table_rows_per_page', 20)) break;
      }
    }
    
    $system->cache->end_capture($category_cache_id);
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>