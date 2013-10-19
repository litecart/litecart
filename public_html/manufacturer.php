<?php
  require_once('includes/app_header.inc.php');
  
  breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'), document::link('manufacturers.php'));
  
  if (empty($_GET['manufacturer_id'])) $_GET['manufacturer_id'] = 0;
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'popularity';
  
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars(document::link('', array(), array('manufacturer_id'))) .'" />';
  
  functions::draw_fancybox('a.fancybox');
  
  ob_start();
  echo '<aside class="shadow rounded-corners">' . PHP_EOL;
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'category_tree.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'manufacturers.inc.php');
  echo '</aside>' . PHP_EOL;
  document::$snippets['column_left'] = ob_get_clean();
  
  $manufacturers_query = database::query(
    "select m.id, m.status, m.name, m.keywords, m.image, mi.short_description, mi.description, mi.head_title, mi.meta_description, mi.meta_keywords, mi.h1_title, mi.link
    from ". DB_TABLE_MANUFACTURERS ." m
    left join ". DB_TABLE_MANUFACTURERS_INFO ." mi on (mi.manufacturer_id = m.id and mi.language_code = '". language::$selected['code'] ."')
    where status
    and m.id = '". (int)$_GET['manufacturer_id'] ."'
    limit 1;"
  );
  $manufacturer = database::fetch($manufacturers_query);
  
  if (empty($manufacturer['status'])) {
    notices::add('errors', language::translate('error_page_not_found', 'The requested page could not be found'));
    header('HTTP/1.1 404 Not Found');
    header('Location: '. document::link(WS_DIR_HTTP_HOME . 'manufacturers.php'));
    exit;
  }
  
  breadcrumbs::add($manufacturer['name'], $_SERVER['REQUEST_URI']);
  
  document::$snippets['title'][] = $manufacturer['head_title'] ? $manufacturer['head_title'] : $manufacturer['name'];
  document::$snippets['keywords'] = $manufacturer['meta_keywords'] ? $manufacturer['meta_keywords'] : $manufacturer['keywords'];
  document::$snippets['description'] = $manufacturer['meta_description'] ? $manufacturer['meta_description'] : $manufacturer['short_description'];
  

  $manufacturer_cache_id = cache::cache_id('box_manufacturer', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if (cache::capture($manufacturer_cache_id, 'file')) {
?>

<div class="box" id="box-manufacturer">
  <div class="heading">
    <span class="filter" style="float: right;">
<?php
    $sort_alternatives = array(
      'popularity' => language::translate('title_popularity', 'Popularity'),
      'name' => language::translate('title_name', 'Name'),
      'price' => language::translate('title_price', 'Price') ,
      'date' => language::translate('title_date', 'Date'),
    );
    
    $separator = false;
    foreach ($sort_alternatives as $key => $title) {
      if ($separator) echo ' ';
      if ($_GET['sort'] == $key) {
        echo '<span class="button active">'. $title .'</span>';
      } else {
        echo '<a class="button" href="'. document::href_link('', array('sort' => $key), true) .'">'. $title .'</a>';
      }
      $separator = true;
    }
?>
    </span>
    <h1><?php echo (!empty($manufacturer['image'])) ? '<img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 200, 60, 'FIT') .'" alt="'. (!empty($manufacturer['h1_title']) ? $manufacturer['h1_title'] : $manufacturer['name']) .'" title="'. (!empty($manufacturer['h1_title']) ? $manufacturer['h1_title'] : $manufacturer['name']) .'" />' : (!empty($manufacturer['h1_title']) ? $manufacturer['h1_title'] : $manufacturer['name']); ?></h1>
  </div>
  <div class="content">
<?php
    if ($_GET['page'] == 1) {
?>    
    <?php if ($manufacturer['description']) { ?>
    <div class="description-wrapper">
      <?php echo $manufacturer['description'] ? '<p class="manufacturer-description">'. $manufacturer['description'] .'</p>' : ''; ?>
    </div>
    <?php } ?>
<?php
    }
?>
    <ul class="listing-wrapper products">
<?php
    $products_query = functions::catalog_products_query(array('manufacturer_id' => $manufacturer['id'], 'sort' => $_GET['sort']));
    if (database::num_rows($products_query) > 0) {
      if ($_GET['page'] > 1) database::seek($products_query, (settings::get('items_per_page', 20) * ($_GET['page']-1)));
      
      $page_items = 0;
      while ($listing_item = database::fetch($products_query)) {
        echo functions::draw_listing_product($listing_item);
        
        if (++$page_items == settings::get('items_per_page', 20)) break;
      }
    }
?>
    </ul>
<?php
    echo functions::draw_pagination(ceil(database::num_rows($products_query)/settings::get('items_per_page', 20)));
?>
  </div>
</div>
<?php
    cache::end_capture($manufacturer_cache_id);
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>