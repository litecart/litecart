<?php

  function draw_fontawesome_icon($name, $params=null, $class=null) {
    trigger_error('draw_fontawesome_icon() is deprecated. Use instead draw_fonticon()', E_USER_DEPRECATED);
    return functions::draw_fonticon('fa-'.$name . ($class ? ' ' . $class : null), $params);
  }

  function draw_fonticon($class, $params=null) {

    switch(true) {
      case (substr($class, 0, 3) == 'fa '):
        return '<i class="'. $class .'"'. (!empty($params) ? ' ' . $params : null) .'></i>';

      case (substr($class, 0, 3) == 'fa-'):
        return '<i class="fa '. $class .'"'. (!empty($params) ? ' ' . $params : null) .'></i>';

      default:
        trigger_error('Unknown font icon ('. $class .')', E_USER_WARNING);
        return;
    }
  }

  function draw_listing_category($category) {

    $listing_category = new view();

    list($width, $height) = functions::image_scale_by_width(320, settings::get('category_image_ratio'));

    $listing_category->snippets = array(
      'category_id' => $category['id'],
      'name' => $category['name'],
      'link' => document::ilink('category', array('category_id' => $category['id'])),
      'image' => array(
        'original' => WS_DIR_IMAGES . $category['image'],
        'thumbnail' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category['image'], $width, $height, 'CROP'),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category['image'], $width*2, $height*2, 'CROP'),
        'viewport' => array(
          'width' => $width,
          'height' => $height,
        ),
      ),
      'short_description' => $category['short_description'],
    );

    return $listing_category->stitch('views/listing_category');
  }

  function draw_listing_product($product, $listing_type='column') {

    $listing_product = new view();

    $sticker = '';
    if ($product['campaign_price']) {
      $sticker = '<div class="sticker sale" title="'. language::translate('title_on_sale', 'On Sale') .'">'. language::translate('sticker_sale', 'Sale') .'</div>';
    } else if ($product['date_created'] > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
      $sticker = '<div class="sticker new" title="'. language::translate('title_new', 'New') .'">'. language::translate('sticker_new', 'New') .'</div>';
    }

    list($width, $height) = functions::image_scale_by_width(160, settings::get('product_image_ratio'));

    $listing_product->snippets = array(
      'listing_type' => $listing_type,
      'product_id' => $product['id'],
      'code' => $product['code'],
      'name' => $product['name'],
      'link' => document::ilink('product', array('product_id' => $product['id']), array('category_id')),
      'image' => array(
        'original' => $product['image'] ? WS_DIR_IMAGES . $product['image'] : '',
        'thumbnail' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], $width, $height, settings::get('product_image_clipping')),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], $width*2, $height*2, settings::get('product_image_clipping')),
        'viewport' => array(
          'width' => $width,
          'height' => $height,
        ),
      ),
      'sticker' => $sticker,
      'manufacturer_name' => $product['manufacturer_name'],
      'short_description' => $product['short_description'],
      'quantity' => $product['quantity'],
      'price' => currency::format(tax::get_price($product['price'], $product['tax_class_id'])),
      'campaign_price' => $product['campaign_price'] ? currency::format(tax::get_price($product['campaign_price'], $product['tax_class_id'])) : null,
    );

  // Watermark Original Image
    if (settings::get('product_image_watermark')) {
      $listing_product->snippets['image']['original'] = functions::image_process(FS_DIR_HTTP_ROOT . $listing_product->snippets['image']['original'], array('watermark' => true));
    }

    return $listing_product->stitch('views/listing_product');
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
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['prerender'] = '<link rel="prerender" href="'. document::href_ilink(null, array('page' => $_GET['page']+1), true) .'" />';

    $pagination = new view();

    $pagination->snippets['items'][] = array(
      'title' => language::translate('title_previous', 'Previous'),
      'link' => document::ilink(null, array('page' => $_GET['page']-1), true),
      'disabled' => ($_GET['page'] <= 1) ? true : false,
      'active' => false,
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

    $html = $pagination->stitch('views/pagination');

    return $html;
  }

?>