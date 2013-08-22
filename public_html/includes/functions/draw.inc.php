<?php
  
  function draw_img($image, $width, $height, $title, $params) {
    return '<img src="'. $image .'" width="'. $width .'" height="'. $height .'" border="0"'. ($params ? ' '.$params : '') .'/>';
  }
  
  function draw_listing_category($category) {
    
    $output = '<li class="category shadow hover-light">' . PHP_EOL
            . '  <a class="link" href="'. $GLOBALS['system']->document->href_link(WS_DIR_HTTP_HOME .'category.php', array('category_id' => $category['id'])) .'" title="'. htmlspecialchars($category['name']) .'">' . PHP_EOL
            . '    <div class="image" style="position: relative;">' . PHP_EOL
            . '    <img src="'. $GLOBALS['system']->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 340, 180, 'CROP') .'" width="340" height="180" alt="'. $category['name'] .'" />' . PHP_EOL
            . '      <div class="footer" style="position: absolute; bottom: 0;">' . PHP_EOL
            . '        <div class="title">'. $category['name'] .'</div>' . PHP_EOL
            . '        <div class="description">'. $category['short_description'] .'</div>' . PHP_EOL
            . '      </div>' . PHP_EOL
            . '    </div>' . PHP_EOL
            . '  </a>' . PHP_EOL
            . '</li>' . PHP_EOL;
    
    return $output;
  }

  function draw_listing_manufacturer($manufacturer) {
    
    $output = '<li class="manufacturer shadow hover-light">' . PHP_EOL
            . '  <a class="link" href="'. $GLOBALS['system']->document->href_link('manufacturer.php', array('manufacturer_id' => $manufacturer['id'])) .'" title="'. htmlspecialchars($manufacturer['name']) .'">' . PHP_EOL
            . '    <div class="image" style="position: relative;">' . PHP_EOL
            . '      <img src="'. $GLOBALS['system']->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 215, 60, 'FIT_ONLY_BIGGER_USE_WHITESPACING') .'" width="215" height="60" alt="'. $manufacturer['name'] .'" /><br />' . PHP_EOL
            . '    </div>' . PHP_EOL
            . '    <div class="title">'. $manufacturer['name'] .'</div>' . PHP_EOL
            . '  </a>' . PHP_EOL
            . '</li>' . PHP_EOL;
    
    return $output;
  }

  function draw_listing_product($product) {
    
    $sticker = '';
    if ($product['date_created'] > date('Y-m-d', strtotime('-1 month'))) {
      $sticker = '<img src="'. WS_DIR_IMAGES .'stickers/new.png" width="48" height="48" alt="" title="'. $GLOBALS['system']->language->translate('title_new', 'New') .'" style="position: absolute; top: 0px; left: 0px;" />';
    } else if ($product['campaign_price']) {
      $sticker = '<img src="'. WS_DIR_IMAGES .'stickers/sale.png" width="48" height="48" alt="" title="'. $GLOBALS['system']->language->translate('title_on_sale', 'On Sale') .'" style="position: absolute; top: 0px; left: 0px;" />';
    }
    
    $output = '<li class="product shadow hover-light" style="position: relative">' . PHP_EOL
            . '  <a class="link" href="'. $GLOBALS['system']->document->href_link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product['id']), array('category_id')) .'" title="'. htmlspecialchars($product['name']) .'">' . PHP_EOL
            . '    <div class="image" style="position: relative;">'. PHP_EOL
            . '      <img src="'. $GLOBALS['system']->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 150, 150, 'FIT_USE_WHITESPACING') .'" width="150" height="150" alt="'. htmlspecialchars($product['name']) .'" />' . PHP_EOL
            . '      ' . $sticker . PHP_EOL
            . '    </div>' . PHP_EOL
            . '    <div class="name">'. $product['name'] .'</div>' . PHP_EOL
            . '    <div class="manufacturer">'. (($product['manufacturer_name']) ? $product['manufacturer_name'] : '&nbsp;') .'</div>' . PHP_EOL
            . '    <div class="price-wrapper">'. ($product['campaign_price'] ? '<s class="regular-price">'. $GLOBALS['system']->currency->format($GLOBALS['system']->tax->calculate($product['price'], $product['tax_class_id'])) .'</s> <strong class="campaign-price">'. $GLOBALS['system']->currency->format($GLOBALS['system']->tax->calculate($product['campaign_price'], $product['tax_class_id'])) .'</strong>' : '<span class="price">'. $GLOBALS['system']->currency->format($GLOBALS['system']->tax->calculate($product['price'], $product['tax_class_id'])) .'</span>') .'</div>' . PHP_EOL
            . '  </a>' . PHP_EOL
            . (($product['image']) ? '  <a href="'. WS_DIR_IMAGES . $product['image'] .'" class="fancybox" data-fancybox-group="product-listing" title="'. htmlspecialchars($product['name']) .'"><img src="'. WS_DIR_IMAGES .'icons/16x16/preview.png" alt="" width="16" height="16" class="zoomable" style="position: absolute; top: 15px; right: 15px;" /></a>' . PHP_EOL : '')
          //. '  <div style="text-align: center;" class="buy_now">'.  $GLOBALS['system']->functions->form_draw_form_begin('buy_now_form') . $GLOBALS['system']->functions->form_draw_hidden_field('product_id', $product['id']) . $GLOBALS['system']->functions->form_draw_button('add_cart_product', $GLOBALS['system']->language->translate('title_add_to_cart', 'Add To Cart'), 'submit') . $GLOBALS['system']->functions->form_draw_form_end() .'</div>' . PHP_EOL
            . '</li>' . PHP_EOL;
    
    return $output;
  }
  
  function draw_fancybox($selector='a.fancybox', $params=array()) {
    
    $default_params = array(
      'hideOnContentClick' => true,
      'showCloseButton'    => true,
      'speedIn'            => 200,
      'speedOut'           => 200,
      'transitionIn'       => 'elastic',
      'transitionOut'      => 'elastic',
      'titlePosition'      => 'inside'
    );
    
    foreach (array_keys($default_params) as $key) {
      if (!isset($params[$key])) $params[$key] = $default_params[$key];
    }
    ksort($params);
    
    if (empty($GLOBALS['system']->document->snippets['head_tags']['fancybox'])) {
      $GLOBALS['system']->document->snippets['head_tags']['fancybox'] = '<script src="'. WS_DIR_EXT .'fancybox/jquery.fancybox-1.3.4.pack.js"></script>' . PHP_EOL
                                                           . '<link rel="stylesheet" href="{snippet:template_path}styles/fancybox.css" media="screen" />';
    }
    
    if (empty($selector)) {
      $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] = '  $(document).ready(function() {' . PHP_EOL
                                                                       . '    $.fancybox({' . PHP_EOL;
    } else {
      $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] = '  $(document).ready(function() {' . PHP_EOL
                                                                       . '    $("a").each(function() {' . PHP_EOL // HTML 5 fix for rel attribute
                                                                       . '      $(this).attr("rel", $(this).attr("data-fancybox-group"));' . PHP_EOL
                                                                       . '    }); ' . PHP_EOL
                                                                       . '    $("body").on("hover", "'. $selector .'", function() { ' . PHP_EOL // Fixes ajax content
                                                                       . '      $'. ($selector ? '("'. $selector .'")' : '') .'.fancybox({' . PHP_EOL;
    }
    
    foreach (array_keys($params) as $key) {
      if (strpos($params[$key], '(') !== false) {
        $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] .= '        "'. $key .'" : '. $params[$key] .',' . PHP_EOL;
      } else {
        switch (gettype($params[$key])) {
          case 'boolean':
            $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] .= '        "'. $key .'" : '. ($params[$key] ? 'true' : 'false') .',' . PHP_EOL;
            break;
          case 'integer':
            $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] .= '        "'. $key .'" : '. $params[$key] .',' . PHP_EOL;
            break;
          case 'string':
            $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] .= '        "'. $key .'" : "'. $params[$key] .'",' . PHP_EOL;
            break;
        }
      }
    }
    
    $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] = rtrim($GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector], ','.PHP_EOL) . PHP_EOL;

    if (empty($selector)) {
    $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] .= '    });' . PHP_EOL
                                                                      . '  });';
    } else {
      $GLOBALS['system']->document->snippets['javascript']['fancybox-'.$selector] .= '      });' . PHP_EOL
                                                                        . '    });' . PHP_EOL
                                                                        . '  });';
    }
  }
  
  function draw_pagination($pages) {
    
    $pages = ceil($pages);
  
    if ($pages < 2) return false;
    
    if ($_GET['page'] < 2) $_GET['page'] = 1;
    
    if ($_GET['page'] > 1) $GLOBALS['system']->document->snippets['head_tags']['prev'] = '<link rel="prev" href="'. htmlspecialchars($GLOBALS['system']->document->link('', array('page' => $_GET['page']-1), true)) .'" />';
    if ($_GET['page'] < $pages) $GLOBALS['system']->document->snippets['head_tags']['next'] = '<link rel="next" href="'. htmlspecialchars($GLOBALS['system']->document->link('', array('page' => $_GET['page']+1), true)) .'" />';
    if ($_GET['page'] < $pages) $GLOBALS['system']->document->snippets['head_tags']['prefetch'] = '<link rel="prerender" href="'. htmlspecialchars($GLOBALS['system']->document->link('', array('page' => $_GET['page']+1), true)) .'" />'; // Mozilla
    if ($_GET['page'] < $pages) $GLOBALS['system']->document->snippets['head_tags']['prerender'] = '<link rel="prerender" href="'. htmlspecialchars($GLOBALS['system']->document->link('', array('page' => $_GET['page']+1), true)) .'" />'; // Webkit
    
    $link = $_SERVER['REQUEST_URI'];
    $link = preg_replace('/page=[0-9]/', '', $link);
    
    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
    
    if (!strpos($link, '?')) $link = $link . '?';
    
    $html = '<nav class="pagination">'. PHP_EOL
          . '  <ul class="list-horizontal">' . PHP_EOL;
    
    if ($_GET['page'] > 1) {
      //$html .= '    <li><a class="page button" href="'. $GLOBALS['system']->document->href_link('', array('page' => 1), true) .'">'. $GLOBALS['system']->language->translate('title_first', 'First') .'</a></li>' . PHP_EOL;
      $html .= '    <li><a class="page button" href="'. $GLOBALS['system']->document->href_link('', array('page' => $_GET['page']-1), true) .'">'. $GLOBALS['system']->language->translate('title_previous', 'Previous') .'</a></li>' . PHP_EOL;
    } else {
      //$html .= '    <li><span class="page button disabled" href="'. $GLOBALS['system']->document->href_link('', array('page' => 1), true) .'">'. $GLOBALS['system']->language->translate('title_first', 'First') .'</span></li>' . PHP_EOL;
      $html .= '    <li><a class="page button disabled" href="'. $GLOBALS['system']->document->href_link('', array('page' => $_GET['page']-1), true) .'">'. $GLOBALS['system']->language->translate('title_previous', 'Previous') .'</a></li>' . PHP_EOL;
    }
    
    for ($i=1; $i<=$pages; $i++) {
      
      if ($i < $pages-5) {
        if ($i > 1 && $i < $_GET['page'] - 1 && $_GET['page'] > 4) {
          $rewind = round(($_GET['page']-1)/2);
          $html .= '    <li><a class="page button" href="'. $GLOBALS['system']->document->href_link('', array('page' => $rewind), true) .'">'. (($rewind == $_GET['page']-2) ? $rewind : '...') .'</a></li>' . PHP_EOL;
          $i = $_GET['page'] - 1;
          if ($i > $pages-4) $i = $pages-4;
        }
      }
      
      if ($i > 5) {  
        if ($i > $_GET['page'] + 1 && $i < $pages) {
          $forward = round(($_GET['page']+1+$pages)/2);
          $html .= '    <li><a class="page button" href="'. $GLOBALS['system']->document->href_link('', array('page' => $forward), true) .'">'. (($forward == $_GET['page']+2) ? $forward : '...') .'</a></li>' . PHP_EOL;
          $i = $pages;
        }
      }
    
      if ($i == $_GET['page']) {
        $html .= '    <li><span class="page button active">'. $i .'</span></li>' . PHP_EOL;
      } else {
        $html .= '    <li><a class="page button" href="'. $GLOBALS['system']->document->href_link('', array('page' => $i), true) .'">'. $i .'</a></li>' . PHP_EOL;
      }
    }
    
    if ($_GET['page'] < $pages) {
      $html .= '    <li><a class="page button" href="'. $GLOBALS['system']->document->href_link('', array('page' => $_GET['page']+1), true) .'">'. $GLOBALS['system']->language->translate('title_next', 'Next') .'</a></li>' . PHP_EOL;
      //$html .= '    <li><a class="page button" href="'. $GLOBALS['system']->document->href_link('', array('page' => $pages), true) .'">'. $GLOBALS['system']->language->translate('title_last', 'Last') .'</a></li>' . PHP_EOL;
    } else {
      $html .= '    <li><span class="page button disabled">'. $GLOBALS['system']->language->translate('title_next', 'Next') .'</span></li>' . PHP_EOL;
      //$html .= '    <li><span class="page button disabled">'. $GLOBALS['system']->language->translate('title_last', 'Last') .'</span></li>' . PHP_EOL;
    }
    
    $html .= '  </ul>'
           . '</nav>';
    
    return $html;
  }
  
?>