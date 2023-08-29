<!DOCTYPE html>
<html lang="{snippet:language}" dir="{snippet:text_direction}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/app.min.css'); ?>" />
{snippet:head_tags}
{snippet:style}
</head>
<body>

<div id="page-container">

    <?php if ($important_notice) { ?>
    <div id="important-notice">
      <div class="fourteen-forty">
        <?php echo $important_notice; ?>
      </div>
    </div>
    <?php } ?>

    <nav id="site-menu">
      <div class="fourteen-forty">
        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_site_menu.inc.php'); ?>
      </div>
    </nav>

    <div id="content-wrapper">
      {snippet:content}
    </div>

    <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_cookie_notice.inc.php'); ?>

    <a id="scroll-up" class="hidden-print" href="#">
      <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
    </a>

  <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_site_footer.inc.php'); ?>
</div>

{snippet:foot_tags}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{snippet:javascript}
</body>
</html>