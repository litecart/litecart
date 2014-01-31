<?php
  require_once('includes/app_header.inc.php');
  
  if (empty($_GET['category_id'])) {
    header('Location: '. document::link(WS_DIR_HTTP_HOME . 'categories.php'));
    exit;
  }
  
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'popularity';
  
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars(document::link('', array(), array('category_id'))) .'" />';
  
  breadcrumbs::add(language::translate('title_categories', 'Categories'), document::link('categories.php'));
  
  $category = new ref_category($_GET['category_id']);
  
  if (empty($category->status)) {
    notices::add('errors', language::translate('error_page_not_found', 'The requested page could not be found'));
    header('HTTP/1.1 404 Not Found');
    header('Location: '. document::link(WS_DIR_HTTP_HOME . 'categories.php'));
    exit;
  }
  
  foreach (functions::catalog_category_trail($category->id) as $category_id => $category_name) {
    breadcrumbs::add($category_name, document::link(basename(__FILE__), array('category_id' => $category_id)));
  }
  
  document::$snippets['title'][] = $category->head_title[language::$selected['code']] ? $category->head_title[language::$selected['code']] : $category->name[language::$selected['code']];
  document::$snippets['keywords'] = $category->meta_keywords[language::$selected['code']] ? $category->meta_keywords[language::$selected['code']] : $category->keywords;
  document::$snippets['description'] = $category->meta_description[language::$selected['code']] ? $category->meta_description[language::$selected['code']] : $category->short_description[language::$selected['code']];
  
  functions::draw_fancybox("a.fancybox[data-fancybox-group='product-listing']");
  
  include(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');
  
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
    <h1><?php echo $category->h1_title[language::$selected['code']] ? $category->h1_title[language::$selected['code']] : $category->name[language::$selected['code']]; ?></h1>
  </div>
  <div class="content">
<?php
    if ($_GET['page'] == 1) {
?>    
    <?php if ($category->description) { ?>
    <div class="description-wrapper">
      <?php echo $category->description[language::$selected['code']] ? '<p class="category-description">'. $category->description[language::$selected['code']] .'</p>' : false; ?>
    </div>
    <?php } ?>
    
<?php
      $subcategories_query = functions::catalog_categories_query($category->id);
      if (database::num_rows($subcategories_query)) {
        echo '<ul class="listing-wrapper categories">' . PHP_EOL;
        while ($subcategory = database::fetch($subcategories_query)) {
          echo functions::draw_listing_category($subcategory);
        }
        echo '</ul>' . PHP_EOL;
      }
?>
    
<?php
    }
    
    switch ($category->list_style) {
      case 'rows':
        $items_per_page = 10;
        break;
      case 'columns':
      default:
        $items_per_page = settings::get('items_per_page');
        break;
    }
    
    $products_query = functions::catalog_products_query(
      array(
        'category_id' => $category->id,
        'manufacturers' => !empty($_GET['manufacturers']) ? $_GET['manufacturers'] : null,
        'product_groups' => !empty($_GET['product_groups']) ? $_GET['product_groups'] : null,
        'sort' => $_GET['sort']
      )
    );
    
    if (database::num_rows($products_query)) {
      echo '<ul class="listing-wrapper products">' . PHP_EOL;
      
      if ($_GET['page'] > 1) database::seek($products_query, $items_per_page * ($_GET['page'] - 1));
      
      $page_items = 0;
      while ($listing_product = database::fetch($products_query)) {
        switch ($category->list_style) {
          case 'rows':
            echo functions::draw_listing_product_row($listing_product);
            break;
          case 'columns':
          default:
            echo functions::draw_listing_product_column($listing_product);
            break;
        }
        if (++$page_items == $items_per_page) break;
      }
    }
    
    echo '    </ul>' . PHP_EOL;
    
    echo functions::draw_pagination(ceil(database::num_rows($products_query)/$items_per_page));
?>
  </div>
</div>
<?php
    cache::end_capture($category_cache_id);
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>