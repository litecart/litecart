<?php
  require_once('includes/app_header.inc.php');
  
  $system->breadcrumbs->add($system->language->translate('title_designers', 'Designers'), $system->document->link('designers.php'));
  
  if (empty($_GET['designer_id'])) $_GET['designer_id'] = 0;
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'popularity';
  
  $system->document->snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars($system->document->link('', array(), array('designer_id'))) .'" />';

  $designers_query = $system->database->query(
    "select d.id, d.name, di.short_description, di.description, di.keywords, di.head_title, di.meta_description, di.meta_keywords, di.link
    from ". DB_TABLE_DESIGNERS ." d
    left join ". DB_TABLE_DESIGNERS_INFO ." di on (di.designer_id = d.id and di.language_code = '". $system->language->selected['code'] ."')
    where status
    and d.id = '". (int)$_GET['designer_id'] ."'
    limit 1;"
  );
  $designer = $system->database->fetch($designers_query);
  
  if (empty($designer)) {
    header('HTTP/1.0 410 Gone');
    die('Error: Designer could not be found');
  }
  
  $system->breadcrumbs->add($designer['name'], $_SERVER['REQUEST_URI']);
  
  $system->document->snippets['title'][] = $designer['head_title'] ? $designer['head_title'] : $designer['name'];
  $system->document->snippets['keywords'] = $designer['meta_keywords'] ? $designer['meta_keywords'] : $designer['keywords'];
  $system->document->snippets['description'] = $designer['meta_description'] ? $designer['meta_description'] : $designer['short_description'];
  
  if (empty($system->document->snippets['head_tags']['fancybox'])) {
    $system->document->snippets['head_tags']['fancybox'] = '<script type="text/javascript" src="'. WS_DIR_EXT .'fancybox/jquery.fancybox-1.3.4.pack.js"></script>' . PHP_EOL
                                                         . '<link rel="stylesheet" href="'. WS_DIR_EXT .'fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />';
  }
  
  if (empty($system->document->snippets['javascript']['fancybox'])) {
    $system->document->snippets['javascript']['fancybox'] = '  $(document).ready(function() {' . PHP_EOL
                                                          . '      $("a.fancybox").fancybox({' . PHP_EOL
                                                          . '        "transitionIn"  : "elastic",' . PHP_EOL
                                                          . '        "transitionOut" : "elastic",' . PHP_EOL
                                                          . '        "speedIn"       : 600,' . PHP_EOL
                                                          . '        "speedOut"      : 200,' . PHP_EOL
                                                          . '        "overlayShow"   : false,' . PHP_EOL
                                                          . '        "titlePosition" : "inside",' . PHP_EOL
                                                          . '      });' . PHP_EOL
                                                          . '  });';
  }
  
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
    echo ($_GET['sort'] == $key) ? $title : '<a href="'. $system->document->link('', array('sort' => $key), true) .'">'. $title .'</a>';
    $separator = true;
  }
?>
      </span>
      <h1 style="margin: 0; font: inherit;"><?php echo $designer['name']; ?></h1>
    </div>
    <div class="content listing-wrapper">
      <?php if ($designer['description']) { ?>
      <div class="designer-description"><?php echo $designer['description'] ?></div>
      <?php } ?>
<?php
  $system->document->snippets['head_tags']['fancybox'] = '<script type="text/javascript" src="ext/fancybox/jquery.fancybox-1.3.4.pack.js"></script>' . PHP_EOL
                                                        . '<link rel="stylesheet" href="ext/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />' . PHP_EOL;
                                                        
  $system->document->snippets['javascript'][] = '  $(document).ready(function() {' . PHP_EOL
                                               . '    $("a.fancybox").fancybox({' . PHP_EOL
                                               . '      "transitionIn"	:	"elastic",' . PHP_EOL
                                               . '      "transitionOut"	:	"elastic",' . PHP_EOL
                                               . '      "speedIn"		:	600,' . PHP_EOL
                                               . '      "speedOut"		:	200,' . PHP_EOL
                                               . '      "overlayShow"	:	false,' . PHP_EOL
                                               . '      "titlePosition" : "inside"' . PHP_EOL
                                               . '    });' . PHP_EOL
                                               . '  });' . PHP_EOL;
  
  $products_query = $system->functions->catalog_products_query(array('designer_id' => $designer['id'], 'sort' => $_GET['sort']));
  if ($system->database->num_rows($products_query) > 0) {
    if ($_GET['page'] > 1) $system->database->seek($products_query, ($system->settings->get('data_table_rows_per_page', 20) * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($listing_item = $system->database->fetch($products_query)) {
      echo $system->functions->draw_listing_product($listing_item);
      
      if (++$page_items == $system->settings->get('data_table_rows_per_page', 20)) break;
    }
    
  } else {
  
    echo '<p><em>'. $system->language->translate('text_no_products_for_designer', 'There are currently no products by this designer in stock.') .'</em></p>';
  }
?>
    </div>

<?php
    echo $system->functions->draw_pagination(ceil($system->database->num_rows($products_query)/$system->settings->get('data_table_rows_per_page', 20)));
  
    $system->cache->end_capture($designer_cache_id);
  }
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>