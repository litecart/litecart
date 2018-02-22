<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
{snippet:head_tags}
<link rel="stylesheet" href="{snippet:template_path}css/framework.min.css" />
<link rel="stylesheet" href="{snippet:template_path}css/app.min.css" />
{snippet:style}
</head>
<body>

<div id="page" class="twelve-eighty">

  <header id="header" class="row nowrap center">

    <div class="col-xs-auto">
      <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
        <img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" style="max-width: 250px; max-height: 60px;" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
      </a>
    </div>

    <div class="col-xs-auto text-center hidden-xs">
      <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_region.inc.php'); ?>
    </div>

    <div class="col-xs-auto text-right">
      <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_cart.inc.php'); ?>
    </div>
  </header>

  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_site_menu.inc.php'); ?>

  <div id="main">
    {snippet:content}
  </div>

  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATE . 'views/site_cookie_notice.inc.php'); ?>

  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_site_footer.inc.php'); ?>
</div>

<a id="scroll-up" href="#">
  <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
</a>

{snippet:foot_tags}
<script src="{snippet:template_path}js/app.min.js"></script>
{snippet:javascript}
</body>
</html>