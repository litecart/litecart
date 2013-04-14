<?php
  require_once('includes/app_header.inc.php');

  //$system->document->snippets['title'] = array(); // reset
  $system->document->snippets['title'][] = $system->language->translate('index.php:head_title', 'One fancy web shop');
  $system->document->snippets['keywords'] = $system->language->translate('index.php:meta_keywords', '');
  $system->document->snippets['description'] = $system->language->translate('index.php:meta_description', '');
  
  $system->document->snippets['head_tags']['opengraph'] = '<meta property="og:url" content="'. $system->document->href_link(WS_DIR_HTTP_HOME) .'" />' . PHP_EOL
                                                        //. '<meta property="og:title" content="'. htmlspecialchars($system->language->translate('index.php:head_title')) .'" />' . PHP_EOL
                                                        //. '<meta property="og:description" content="'. htmlspecialchars($system->language->translate('index.php:meta_description')) .'" />' . PHP_EOL
                                                        . '<meta property="og:type" content="website" />' . PHP_EOL
                                                        . '<meta property="og:image" content="'. $system->document->href_link(WS_DIR_IMAGES . 'logotype.png') .'" />';
?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'slider.inc.php'); ?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'logotypes.inc.php'); ?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'new_products.inc.php'); ?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'most_popular.inc.php'); ?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'campaigns.inc.php'); ?>

<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>