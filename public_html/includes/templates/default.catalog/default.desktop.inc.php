<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{snippet:language}" lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={snippet:charset}" />
<meta name="keywords" content="{snippet:keywords}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link href="<!--snippet:template_path-->styles/desktop.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo WS_DIR_EXT; ?>jquery/jquery-1.8.0.min.js"></script>
<!--snippet:head_tags-->
<!--snippet:javascript-->
</head>
<body>

<div id="body-wrapper">
  <div id="header-wrapper">
    <div style="float: right; display: inline;">
      <div id="languages-wrapper"><?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'languages.inc.php'); ?></div>
      <div id="currencies-wrapper"><?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'currencies.inc.php'); ?></div>
      <div id="search-wrapper"><?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'search.inc.php'); ?></div>
    </div>
    <a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'index.php'); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" height="60" title="<?php echo $system->settings->get('store_name'); ?>" /></a>
  </div>
  
  <div id="menu-wrapper">
    <div style="float: right">
      <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'cart.inc.php'); ?>
    </div>
    <?php include (FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'site_menu.inc.php'); ?>
  </div>
  
  <?php include (FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'top_menu.inc.php'); ?>
  
  <div id="content-wrapper">
    <table width="100%">
      <tr>
        <td id="sidebar">
          <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'categories.inc.php'); ?>
          <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'manufacturers.inc.php'); ?>
          <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'account.inc.php'); ?>
          <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'login.inc.php'); ?>
        </td>
        <td id="content">
          <!--snippet:alerts-->
          <!--snippet:content-->
        </td>
      </tr>
    </table>    
  </div>
  
  <div id="footer-wrapper">
    <div id="footer" style="overflow: auto;">
      <div id="copyright" style="float: right; display: inline; font-weight: bold; text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.5);">
        <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo $system->settings->get('store_name'); ?> &middot; Powered by <a href="#">LiteCart&trade;</a></p>
        <p><!--snippet:stats--></p>
      </div>
      <div id="breadcrumbs"><!--snippet:breadcrumbs--></div>
    </div>
  </div>
</div>

</body>
</html>