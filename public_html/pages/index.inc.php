<?php

  document::$snippets['title'][] = language::translate('index:head_title', 'Online Store');
  document::$snippets['description'] = language::translate('index:meta_description', '');
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('') .'" />';
  document::$snippets['head_tags']['opengraph'] = '<meta property="og:url" content="'. document::href_ilink('') .'" />' . PHP_EOL
                                                . '<meta property="og:type" content="website" />' . PHP_EOL
                                                . '<meta property="og:image" content="'. document::href_link(WS_DIR_IMAGES . 'logotype.png') .'" />';

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');

  $page = new view();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_slider.inc.php');
  $page->snippets['box_slider'] = ob_get_clean();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_manufacturer_logotypes.inc.php');
  $page->snippets['box_manufacturer_logotypes'] = ob_get_clean();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_campaign_products.inc.php');
  $page->snippets['box_campaign_products'] = ob_get_clean();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_most_popular_products.inc.php');
  $page->snippets['box_most_popular_products'] = ob_get_clean();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_latest_products.inc.php');
  $page->snippets['box_latest_products'] = ob_get_clean();

  echo $page->stitch('views/index');
?>
