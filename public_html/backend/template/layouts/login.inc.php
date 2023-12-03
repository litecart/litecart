<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}"<?php if (!empty($_COOKIE['dark_mode'])) echo ' class="dark-mode"'; ?>>
<head>
<title>{{title}}</title>
<meta charset="{{charset}}" />
<meta name="robots" content="noindex, nofollow" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/app.min.css'); ?>" />
{{head_tags}}
{{style}}
</head>
<body>

{{content}}

{{foot_tags}}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{{javascript}}

</body>
</html>