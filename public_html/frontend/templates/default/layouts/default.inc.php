<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}" />
<meta name="description" content="{{description}}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/app.min.css'); ?>" />
{{head_tags}}
{{style}}
</head>
<body>

<div id="page" class="container">

  <?php include vmod::check(FS_DIR_TEMPLATE . 'views/box_cookie_notice.inc.php'); ?>

  <header id="header" class="hidden-print">
    <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
      <img src="<?php echo document::href_link(WS_DIR_STORAGE . 'images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
    </a>

    <div class="text-center hidden-xs">
      <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_region.inc.php'); ?>
    </div>

    <div class="text-right">
      <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_cart.inc.php'); ?>
    </div>
  </header>

  <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_site_menu.inc.php'); ?>


  <main id="main">
    {{content}}
  </main>

  <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_site_footer.inc.php'); ?>
</div>

<a id="scroll-up" class="hidden-print" href="#">
  <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
</a>

{{foot_tags}}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{{javascript}}
</body>
</html>