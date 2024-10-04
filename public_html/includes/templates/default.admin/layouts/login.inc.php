<!DOCTYPE html>
<html lang="{snippet:language}" dir="{snippet:text_direction}"<?php echo !empty($_COOKIE['dark_mode']) ? ' class="dark-mode"' : ''; ?>>
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}">
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/variables.css'); ?>">
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/framework.min.css'); ?>">
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/app.min.css'); ?>">
{snippet:head_tags}
{snippet:style}
</head>
<body>

{snippet:content}

{snippet:foot_tags}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{snippet:javascript}

</body>
</html>