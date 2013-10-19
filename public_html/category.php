<?php
  require_once('includes/app_header.inc.php');
  
  if (empty($_GET['category_id'])) $_GET['category_id'] = 0;
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'popularity';
  
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars(document::link('', array(), array('category_id'))) .'" />';
  
  breadcrumbs::add(language::translate('title_categories', 'Categories'), document::link('categories.php'));

  $categories_query = database::query(
    "select c.id, c.status, c.image, c.keywords, ci.name, ci.description, ci.short_description, ci.head_title, ci.h1_title, ci.meta_description, ci.meta_keywords
    from ". DB_TABLE_CATEGORIES ." c
    left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
    where c.id = '". (int)$_GET['category_id'] ."'
    limit 1;"
  );
  $category = database::fetch($categories_query);
  
  if (empty($category['status'])) {
    notices::add('errors', language::translate('error_page_not_found', 'The requested page could not be found'));
    header('HTTP/1.1 404 Not Found');
    header('Location: '. document::link(WS_DIR_HTTP_HOME . 'categories.php'));
    exit;
  }
  
  foreach (functions::catalog_category_trail($category['id']) as $category_id => $category_name) {
    breadcrumbs::add($category_name, document::link(basename(__FILE__), array('category_id' => $category_id)));
  }
  
  document::$snippets['title'][] = $category['head_title'] ? $category['head_title'] : $category['name'];
  document::$snippets['keywords'] = $category['meta_keywords'] ? $category['meta_keywords'] : $category['keywords'];
  document::$snippets['description'] = $category['meta_description'] ? $category['meta_description'] : $category['short_description'];
  
  functions::draw_fancybox('a.fancybox');
  
  ob_start();
  echo '<aside class="shadow rounded-corners">' . PHP_EOL;
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'category_tree.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'manufacturers.inc.php');
  echo '</aside>' . PHP_EOL;
  document::$snippets['column_left'] = ob_get_clean();
  
  $category_cache_id = cache::cache_id('box_category', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if (cache::capture($category_cache_id, 'file')) {
?>
<div class="box" id="box-category">
  <div class="heading">
    <nav class="filter" style="float: right;">
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
      </nav>
    <h1><?php echo $category['h1_title'] ? $category['h1_title'] : $category['name']; ?></h1>
  </div>
  <div class="content">
<?php
    if ($_GET['page'] == 1) {
?>    
    <?php if ($category['description']) { ?>
    <div class="description-wrapper">
      <?php echo $category['description'] ? '<p class="category-description">'. $category['description'] .'</p>' : false; ?>
    </div>
    <?php } ?>
    
    <ul class="listing-wrapper categories">
<?php
      $subcategories_query = functions::catalog_categories_query($category['id']);
      while ($subcategory = database::fetch($subcategories_query)) {
        echo functions::draw_listing_category($subcategory);
      }
?>
    </ul>
<?php
    }
?>
    <ul class="listing-wrapper products">
<?php
    $products_query = functions::catalog_products_query(array('category_id' => $category['id'], 'sort' => $_GET['sort']));
    if (database::num_rows($products_query) > 0) {
      if ($_GET['page'] > 1) database::seek($products_query, (settings::get('items_per_page') * ($_GET['page']-1)));
      
      $page_items = 0;
      while ($listing_product = database::fetch($products_query)) {
      
        echo functions::draw_listing_product($listing_product);
        
        if (++$page_items == settings::get('items_per_page')) break;
      }
    }
?>
    </ul>
<?php
    echo functions::draw_pagination(ceil(database::num_rows($products_query)/settings::get('items_per_page')));
?>
  </div>
</div>
<?php
    cache::end_capture($category_cache_id);
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>