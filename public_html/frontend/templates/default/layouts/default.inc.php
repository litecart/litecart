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

<?php include 'app://frontend/partials/site_top_navigation.inc.php'; ?>

<?php include 'app://frontend/partials/site_navigation.inc.php'; ?>

{{content}}

<?php include 'app://frontend/partials/site_footer.inc.php'; ?>

<a id="scroll-up" class="hidden-print" href="#">
  <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
</a>

<?php include FS_DIR_TEMPLATE . 'partials/box_cookie_notice.inc.php'; ?>

{{foot_tags}}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/3rdparty.js'); ?>"></script>
{{javascript}}
</body>
</html>