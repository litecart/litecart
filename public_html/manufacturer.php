<?php
  require_once('includes/app_header.inc.php');
  
  $system->breadcrumbs->add($system->language->translate('title_manufacturers', 'Manufacturers'), $system->document->link('manufacturers.php'));
  
  if (empty($_GET['manufacturer_id'])) $_GET['manufacturer_id'] = 0;
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'popularity';
  
  $system->document->snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars($system->document->link('', array(), array('manufacturer_id'))) .'" />';
  
  $system->functions->draw_fancybox('a.fancybox');
  
  ob_start();
  echo '<div id="sidebar" class="shadow rounded-corners">' . PHP_EOL;
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'search.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'category_tree.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'manufacturers.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'account.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'login.inc.php');
  echo '</div>' . PHP_EOL;
  $system->document->snippets['column_left'] = ob_get_clean();
  
  $manufacturers_query = $system->database->query(
    "select m.id, m.name, m.keywords, mi.short_description, mi.description, mi.head_title, mi.meta_description, mi.meta_keywords, mi.link
    from ". DB_TABLE_MANUFACTURERS ." m
    left join ". DB_TABLE_MANUFACTURERS_INFO ." mi on (mi.manufacturer_id = m.id and mi.language_code = '". $system->language->selected['code'] ."')
    where status
    and m.id = '". (int)$_GET['manufacturer_id'] ."'
    limit 1;"
  );
  $manufacturer = $system->database->fetch($manufacturers_query);
  
  if (empty($manufacturer)) {
    $system->notices->add('errors', $system->language->translate('error_page_not_found', 'The requested page could not be found'));
    header('Location: HTTP/1.1 404 Not Found');
    header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'manufacturers.php'));
    exit;
  }
  
  $system->breadcrumbs->add($manufacturer['name'], $_SERVER['REQUEST_URI']);
  
  $system->document->snippets['title'][] = $manufacturer['head_title'] ? $manufacturer['head_title'] : $manufacturer['name'];
  $system->document->snippets['keywords'] = $manufacturer['meta_keywords'] ? $manufacturer['meta_keywords'] : $manufacturer['keywords'];
  $system->document->snippets['description'] = $manufacturer['meta_description'] ? $manufacturer['meta_description'] : $manufacturer['short_description'];
  

  $manufacturer_cache_id = $system->cache->cache_id('box_manufacturer', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if ($system->cache->capture($manufacturer_cache_id, 'file')) {
?>

  <div class="box" id="box-manufacturer">
    <div class="heading">
      <span class="filter" style="float: right;">
<?php
    $sort_alternatives = array(
      'popularity' => $system->language->translate('title_popularity', 'Popularity'),
      'name' => $system->language->translate('title_name', 'Name'),
      'price' => $system->language->translate('title_price', 'Price') ,
      'date' => $system->language->translate('title_date', 'Date'),
    );
    
    $separator = false;
    foreach ($sort_alternatives as $key => $title) {
      if ($separator) echo ' ';
      if ($_GET['sort'] == $key) {
        echo '<span class="button active">'. $title .'</span>';
      } else {
        echo '<a class="button" href="'. $system->document->href_link('', array('sort' => $key), true) .'">'. $title .'</a>';
      }
      $separator = true;
    }
?>
      </span>
      <h1><?php echo $manufacturer['name']; ?></h1>
    </div>
    <div class="content">
      <?php if ($_GET['page'] == 1) { ?>
      <?php if ($manufacturer['description']) { ?><div class="manufacturer-description"><?php echo $manufacturer['description'] ?></div><?php } ?>
      <?php } ?>
      <ul class="listing-wrapper products">
<?php
    $products_query = $system->functions->catalog_products_query(array('manufacturer_id' => $manufacturer['id'], 'sort' => $_GET['sort']));
    if ($system->database->num_rows($products_query) > 0) {
      if ($_GET['page'] > 1) $system->database->seek($products_query, ($system->settings->get('data_table_rows_per_page', 20) * ($_GET['page']-1)));
      
      $page_items = 0;
      while ($listing_item = $system->database->fetch($products_query)) {
        echo $system->functions->draw_listing_product($listing_item);
        
        if (++$page_items == $system->settings->get('data_table_rows_per_page', 20)) break;
      }
    }
?>
      </ul>
    </div>

<?php
    echo $system->functions->draw_pagination(ceil($system->database->num_rows($products_query)/$system->settings->get('data_table_rows_per_page', 20)));
  
    $system->cache->end_capture($manufacturer_cache_id);
  }
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>