<?php
  require_once('includes/app_header.inc.php');
  
  session_destroy();
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  $forward_url = $system->document->link('index.php');
  header('Location: '. $forward_url);
  exit;
?>
<h1><?php echo $system->language->translate('title_logged_out', 'Logged Out'); ?></h1>
<?php echo $system->language->translate('description_logged_out', 'You are now logged out.'); ?>
<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>