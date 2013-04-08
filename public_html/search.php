<?php
  require_once('includes/app_header.inc.php');
  
  if (empty($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'occurences';
  
  $system->document->snippets['title'][] = empty($_GET['query']) ? $system->language->translate('title_search_results', 'Search Results') : sprintf($system->language->translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), $_GET['query']);
  //$system->document->snippets['keywords'] = '';
  //$system->document->snippets['description'] = '';
  
  $system->breadcrumbs->add($system->language->translate('title_search_results', 'Search Results'), $_SERVER['REQUEST_URI']);
  
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
  
?>
<div class="box" id="search-results">
  <div class="heading">
    <span class="filter" style="float: right;">
<?php
    $sort_alternatives = array(
      'popularity' => $system->language->translate('title_popularity', 'Popularity'),
      'name' => $system->language->translate('title_name', 'Name'),
      'price' => $system->language->translate('title_price', 'Price') ,
      'date' => $system->language->translate('title_date', 'Date'),
      'occurences' => $system->language->translate('title_occurences', 'Occurences'),
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
      <?php if ($_GET['query']) { ?>
    <h1 class="title"><?php echo sprintf($system->language->translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), $_GET['query']); ?></h1>
    <?php } ?>
  </div>
  <div class="content">
    <ul class="listing-wrapper">
<?php
    $categories_info_query = $system->database->query(
      "select c.id
      from ". DB_TABLE_CATEGORIES ." c, ". DB_TABLE_CATEGORIES_INFO ." ci
      where c.status
      and (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
      and (
        ci.name like '%". $system->database->input($_GET['query']) ."%'
        or ci.description like '%". $system->database->input($_GET['query']) ."%'
      );"
    );
    
    $sql_where_categories = array();
    while ($category = $system->database->fetch($categories_info_query)) {
      $sql_where_categories[] = "find_in_set('". (int)$category['id'] ."', p.categories)";
    }
    
    $manufacturers_info_query = $system->database->query(
      "select m.id
      from ". DB_TABLE_MANUFACTURERS ." m, ". DB_TABLE_MANUFACTURERS_INFO ." mi
      where m.status
      and (mi.manufacturer_id = m.id and mi.language_code = '". $system->language->selected['code'] ."')
      and (
        m.name like '%". $system->database->input($_GET['query']) ."%'
        or mi.description like '%". $system->database->input($_GET['query']) ."%'
      );"
    );
    
    $manufacturer_ids = array();
    while ($manufacturer = $system->database->fetch($manufacturers_info_query)) {
      $manufacturer_ids[] = $manufacturer['id'];
    }
    
    $products_query = $system->database->query(
      "select p.id
      from ". DB_TABLE_PRODUCTS ." p, ". DB_TABLE_PRODUCTS_INFO ." pi
      where p.status
      and (pi.product_id = p.id and pi.language_code = '". $system->language->selected['code'] ."')
      and (
        find_in_set('". $system->database->input($_GET['query']) ."', p.keywords)
        ". (!empty($sql_where_categories) ? "or " . implode(" or ", $sql_where_categories) : false) ."
        ". (!empty($manufacturer_ids) ? "or p.manufacturer_id in ('". implode("', '", $manufacturer_ids) . "')" : false) ."
        or (
          pi.name like '%". $system->database->input($_GET['query']) ."%'
          or pi.short_description like '%". $system->database->input($_GET['query']) ."%'
          or pi.description like '%". $system->database->input($_GET['query']) ."%'
        )
      );"
    );
    
    $product_ids = array();
    while ($product = $system->database->fetch($products_query)) {
      $product_ids[] = $product['id'];
    }
    
    $products_query = $system->functions->catalog_products_query(array('products' => $product_ids, 'sort' => $_GET['sort']));
    if ($system->database->num_rows($products_query) > 0) {
    
      if ($_GET['page'] > 1) $system->database->seek($products_query, ($system->settings->get('items_per_page') * ($_GET['page']-1)));
      
      $page_items = 0;
      while ($listing_product = $system->database->fetch($products_query)) {
        echo $system->functions->draw_listing_product($listing_product);
        
        if (++$page_items == $system->settings->get('items_per_page')) break;
      }
      
    } else {
      echo '<em>'. $system->language->translate('text_no_products_found_for_search_string', 'No products found for search string.') .'</em>' . PHP_EOL;
    }
?>
    </ul>
  </div>
<?php
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($products_query)/$system->settings->get('items_per_page')));
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>