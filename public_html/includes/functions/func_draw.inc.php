<?php

  function draw_fonticon($class, $params=null) {

    switch(true) {

    // Fontawesome (Deprecated)
      case (substr($class, 0, 3) == 'fa '):
        trigger_error('Fonticon syntax "fa " is deprecated, use instead "fa-"', E_USER_DEPRECATED);
        return draw_fonticon(substr($class, 3), $parameters);

    // Fontawesome
      case (substr($class, 0, 3) == 'fa-'):
        //document::$snippets['head_tags']['fontawesome'] = '<link rel="stylesheet" href="//cdn.jsdelivr.net/fontawesome/latest/css/font-awesome.min.css" />'; // Uncomment if removed from lib_document
        return '<i class="fa '. $class .'"'. (!empty($params) ? ' ' . $params : null) .'></i>';

    // Foundation
      case (substr($class, 0, 3) == 'fi-'):
        document::$snippets['head_tags']['foundation-icons'] = '<link rel="stylesheet" href="//cdn.jsdelivr.net/foundation-icons/latest/foundation-icons.min.css" />';
        return '<i class="'. $class .'"'. (!empty($params) ? ' ' . $params : null) .'></i>';

    // Gyphicon
      case (substr($class, 0, 10) == 'glyphicon-'):
        //document::$snippets['head_tags']['glyphicon'] = '<link rel="stylesheet" href="'/path/to/glyphicon.min.css" />'; // Not embedded in release
        return '<span class="glyphicon '. $class .'"'. (!empty($params) ? ' ' . $params : null) .'></span>';

    // Ion Icons
      case (substr($class, 0, 4) == 'ion-'):
        document::$snippets['head_tags']['ionicons'] = '<link rel="stylesheet" href="//cdn.jsdelivr.net/ionicons/latest/css/ionicons.min.css" />';
        return '<i class="'. $class .'"'. (!empty($params) ? ' ' . $params : null) .'></i>';

      default:
        trigger_error('Unknown font icon ('. $class .')', E_USER_WARNING);
        return;
    }
  }

  function draw_listing_category($category) {

    $listing_category = new ent_view();

    list($width, $height) = functions::image_scale_by_width(480, settings::get('category_image_ratio'));

    $listing_category->snippets = array(
      'category_id' => $category['id'],
      'name' => $category['name'],
      'link' => document::ilink('category', array('category_id' => $category['id'])),
      'image' => array(
        'original' => 'images/' . $category['image'],
        'thumbnail' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $category['image'], $width, $height, settings::get('category_image_clipping')),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $category['image'], $width*2, $height*2, settings::get('category_image_clipping')),
        'viewport' => array(
          'width' => $width,
          'height' => $height,
        ),
      ),
      'short_description' => $category['short_description'],
    );

    return $listing_category->stitch('views/listing_category');
  }

  function draw_listing_product($product, $listing_type='column', $inherit_params=array()) {

    $listing_product = new ent_view();

    $sticker = '';
    if ((float)$product['campaign_price']) {
      $sticker = '<div class="sticker sale" title="'. language::translate('title_on_sale', 'On Sale') .'">'. language::translate('sticker_sale', 'Sale') .'</div>';
    } else if ($product['date_created'] > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
      $sticker = '<div class="sticker new" title="'. language::translate('title_new', 'New') .'">'. language::translate('sticker_new', 'New') .'</div>';
    }

    list($width, $height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));

    $listing_product->snippets = array(
      'product_id' => $product['id'],
      'code' => $product['code'],
      'sku' => $product['sku'],
      'mpn' => $product['mpn'],
      'gtin' => $product['gtin'],
      'name' => $product['name'],
      'link' => document::ilink('product', array('product_id' => $product['id']), $inherit_params),
      'image' => array(
        'original' => ltrim($product['image'] ? 'images/' . $product['image'] : '', '/'),
        'thumbnail' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $product['image'], $width, $height, settings::get('product_image_clipping'), settings::get('product_image_trim')),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $product['image'], $width*2, $height*2, settings::get('product_image_clipping'), settings::get('product_image_trim')),
        'viewport' => array(
          'width' => $width,
          'height' => $height,
        ),
      ),
      'sticker' => $sticker,
      'manufacturer' => array(),
      'short_description' => $product['short_description'],
      'quantity' => $product['quantity'],
      'regular_price' => tax::get_price($product['price'], $product['tax_class_id']),
      'campaign_price' => (float)$product['campaign_price'] ? tax::get_price($product['campaign_price'], $product['tax_class_id']) : null,
    );

    if (!empty($product['manufacturer_id'])) {
      $listing_product->snippets['manufacturer'] = array(
        'id' => $product['manufacturer_id'],
        'name' => $product['manufacturer_name'],
      );
    }

  // Watermark Original Image
    if (settings::get('product_image_watermark')) {
      $listing_product->snippets['image']['original'] = functions::image_process(FS_DIR_APP . $listing_product->snippets['image']['original'], array('watermark' => true));
    }

    return $listing_product->stitch('views/listing_product_'.$listing_type);
  }

  function draw_lightbox($selector='', $params=array()) {

    $selector = str_replace("'", '"', $selector);

    document::$snippets['head_tags']['featherlight'] = '<link rel="stylesheet" href="'. WS_DIR_APP .'ext/featherlight/featherlight.min.css" />';
    document::$snippets['foot_tags']['featherlight'] = '<script src="'. WS_DIR_APP .'ext/featherlight/featherlight.min.js"></script>';
    document::$snippets['javascript']['featherlight'] = '  $.featherlight.autoBind = \'[data-toggle="lightbox"]\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.loading = \'<div class="loader" style="width: 128px; height: 128px; opacity: 0.5;"></div>\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.closeIcon = \'&#x2716;\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.targetAttr = \'data-target\';';
    if (empty($selector)) return;

    if (preg_match('#^(https?:)?//#', $selector)) {
      document::$snippets['javascript']['featherlight-'.$selector] = '  $.featherlight(\''. $selector .'\', {' . PHP_EOL;
    } else {
      document::$snippets['javascript']['featherlight-'.$selector] = '  $(\''. $selector .'\').featherlight({' . PHP_EOL;
    }

    foreach ($params as $key => $value) {
      switch (gettype($params[$key])) {
        case 'NULL':
          document::$snippets['javascript']['featherlight-'.$selector] .= '    '. $key .': null,' . PHP_EOL;
          break;
        case 'boolean':
          document::$snippets['javascript']['featherlight-'.$selector] .= '    '. $key .': '. ($value ? 'true' : 'false') .',' . PHP_EOL;
          break;
        case 'integer':
          document::$snippets['javascript']['featherlight-'.$selector] .= '    '. $key .': '. $value .',' . PHP_EOL;
          break;
        case 'string':
          if (preg_match('#^function\s?\(#', $value)) {
            document::$snippets['javascript']['featherlight-'.$selector] .= '    '. $key .': '. $value .',' . PHP_EOL;
          } else if (preg_match('#^undefined$#', $value)) {
            document::$snippets['javascript']['featherlight-'.$selector] .= '    '. $key .': undefined,' . PHP_EOL;
          } else {
            document::$snippets['javascript']['featherlight-'.$selector] .= '    '. $key .': \''. addslashes($value) .'\',' . PHP_EOL;
          }
          break;
        case 'array':
          document::$snippets['javascript']['featherlight-'.$selector] .= '    '. $key .': [\''. implode('\', \'', $value) .'\'],' . PHP_EOL;
          break;
      }
    }

    document::$snippets['javascript']['featherlight-'.$selector] = rtrim(document::$snippets['javascript']['featherlight-'.$selector], ','.PHP_EOL) . PHP_EOL
                                                                 . '  });';
  }

  function draw_pagination($pages) {

    $pages = ceil($pages);

    if ($pages < 2) return false;

    if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) $_GET['page'] = 1;

    if ($_GET['page'] > 1) document::$snippets['head_tags']['prev'] = '<link rel="prev" href="'. document::href_link(null, array('page' => $_GET['page']-1), true) .'" />';
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['next'] = '<link rel="next" href="'. document::href_link(null, array('page' => $_GET['page']+1), true) .'" />';
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['prerender'] = '<link rel="prerender" href="'. document::href_link(null, array('page' => $_GET['page']+1), true) .'" />';

    $pagination = new ent_view();

    $pagination->snippets['items'][] = array(
      'title' => language::translate('title_previous', 'Previous'),
      'link' => document::link(null, array('page' => $_GET['page']-1), true),
      'disabled' => ($_GET['page'] <= 1) ? true : false,
      'active' => false,
    );

    for ($i=1; $i<=$pages; $i++) {

      if ($i < $pages-5) {
        if ($i > 1 && $i < $_GET['page'] - 1 && $_GET['page'] > 4) {
          $rewind = round(($_GET['page']-1)/2);
          $pagination->snippets['items'][] = array(
            'title' => ($rewind == $_GET['page']-2) ? $rewind : '...',
            'link' => document::link(null, array('page' => $rewind), true),
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
            'link' => document::link(null, array('page' => $forward), true),
            'disabled' => false,
            'active' => false,
          );
          $i = $pages;
        }
      }

      $pagination->snippets['items'][] = array(
        'title' => $i,
        'link' => document::link(null, array('page' => $i), true),
        'disabled' => false,
        'active' => ($i == $_GET['page']) ? true : false,
      );
    }

    $pagination->snippets['items'][] = array(
      'title' => language::translate('title_next', 'Next'),
      'link' => document::link(null, array('page' => $_GET['page']+1), true),
      'disabled' => ($_GET['page'] >= $pages) ? true : false,
      'active' => false,
    );

    $html = $pagination->stitch('views/pagination');

    return $html;
  }
