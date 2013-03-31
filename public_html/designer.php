<?php
  require_once('includes/app_header.inc.php');
  
  $system->breadcrumbs->add($system->language->translate('title_designers', 'Designers'), $system->document->link('designers.php'));
  
  if (empty($_GET['designer_id'])) $_GET['designer_id'] = 0;
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'popularity';
  
  $system->document->snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars($system->document->link('', array(), array('designer_id'))) .'" />';

  $designers_query = $system->database->query(
    "select d.id, d.status, d.name, d.keywords, di.short_description, di.description, di.head_title, di.h1_title, di.meta_description, di.meta_keywords, di.link
    from ". DB_TABLE_DESIGNERS ." d
    left join ". DB_TABLE_DESIGNERS_INFO ." di on (di.designer_id = d.id and di.language_code = '". $system->language->selected['code'] ."')
    where status
    and d.id = '". (int)$_GET['designer_id'] ."'
    limit 1;"
  );
  $designer = $system->database->fetch($designers_query);
  
  if (empty($designer['status'])) {
    $system->notices->add('errors', $system->language->translate('error_page_not_found', 'The requested page could not be found'));
    header('HTTP/1.1 404 Not Found');
    header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'designers.php'));
    exit;
  }
  
  $system->breadcrumbs->add($designer['name'], $_SERVER['REQUEST_URI']);
  
  $system->document->snippets['title'][] = $designer['head_title'] ? $designer['head_title'] : $designer['name'];
  $system->document->snippets['keywords'] = $designer['meta_keywords'] ? $designer['meta_keywords'] : $designer['keywords'];
  $system->document->snippets['description'] = $designer['meta_description'] ? $designer['meta_description'] : $designer['short_description'];
  
  $system->functions->draw_fancybox('a.fancybox');
  
  $designer_cache_id = $system->cache->cache_id('box_designer', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if ($system->cache->capture($designer_cache_id, 'file')) {
?>

  <div class="box" id="box-designer">
    <div class="heading">
      <span style="float: right; font-weight: normal;">
<?php
  $sort_alternatives = array(
    'popularity' => $system->language->translate('title_popularity', 'Popularity'),
    'name' => $system->language->translate('title_name', 'Name'),
    'price' => $system->language->translate('title_price', 'Price') ,
    'date' => $system->language->translate('title_date', 'Date'),
  );
   
  echo '<strong>'. $system->language->translate('title_sort_by', 'Sort By') .':</strong> ';
  $separator = false;
  foreach ($sort_alternatives as $key => $title) {
    if ($separator) echo ' | ';
    echo ($_GET['sort'] == $key) ? $title : '<a href="'. $system->document->href_link('', array('sort' => $key), true) .'">'. $title .'</a>';
    $separator = true;
  }
?>
      </span>
      <h1><?php echo (!empty($designer['image'])) ? '<img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $designer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 200, 60, 'FIT') .'" alt="'. $designer['name'] .'" title="'. $designer['name'] .'" />' : $designer['name']; ?></h1>
    </div>
    <div class="content">
<?php
    if ($_GET['page'] == 1) {
?>    
      <?php if ($designer['description']) { ?>
      <div class="description-wrapper">
        <?php echo $designer['description'] ? $designer['description'] : ''; ?>
      </div>
      <?php } ?>
<?php
    }
?>
      <ul class="listing-wrapper products">
<?php
  
  $products_query = $system->functions->catalog_products_query(array('designer_id' => $designer['id'], 'sort' => $_GET['sort']));
  if ($system->database->num_rows($products_query) > 0) {
    if ($_GET['page'] > 1) $system->database->seek($products_query, ($system->settings->get('items_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($listing_item = $system->database->fetch($products_query)) {
      echo $system->functions->draw_listing_product($listing_item);
      
      if (++$page_items == $system->settings->get('items_per_page')) break;
    }
  }
?>
      </ul>
    </div>

<?php
    echo $system->functions->draw_pagination(ceil($system->database->num_rows($products_query)/$system->settings->get('items_per_page')));
  
    $system->cache->end_capture($designer_cache_id);
  }
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>