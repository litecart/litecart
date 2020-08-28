<!DOCTYPE html>
<html lang="{snippet:language}" dir="{snippet:text_direction}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="{snippet:template_path}css/variables.css" />
<link rel="stylesheet" href="{snippet:template_path}css/framework.min.css" />
<link rel="stylesheet" href="{snippet:template_path}css/app.min.css" />
{snippet:head_tags}
{snippet:style}
</head>
<body>

<div id="site-wrapper">
  <?php include vmod::check(FS_DIR_TEMPLATE . 'views/box_cookie_notice.inc.php'); ?>

<header id="header" class="hidden-print">
  <div class="container">
    <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
      <img src="<?php echo document::href_link(WS_DIR_STORAGE . 'images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
    </a>

      <div class="text-center hidden-xs" style="font-size: 1.5em;">
        <?php //include vmod::check(FS_DIR_APP . 'frontend/boxes/box_region.inc.php'); ?>
        <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
          <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <div class="text-right">
        <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_cart.inc.php'); ?>
      </div>
    </div>
  </header>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_site_menu.inc.php'); ?>

  {snippet:content}

  <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_site_footer.inc.php'); ?>
</div>

<a id="scroll-up" class="hidden-print" href="#">
  <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
</a>

{snippet:foot_tags}
<script src="{snippet:template_path}js/app.min.js"></script>
{snippet:javascript}
</body>
</html>