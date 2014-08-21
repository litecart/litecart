<?php

  function draw_img($image, $width, $height, $title, $params) {
    return '<img src="'. $image .'" width="'. $width .'" height="'. $height .'" border="0"'. ($params ? ' '.$params : '') .'/>';
  }
  
  function draw_listing_category($category) {
    
    $list_item = new view();
    
    $list_item->snippets = array(
      'name' => $category['name'],
      'link' => document::ilink('category', array('category_id' => $category['id'])),
      'image' => functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 340, 180, 'CROP'),
      'short_description' => $category['short_description'],
    );
    
    return $list_item->stitch('listing_category');
  }
  
  function draw_listing_product_column($product) {
    trigger_error('The function draw_listing_product_column() is deprecated, use instead draw_listing_product()', E_USER_DEPRECATED);
    return functions::draw_listing_product($product, 'column');
  }
  
  function draw_listing_product_row($product) {
    trigger_error('The function draw_listing_product_row() is deprecated, use instead draw_listing_product()', E_USER_DEPRECATED);
    return functions::draw_listing_product($product, 'row');
  }
  
  function draw_listing_product($product, $listing_type='column') {
    
    $list_item = new view();
    
    $sticker = '';
    if ($product['campaign_price']) {
      $sticker = '<img src="'. WS_DIR_IMAGES .'stickers/sale.png" width="48" height="48" alt="" title="'. language::translate('title_on_sale', 'On Sale') .'" class="sticker" />';
    } else if ($product['date_created'] > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
      $sticker = '<img src="'. WS_DIR_IMAGES .'stickers/new.png" width="48" height="48" alt="" title="'. language::translate('title_new', 'New') .'" class="sticker" />';
    }
    
    $list_item->snippets = array(
      'listing_type' => $listing_type,
      'name' => $product['name'],
      'link' => document::ilink('product', array('product_id' => $product['id']), array('category_id')),
      'image' => $product['image'] ? WS_DIR_IMAGES . $product['image'] : '',
      'thumbnail' => functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 640, 640, 'FIT_USE_WHITESPACING'),
      'sticker' => $sticker,
      'manufacturer_name' => $product['manufacturer_name'],
      'short_description' => $product['short_description'],
      'price' => currency::format(tax::calculate($product['price'], $product['tax_class_id'])),
      'campaign_price' => $product['campaign_price'] ? currency::format(tax::calculate($product['campaign_price'], $product['tax_class_id'])) : null,
      'preview_icon' => WS_DIR_IMAGES .'icons/16x16/preview.png',
    );
    
    return $list_item->stitch('listing_product');
  }
  
  function draw_fancybox($selector='a.fancybox', $params=array()) {
    
    $default_params = array(
      'hideOnContentClick' => true,
      'padding'            => 20,
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
    
    if (empty(document::$snippets['head_tags']['fancybox'])) {
      document::$snippets['head_tags']['fancybox'] = '<script src="'. WS_DIR_EXT .'fancybox/jquery.fancybox-1.3.4.pack.js"></script>' . PHP_EOL
                                                   . '<link rel="stylesheet" href="{snippet:template_path}styles/fancybox.css" media="screen" />';
    }
    
    if (empty($selector)) {
      document::$snippets['javascript']['fancybox-'.$selector] = '  $(document).ready(function() {' . PHP_EOL
                                                               . '    $.fancybox({' . PHP_EOL;
    } else {
      document::$snippets['javascript']['fancybox-'.$selector] = '  $(document).ready(function() {' . PHP_EOL
                                                               . '    $("a").each(function() {' . PHP_EOL // HTML 5 fix for rel attribute
                                                               . '      $(this).attr("rel", $(this).attr("data-fancybox-group"));' . PHP_EOL
                                                               . '    }); ' . PHP_EOL
                                                               . '    $("body").on("hover", "'. $selector .'", function() { ' . PHP_EOL // Fixes ajax content
                                                               . '      $'. ($selector ? '("'. $selector .'")' : '') .'.fancybox({' . PHP_EOL;
    }
    
    foreach (array_keys($params) as $key) {
      if (strpos($params[$key], '(') !== false) {
        document::$snippets['javascript']['fancybox-'.$selector] .= '        "'. $key .'" : '. $params[$key] .',' . PHP_EOL;
      } else {
        switch (gettype($params[$key])) {
          case 'boolean':
            document::$snippets['javascript']['fancybox-'.$selector] .=
            '        "'. $key .'" : '.
            ($params[$key] ? 'true' : 'false') .',' . PHP_EOL;
            break;
          case 'integer':
            document::$snippets['javascript']['fancybox-'.$selector] .= '        "'. $key .'" : '. $params[$key] .',' . PHP_EOL;
            break;
          case 'string':
            document::$snippets['javascript']['fancybox-'.$selector] .= '        "'. $key .'" : "'. $params[$key] .'",' . PHP_EOL;
            break;
        }
      }
    }
    
    document::$snippets['javascript']['fancybox-'.$selector] = rtrim(document::$snippets['javascript']['fancybox-'.$selector], ','.PHP_EOL) . PHP_EOL;

    if (empty($selector)) {
      document::$snippets['javascript']['fancybox-'.$selector] .= '    });' . PHP_EOL
                                                                . '  });';
    } else {
      document::$snippets['javascript']['fancybox-'.$selector] .= '      });' . PHP_EOL
                                                                . '    });' . PHP_EOL
                                                                . '  });';
    }
  }
  
  function draw_pagination($pages) {
    
    $pages = ceil($pages);
  
    if ($pages < 2) return false;
    
    
    
    if (empty($_GET['page']) && $_GET['page'] < 2) $_GET['page'] = 1;
    
    if ($_GET['page'] > 1) document::$snippets['head_tags']['prev'] = '<link rel="prev" href="'. document::href_ilink(null, array('page' => $_GET['page']-1), true) .'" />';
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['next'] = '<link rel="next" href="'. document::href_ilink(null, array('page' => $_GET['page']+1), true) .'" />';
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['prefetch'] = '<link rel="prerender" href="'. document::href_ilink(null, array('page' => $_GET['page']+1), true) .'" />'; // Mozilla
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['prerender'] = '<link rel="prerender" href="'. document::href_ilink(null, array('page' => $_GET['page']+1), true) .'" />'; // Webkit
    
    $link = $_SERVER['REQUEST_URI'];
    $link = preg_replace('/page=[0-9]/', '', $link);
    
    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
    
    if (!strpos($link, '?')) $link = $link . '?';
    
    $pagination = new view();
    
    $pagination->snippets['items'][] = array(
      'title' => language::translate('title_previous', 'Previous'),
      'link' => document::ilink(null, array('page' => $_GET['page']-1), true),
      'disabled' => ($_GET['page'] <= 1) ? true : false,
    );
    
    for ($i=1; $i<=$pages; $i++) {
      
      if ($i < $pages-5) {
        if ($i > 1 && $i < $_GET['page'] - 1 && $_GET['page'] > 4) {
          $rewind = round(($_GET['page']-1)/2);
          $pagination->snippets['items'][] = array(
            'title' => ($rewind == $_GET['page']-2) ? $rewind : '...',
            'link' => document::ilink(null, array('page' => $rewind), true),
            'disabled' => false,
            'active' => false,
          );
          $i = $_GET['page'] - 1;
          if ($i > $pages-4) $i = $pages-4;
        }
      }
      
      if ($i > 5) {  
        if ($i > $_GET['page'] + 1 && $i < $pages) {
          $forward = round(($_GET['page']+1+$pages)/2);
          $pagination->snippets['items'][] = array(
            'title' => ($forward == $_GET['page']+2) ? $forward : '...',
            'link' => document::ilink(null, array('page' => $forward), true),
            'disabled' => false,
            'active' => false,
          );
          $i = $pages;
        }
      }
      
      $pagination->snippets['items'][] = array(
        'title' => $i,
        'link' => document::ilink(null, array('page' => $i), true),
        'disabled' => false,
        'active' => ($i == $_GET['page']) ? true : false,
      );
    }
    
    $pagination->snippets['items'][] = array(
      'title' => language::translate('title_next', 'Next'),
      'link' => document::ilink(null, array('page' => $_GET['page']+1), true),
      'disabled' => ($_GET['page'] >= $pages) ? true : false,
      'active' => false,
    );
    
    $html = $pagination->stitch('pagination');
    
    return $html;
  }

?>