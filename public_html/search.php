<?php
  require_once('includes/app_header.inc.php');
  
  if (empty($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'occurrences';
  
  document::$snippets['title'][] = empty($_GET['query']) ? language::translate('title_search_results', 'Search Results') : sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), $_GET['query']);
  //document::$snippets['keywords'] = '';
  //document::$snippets['description'] = '';
  
  breadcrumbs::add(language::translate('title_search_results', 'Search Results'), $_SERVER['REQUEST_URI']);
  
  functions::draw_fancybox("a.fancybox[data-fancybox-group='product-listing']");
  
  include(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');
?>
<div class="box" id="search-results">
  <div class="heading">
    <span class="filter" style="float: right;">
<?php
    $sort_alternatives = array(
      'popularity' => language::translate('title_popularity', 'Popularity'),
      'name' => language::translate('title_name', 'Name'),
      'price' => language::translate('title_price', 'Price') ,
      'date' => language::translate('title_date', 'Date'),
      'occurrences' => language::translate('title_occurrences', 'Occurrences'),
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
      <?php if ($_GET['query']) { ?>
    <h1 class="title"><?php echo sprintf(language::translate('title_search_results_for_s', 'Search Results for &quot;%s&quot;'), $_GET['query']); ?></h1>
    <?php } ?>
  </div>
  <div class="content">
    <ul class="listing-wrapper">
<?php
    $categories_info_query = database::query(
      "select c.id
      from ". DB_TABLE_CATEGORIES ." c, ". DB_TABLE_CATEGORIES_INFO ." ci
      where c.status
      and (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
      and (
        ci.name like '%". database::input($_GET['query']) ."%'
        or ci.description like '%". database::input($_GET['query']) ."%'
      );"
    );
    
    $sql_where_categories = array();
    while ($category = database::fetch($categories_info_query)) {
      $sql_where_categories[] = "find_in_set('". (int)$category['id'] ."', p.categories)";
    }
    
    $manufacturers_info_query = database::query(
      "select m.id
      from ". DB_TABLE_MANUFACTURERS ." m, ". DB_TABLE_MANUFACTURERS_INFO ." mi
      where m.status
      and (mi.manufacturer_id = m.id and mi.language_code = '". language::$selected['code'] ."')
      and (
        m.name like '%". database::input($_GET['query']) ."%'
        or mi.description like '%". database::input($_GET['query']) ."%'
      );"
    );
    
    $manufacturer_ids = array();
    while ($manufacturer = database::fetch($manufacturers_info_query)) {
      $manufacturer_ids[] = $manufacturer['id'];
    }
    
    $products_query = database::query(
      "select p.id
      from ". DB_TABLE_PRODUCTS ." p, ". DB_TABLE_PRODUCTS_INFO ." pi
      where p.status
      and (pi.product_id = p.id and pi.language_code = '". language::$selected['code'] ."')
      and (
        find_in_set('". database::input($_GET['query']) ."', p.keywords)
        ". (!empty($sql_where_categories) ? "or " . implode(" or ", $sql_where_categories) : false) ."
        ". (!empty($manufacturer_ids) ? "or p.manufacturer_id in ('". implode("', '", $manufacturer_ids) . "')" : false) ."
        or p.code like '%". database::input($_GET['query']) ."%'
        or p.sku like '%". database::input($_GET['query']) ."%'
        or p.upc like '%". database::input($_GET['query']) ."%'
        or pi.name like '%". database::input($_GET['query']) ."%'
        or pi.short_description like '%". database::input($_GET['query']) ."%'
        or pi.description like '%". database::input($_GET['query']) ."%'
      );"
    );
    
    $product_ids = array();
    while ($product = database::fetch($products_query)) {
      $product_ids[] = $product['id'];
    }
    
    $products_query = functions::catalog_products_query(array('products' => $product_ids, 'sort' => $_GET['sort']));
    if (database::num_rows($products_query) > 0) {
    
      if ($_GET['page'] > 1) database::seek($products_query, (settings::get('items_per_page') * ($_GET['page']-1)));
      
      $page_items = 0;
      while ($listing_product = database::fetch($products_query)) {
        echo functions::draw_listing_product_column($listing_product);
        
        if (++$page_items == settings::get('items_per_page')) break;
      }
      
    } else {
      echo '<em>'. language::translate('text_no_products_found_for_search_string', 'No products found for search string.') .'</em>' . PHP_EOL;
    }
?>
    </ul>
  </div>
<?php
  echo functions::draw_pagination(ceil(database::num_rows($products_query)/settings::get('items_per_page')));
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>