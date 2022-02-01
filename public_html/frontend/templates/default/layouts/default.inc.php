<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}" />
<meta name="description" content="{{description}}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/framework.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/app.css'); ?>" />
{{head_tags}}
{{style}}
</head>
<body>

<?php include vmod::check(FS_DIR_TEMPLATE . 'partials/box_cookie_notice.inc.php'); ?>

<?php include vmod::check(FS_DIR_TEMPLATE . 'partials/site_header.inc.php'); ?>

<?php include vmod::check(FS_DIR_APP . 'frontend/partials/site_navigation.inc.php'); ?>

{{content}}

<?php include vmod::check(FS_DIR_APP . 'frontend/partials/site_footer.inc.php'); ?>

<a id="scroll-up" class="hidden-print" href="#">
  <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
</a>

{{foot_tags}}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.js'); ?>"></script>
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/3rdparty.js'); ?>"></script>
{{javascript}}
</body>
</html>