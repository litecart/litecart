<?php

  function draw_banner($keywords) {

    if (!is_array($keywords)) {
      $keywords = preg_split('#\s*,\s*#', $keywords);
    }

  // Add support for Google Analytics campaigns
    if (isset($_GET['utm_campaign'])) {
      $keywords[] = $_GET['utm_campaign'];
    }

    $banner_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."banners
      where status
      and image != '' or html != ''
      and (". implode(" or ", array_map(function($k){ return "find_in_set('". database::input($k) ."', keywords)"; }, $keywords)) .")
      order by rand()
      limit 1;"
    );

    if (!$banner = database::fetch($banner_query)) return;

    database::query(
      "update ". DB_TABLE_PREFIX ."banners
      set total_views = total_views + 1
      where id = ". (int)$banner['id'] ."
      limit 1;"
    );

    if (empty($banner['html'])) {
      $banner['html'] = '<img class="img-responsive" src="$image_url" alt="" />';

      if (!empty($banner['link'])) {
        $banner['html'] = '<a href="$target_url" target="_blank">' . PHP_EOL
                        . '  ' . $banner['html'] . PHP_EOL
                        . '</a>';
      }
    }

    $aliases = [
      '$id' => $banner['id'],
      '$language_code' => language::$selected['code'],
      '$image_url' => document::link(WS_DIR_APP .'images/' . $banner['image']),
      '$target_url' => document::href_link($banner['link']),
    ];

    document::$snippets['javascript'][] = '  var mouseOverAd = false;' . PHP_EOL
                                        . '  $(\'.banner[data-id="'. $banner['id'] .'"]\').hover(function(){' . PHP_EOL
                                        . '    mouseOverAd = "'. $banner['id'] .'";' . PHP_EOL
                                        . '  }, function(){' . PHP_EOL
                                        . '    mouseOverAd = false;' . PHP_EOL
                                        . '  });' . PHP_EOL
                                        . PHP_EOL
                                        . '  $(\'.banner[data-id="'. $banner['id'] .'"]\').click(function(){' . PHP_EOL
                                        . '    $.post("'. document::ilink('ajax/bct') .'", "banner_id='. $banner['id'] .'");' . PHP_EOL
                                        . '  });' . PHP_EOL
                                        . PHP_EOL
                                        . '  $(window).blur(function(){' . PHP_EOL
                                        . '    if (mouseOverAd == "'. $banner['id'] .'"){' . PHP_EOL
                                        . '      $.post("'. document::ilink('ajax/bct') .'", "banner_id='. $banner['id'] .'");' . PHP_EOL
                                        . '    }' . PHP_EOL
                                        . '  });';

    $output = strtr($banner['html'], $aliases);

    $output = '<div class="banner" data-id="'. $banner['id'] .'" data-name="'. $banner['name'] .'">'. PHP_EOL
             . $output . PHP_EOL
             . '</div>';

    return $output;
  }

  function draw_fonticon($class, $parameters=null) {

    switch(true) {

    // Bootstrap Icons
      case (substr($class, 0, 3) == 'bi-'):
        document::$snippets['head_tags']['bootstrap-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" />';
        return '<i class="bi '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';

    // Fontawesome 4
      case (substr($class, 0, 3) == 'fa-'):
        //document::$snippets['head_tags']['fontawesome'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/fontawesome/4.7.0/css/font-awesome.min.css" />'; // Uncomment if removed from lib_document
        return '<i class="fa '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';

    // Foundation
      case (substr($class, 0, 3) == 'fi-'):
        document::$snippets['head_tags']['foundation-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/foundation-icons/latest/foundation-icons.min.css" />';
        return '<i class="'. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';

    // Glyphicon
      case (substr($class, 0, 10) == 'glyphicon-'):
        //document::$snippets['head_tags']['glyphicon'] = '<link rel="stylesheet" href="'/path/to/glyphicon.min.css" />'; // Not embedded in release
        return '<span class="glyphicon '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></span>';

    // Ion Icons
      case (substr($class, 0, 4) == 'ion-'):
        document::$snippets['head_tags']['ionicons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/ionicons/latest/css/ionicons.min.css" />';
        return '<i class="'. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';

    // Material Design Icons
      case (substr($class, 0, 4) == 'mdi-'):
        document::$snippets['head_tags']['material-design-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" />';
        return '<i class="mdi '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';
    }

    switch($class) {
      case 'add':         return draw_fonticon('fa-plus', 'style="color: #6c6;"');
      case 'cancel':      return draw_fonticon('fa-times');
      case 'edit':        return draw_fonticon('fa-pencil');
      case 'folder':      return draw_fonticon('fa-folder', 'style="color: #cc6;"');
      case 'folder-open': return draw_fonticon('fa-folder-open', 'style="color: #cc6;"');
      case 'remove':      return draw_fonticon('fa-times-circle', 'style="color: #c33;"');
      case 'delete':      return draw_fonticon('fa-trash-o');
      case 'move-up':     return draw_fonticon('fa-arrow-circle-up', 'style="color: #39c;"');
      case 'move-down':   return draw_fonticon('fa-arrow-circle-down', 'style="color: #39c;"');
      case 'ok':          return draw_fonticon('fa-check');
      case 'on':          return draw_fonticon('fa-circle', 'style="font-size: 0.75em; color: #88cc44;"');
      case 'off':         return draw_fonticon('fa-circle', 'style="font-size: 0.75em; color: #ff6644;"');
      case 'semi-off':    return draw_fonticon('fa-circle', 'style="font-size: 0.75em; color: #ded90f;"');
      case 'save':        return draw_fonticon('fa-floppy-o');
      case 'send':        return draw_fonticon('fa-paper-plane');
      default: trigger_error('Unknown font icon ('. $class .')', E_USER_WARNING); return;
    }
  }

  function draw_listing_category($category) {

    $listing_category = new ent_view(FS_DIR_TEMPLATE . 'partials/listing_category.inc.php');

    list($width, $height) = functions::image_scale_by_width(480, settings::get('category_image_ratio'));

    $listing_category->snippets = [
      'category_id' => $category['id'],
      'name' => $category['name'],
      'link' => document::ilink('category', ['category_id' => $category['id']]),
      'image' => [
        'original' => 'images/' . $category['image'],
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category['image'], $width, $height),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category['image'], $width*2, $height*2),
        'viewport' => [
          'width' => $width,
          'height' => $height,
          'ratio' => str_replace(':', '/', settings::get('category_image_ratio')),
          'clipping' => strtolower(settings::get('category_image_clipping')),
        ],
      ],
      'short_description' => $category['short_description'],
    ];

    return (string)$listing_category;
  }

  function draw_listing_product($product, $inherit_params=[]) {

    $listing_product = new ent_view(FS_DIR_TEMPLATE . 'partials/listing_product.inc.php');

    $sticker = '';
    if ($product['campaign_price']) {
      $sticker = '<div class="sticker sale" title="'. language::translate('title_on_sale', 'On Sale') .'">'. language::translate('sticker_sale', 'Sale') .'</div>';
    } else if ($product['date_created'] > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
      $sticker = '<div class="sticker new" title="'. language::translate('title_new', 'New') .'">'. language::translate('sticker_new', 'New') .'</div>';
    }

    list($width, $height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));

    $listing_product->snippets = [
      'product_id' => $product['id'],
      'code' => $product['code'],
      'name' => $product['name'],
      'link' => document::ilink('product', ['product_id' => $product['id']], $inherit_params),
      'image' => [
        'original' => ltrim($product['image'] ? 'images/' . $product['image'] : '', '/'),
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product['image'], $width, $height, settings::get('product_image_trim')),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product['image'], $width*2, $height*2, settings::get('product_image_trim')),
        'viewport' => [
          'width' => $width,
          'height' => $height,
          'ratio' => str_replace(':', '/', settings::get('product_image_ratio')),
          'clipping' => strtolower(settings::get('product_image_clipping')),
        ],
      ],
      'sticker' => $sticker,
      'brand' => [],
      'short_description' => $product['short_description'],
      'quantity' => $product['quantity'],
      'quantity_unit_id' => $product['quantity_unit_id'],
      'recommended_price' => tax::get_price($product['recommended_price'], $product['tax_class_id']),
      'regular_price' => tax::get_price($product['price'], $product['tax_class_id']),
      'campaign_price' => $product['campaign_price'] ? tax::get_price($product['campaign_price'], $product['tax_class_id']) : null,
      'final_price' => tax::get_price($product['final_price'], $product['tax_class_id']),
      'tax' => tax::get_tax($product['price'], $product['tax_class_id']),
      'tax_class_id' => $product['tax_class_id'],
      'delivery_status_id' => $product['delivery_status_id'],
      'sold_out_status_id' => $product['sold_out_status_id'],
    ];

    if (!empty($product['brand_id'])) {
      $listing_product->snippets['brand'] = [
        'id' => $product['brand_id'],
        'name' => $product['brand_name'],
      ];
    }

  // Watermark Original Image
    if (settings::get('product_image_watermark')) {
      $listing_product->snippets['image']['original'] = functions::image_process(FS_DIR_APP . $listing_product->snippets['image']['original'], ['watermark' => true]);
    }

    return (string)$listing_product;
  }

  function draw_lightbox($selector='', $parameters=[]) {

    document::$snippets['head_tags']['featherlight'] = '<link rel="stylesheet" href="'. document::href_rlink(FS_DIR_APP . 'assets/featherlight/featherlight.min.css') .'" />';
    document::$snippets['foot_tags']['featherlight'] = '<script src="'. document::href_rlink(FS_DIR_APP . 'assets/featherlight/featherlight.min.js') .'"></script>';
    document::$snippets['javascript']['featherlight'] = '  $.featherlight.autoBind = \'[data-toggle="lightbox"]\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.loading = \'<div class="loader" style="width: 128px; height: 128px; opacity: 0.5;"></div>\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.closeIcon = \'&#x2716;\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.targetAttr = \'data-target\';';

    $selector = str_replace("'", '"', $selector);

    if (empty($selector)) return;

    if (preg_match('#^(https?:)?//#', $selector)) {
      $js = '  $.featherlight(\''. $selector .'\', {' . PHP_EOL;
    } else {
      $js = '  $(\''. $selector .'\').featherlight({' . PHP_EOL;
    }

    foreach ($parameters as $key => $value) {
      switch (gettype($parameters[$key])) {

        case 'NULL':
          $js .= '    '. $key .': null,' . PHP_EOL;
          break;

        case 'boolean':
          $js .= '    '. $key .': '. ($value ? 'true' : 'false') .',' . PHP_EOL;
          break;

        case 'integer':
          $js .= '    '. $key .': '. $value .',' . PHP_EOL;
          break;

        case 'string':
          if (preg_match('#^function\s?\(#', $value)) {
            $js .= '    '. $key .': '. $value .',' . PHP_EOL;
          } else if (preg_match('#^undefined$#', $value)) {
            $js .= '    '. $key .': undefined,' . PHP_EOL;
          } else {
            $js .= '    '. $key .': \''. addslashes($value) .'\',' . PHP_EOL;
          }
          break;

        case 'array':
          $js .= '    '. $key .': [\''. implode('\', \'', $value) .'\'],' . PHP_EOL;
          break;
      }
    }

    $js = rtrim($js, ",\r\n") . PHP_EOL
        . '  });';

    document::$snippets['javascript']['featherlight-'.$selector] = $js;
  }

  function draw_pagination($pages) {

    $pages = ceil($pages);

    if ($pages < 2) return false;

    if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) $_GET['page'] = 1;

    if ($_GET['page'] > 1) document::$snippets['head_tags']['prev'] = '<link rel="prev" href="'. document::href_link(null, ['page' => $_GET['page']-1], true) .'" />';
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['next'] = '<link rel="next" href="'. document::href_link(null, ['page' => $_GET['page']+1], true) .'" />';
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['prerender'] = '<link rel="prerender" href="'. document::href_link(null, ['page' => $_GET['page']+1], true) .'" />';

    $pagination = new ent_view(FS_DIR_TEMPLATE . 'partials/pagination.inc.php');

    $pagination->snippets['items'][] = [
      'page' => $_GET['page']-1,
      'title' => language::translate('title_previous', 'Previous'),
      'link' => document::link(null, ['page' => $_GET['page']-1], true),
      'disabled' => ($_GET['page'] <= 1) ? true : false,
      'active' => false,
    ];

    for ($i=1; $i<=$pages; $i++) {

      if ($i < $pages-5) {
        if ($i > 1 && $i < $_GET['page'] - 1 && $_GET['page'] > 4) {
          $rewind = round(($_GET['page'] - 1) / 2);
          $pagination->snippets['items'][] = [
            'page' => $rewind,
            'title' => ($rewind == $_GET['page']-2) ? $rewind : '...',
            'link' => document::link(null, ['page' => $rewind], true),
            'disabled' => false,
            'active' => false,
          ];
          $i = $_GET['page'] - 1;
          if ($i > $pages-4) $i = $pages-4;
        }
      }

      if ($i > 5) {
        if ($i > $_GET['page'] + 1 && $i < $pages) {
          $forward = round(($_GET['page']+1+$pages)/2);
          $pagination->snippets['items'][] = [
            'page' => $forward,
            'title' => ($forward == $_GET['page']+2) ? $forward : '...',
            'link' => document::link(null, ['page' => $forward], true),
            'disabled' => false,
            'active' => false,
          ];
          $i = $pages;
        }
      }

      $pagination->snippets['items'][] = [
        'page' => $i,
        'title' => $i,
        'link' => document::link(null, ['page' => $i], true),
        'disabled' => false,
        'active' => ($i == $_GET['page']) ? true : false,
      ];
    }

    $pagination->snippets['items'][] = [
      'page' => $_GET['page']+1,
      'title' => language::translate('title_next', 'Next'),
      'link' => document::link(null, ['page' => $_GET['page']+1], true),
      'disabled' => ($_GET['page'] >= $pages) ? true : false,
      'active' => false,
    ];

    return (string)$pagination;
  }
