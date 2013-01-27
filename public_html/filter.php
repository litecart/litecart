<?php
  require_once('includes/app_header.inc.php');
  
  if (empty($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'date';
  
  $system->document->snippets['title'][] = $system->language->translate('filter.php:head_title');
  $system->document->snippets['keywords'] = $system->language->translate('filter.php:keywords');
  $system->document->snippets['description'] = $system->language->translate('filter.php:meta_description');
  
  $system->breadcrumbs->add($system->language->translate('title_products', 'Products'), $_SERVER['REQUEST_URI']);
  
  $system->functions->draw_fancybox('a.fancybox');
  
  $system->document->snippets['javascript'][] = '  var nextPage = '. ($_GET['page']+1) .';'. PHP_EOL
                                              . '  var scrollInProgress = false;' . PHP_EOL
                                              . '  var endOfContent = false;' . PHP_EOL
                                              //. '  $(window).scroll(function () {' . PHP_EOL
                                              . '  $("#listing-append").live("click", function () {' . PHP_EOL
                                              //. '    if ($(window).scrollTop() >= $(document).height() - $(window).height() - 25) {' . PHP_EOL
                                              . '      if (!scrollInProgress && !endOfContent) {' . PHP_EOL
                                              . '        $("body").css("cursor", "wait");' . PHP_EOL
                                              . '        scrollInProgress = true;' . PHP_EOL
                                              . '        var url = "'. $system->document->link('ajax/products.html.php', array('manufacturers' => isset($_GET['manufacturers']) ? $_GET['manufacturers'] : null, 'product_groups' => isset($_GET['product_groups']) ? $_GET['product_groups'] : null, 'sort' => $_GET['sort'], 'page' => 'nextPage')) .'";' . PHP_EOL
                                              . '        $.get(url.replace(/nextPage/g, nextPage), function(data) {' . PHP_EOL
                                              . '          if (data.replace(/^\s\s*/, "") == "") {' . PHP_EOL
                                              . '            endOfContent = true;' . PHP_EOL
                                              . '            $(".pagination").remove();' . PHP_EOL
                                              . '            $("body").css("cursor", "");' . PHP_EOL
                                              . '            return;' . PHP_EOL
                                              . '          }' . PHP_EOL
                                              //. '          if ($(".pagination").length) $(".pagination").remove();' . PHP_EOL
                                              . '          if ($(".pagination").length) $(".pagination").html("<a href=\"#\" id=\"listing-append\" class=\"page\">'. sprintf($system->language->translate('text_show_d_more_products', 'Show %d more products'), $system->settings->get('data_table_rows_per_page')) .'</a>")' . PHP_EOL
                                              . '          $(".listing-wrapper").append("<div id=\"page"+ nextPage +"-wrapper\" class=\"appended-page\" style=\"display: none;\">" + data + "</div>");' . PHP_EOL
                                              . '          $("#page"+ nextPage + "-wrapper").fadeIn();'. PHP_EOL
                                              . '          $("body").css("cursor", "");' . PHP_EOL
                                              . '          nextPage++;' . PHP_EOL
                                              . '          scrollInProgress = false;' . PHP_EOL
                                              . '        });' . PHP_EOL
                                              . '      }' . PHP_EOL
                                              //. '    }' . PHP_EOL
                                              . '    return false;' . PHP_EOL
                                              . '  });' . PHP_EOL
                                              . '  ' . PHP_EOL
                                              . '  $(document).ready(function() {' . PHP_EOL
                                              . '    if ($(".pagination").length) $(".pagination").html("<a href=\"#\" id=\"listing-append\" class=\"page\">'. sprintf($system->language->translate('text_show_d_more_products', 'Show %d more products'), $system->settings->get('data_table_rows_per_page')) .'</a>");' .PHP_EOL
                                              . '  })' . PHP_EOL;
  
  ob_start();
  echo '<div id="sidebar" class="shadow rounded-corners">' . PHP_EOL;
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'product_filter.inc.php');
  echo '</div>' . PHP_EOL;
  $system->document->snippets['column_left'] = ob_get_clean();
  
?>
<div class="box" id="box-products">
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
        echo '<a class="button" href="'. $system->document->link('', array('sort' => $key), true) .'">'. $title .'</a>';
      }
      $separator = true;
    }
?>
      </span>  
    <h1 class="title"><?php echo $system->language->translate('title_products', 'Products'); ?></h1>
  </div>
  <div class="content">
    <div class="listing-wrapper">
<?php
    $products_query = $system->functions->catalog_products_query(
      array(
        'categories' => isset($_GET['categories']) ? $_GET['categories'] : null,
        'product_groups' => isset($_GET['product_groups']) ? $_GET['product_groups'] : null,
        'manufacturers' => isset($_GET['manufacturers']) ? $_GET['manufacturers'] : null,
        'sort' => $_GET['sort']
      )
    );
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
    </div>
  </div>
  
<?php
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($products_query)/$system->settings->get('items_per_page')));
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>