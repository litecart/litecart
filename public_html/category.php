<?php
  require_once('includes/app_header.inc.php');
  
  if (empty($_GET['category_id'])) $_GET['category_id'] = 0;
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'popularity';
  
  $system->document->snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars($system->document->link('', array(), array('category_id'))) .'" />';
  
  $system->breadcrumbs->add($system->language->translate('title_categories', 'Categories'), $system->document->link('categories.php'));

  $categories_query = $system->database->query(
    "select c.id, c.status, c.image, c.keywords, ci.name, ci.description, ci.short_description, ci.head_title, ci.h1_title, ci.meta_description, ci.meta_keywords
    from ". DB_TABLE_CATEGORIES ." c
    left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
    where c.id = '". (int)$_GET['category_id'] ."'
    limit 1;"
  );
  $category = $system->database->fetch($categories_query);
  
  if (empty($category['status'])) {
    $system->notices->add('errors', $system->language->translate('error_page_not_found', 'The requested page could not be found'));
    header('HTTP/1.1 404 Not Found');
    header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'categories.php'));
    exit;
  }
  
  /*
  $system->document->snippets['javascript'][] = '  var nextPage = '. ($_GET['page']+1) .';'. PHP_EOL
                                              . '  var scrollInProgress = false;' . PHP_EOL
                                              . '  var endOfContent = false;' . PHP_EOL
                                              . '  $(window).scroll(function () {' . PHP_EOL
                                              . '    if ($(window).scrollTop() >= $(document).height() - $(window).height() - 400) {' . PHP_EOL
                                              . '      if (!scrollInProgress && !endOfContent) {' . PHP_EOL
                                              . '        scrollInProgress = true;' . PHP_EOL
                                              . '        var url = "'. $system->document->href_link('ajax/products.html.php', array('category_id' => $category['id'], 'sort' => $_GET['sort'], 'page' => 'nextPage')) .'";' . PHP_EOL
                                              . '        $.get(url.replace(/nextPage/g, nextPage), function(data) {' . PHP_EOL
                                              . '          if (data == "") {' . PHP_EOL
                                              . '            endOfContent = true;' . PHP_EOL
                                              . '            return;' . PHP_EOL
                                              . '          }' . PHP_EOL
                                              . '          if ($(".pagination").length) $(".pagination").remove();' . PHP_EOL
                                              . '          $(".listing-wrapper").append("<div id=\"page"+ nextPage +"-wrapper\" style=\"display: none;\">" + data + "</div>");' . PHP_EOL
                                              . '          $("#page"+ nextPage + "-wrapper").fadeIn();'. PHP_EOL
                                              . '          nextPage++;' . PHP_EOL
                                              . '          scrollInProgress = false;' . PHP_EOL
                                              . '        });' . PHP_EOL
                                              . '      }' . PHP_EOL
                                              . '    }' . PHP_EOL
                                              . '  });';
  */
  
  foreach ($system->functions->catalog_category_trail($category['id']) as $category_id => $category_name) {
    $system->breadcrumbs->add($category_name, $system->document->link(basename(__FILE__), array('category_id' => $category_id)));
  }
  
  $system->document->snippets['title'][] = $category['head_title'] ? $category['head_title'] : $category['name'];
  $system->document->snippets['keywords'] = $category['meta_keywords'] ? $category['meta_keywords'] : $category['keywords'];
  $system->document->snippets['description'] = $category['meta_description'] ? $category['meta_description'] : $category['short_description'];
  
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
  
  $category_cache_id = $system->cache->cache_id('box_category', array('basename', 'get', 'language', 'currency', 'account', 'prices'));
  if ($system->cache->capture($category_cache_id, 'file')) {
?>
<div class="box" id="box-category">
  <div class="heading">
    <nav class="filter" style="float: right;">
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
      $subcategories_query = $system->functions->catalog_categories_query($category['id']);
      while ($subcategory = $system->database->fetch($subcategories_query)) {
        echo $system->functions->draw_listing_category($subcategory);
      }
?>
    </ul>
<?php
    }
?>
    <ul class="listing-wrapper products">
<?php
    
    $products_query = $system->functions->catalog_products_query(array('category_id' => $category['id'], 'sort' => $_GET['sort']));
    if ($system->database->num_rows($products_query) > 0) {
      if ($_GET['page'] > 1) $system->database->seek($products_query, ($system->settings->get('items_per_page') * ($_GET['page']-1)));
      
      $page_items = 0;
      while ($listing_product = $system->database->fetch($products_query)) {
      
        echo $system->functions->draw_listing_product($listing_product);
        
        if (++$page_items == $system->settings->get('items_per_page')) break;
      }
    }
    

?>
    </ul>
<?php
    echo $system->functions->draw_pagination(ceil($system->database->num_rows($products_query)/$system->settings->get('items_per_page')));
?>
  </div>
</div>
<?php
    $system->cache->end_capture($category_cache_id);
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>