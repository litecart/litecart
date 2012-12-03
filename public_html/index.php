<?php
  require_once('includes/app_header.inc.php');

  //$system->document->snippets['title'] = array(); // reset
  $system->document->snippets['title'][] = $system->language->translate('index.php:head_title', 'One fancy web shop');
  $system->document->snippets['keywords'] = $system->language->translate('index.php:meta_keywords', '');
  $system->document->snippets['description'] = $system->language->translate('index.php:meta_description', '');
?>

<?php
  ob_start();
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'slider.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'logotypes.inc.php');
  $system->document->snippets['leaderboard'] = ob_get_clean();
?>

<?php
  ob_start();
  echo '<div id="sidebar" class="shadow rounded-corners">' . PHP_EOL;
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'search.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'category_tree.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'manufacturers.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'account.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'login.inc.php');
  echo '</div>' . PHP_EOL;
  $system->document->snippets['column_left'] = ob_get_clean();
?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'bestsellers.inc.php'); ?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'new_products.inc.php'); ?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'campaigns.inc.php'); ?>

<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>