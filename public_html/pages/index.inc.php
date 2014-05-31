<?php
  require_once('includes/app_header.inc.php');

  //document::$snippets['title'] = array(); // reset
  document::$snippets['title'][] = language::translate('index.php:head_title', 'One fancy web shop');
  document::$snippets['keywords'] = language::translate('index.php:meta_keywords', '');
  document::$snippets['description'] = language::translate('index.php:meta_description', '');
  
  document::$snippets['head_tags']['opengraph'] = '<meta property="og:url" content="'. document::href_link(WS_DIR_HTTP_HOME) .'" />' . PHP_EOL
                                                        //. '<meta property="og:title" content="'. htmlspecialchars(language::translate('index.php:head_title')) .'" />' . PHP_EOL
                                                        //. '<meta property="og:description" content="'. htmlspecialchars(language::translate('index.php:meta_description')) .'" />' . PHP_EOL
                                                        . '<meta property="og:type" content="website" />' . PHP_EOL
                                                        . '<meta property="og:image" content="'. document::href_link(WS_DIR_IMAGES . 'logotype.png') .'" />';
                                                        
  include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');
?>

<?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'slider.inc.php'); ?>

<?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'logotypes.inc.php'); ?>

<?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'most_popular.inc.php'); ?>

<?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'campaigns.inc.php'); ?>

<?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'latest_products.inc.php'); ?>

<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>