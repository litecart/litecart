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
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/checkout.min.css'); ?>" />
{{head_tags}}
{{style}}
</head>
<body>

<header id="header" class="container">
  <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
    <img src="<?php echo document::href_rlink(FS_DIR_STORAGE . 'images/logotype.png'); ?>" alt="<?php echo settings::get('site_name'); ?>" title="<?php echo settings::get('site_name'); ?>" />
  </a>

  <div class="middle hidden-xs hidden-sm"></div>

  <div class="customer-service hidden-xs">
    <div class="title"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></div>
    <?php if (settings::get('site_phone')) { ?>
    <div class="phone"><?php echo functions::draw_fonticon('fa-phone'); ?> <?php echo settings::get('site_phone'); ?></div>
    <?php } else { ?>
    <div class="email"><?php echo functions::draw_fonticon('fa-envelope'); ?> <?php echo settings::get('site_email'); ?></div>
    <?php } ?>
  </div>
</header>


<main id="page">
  {{content}}
</main>

{{foot_tags}}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{{javascript}}
</body>
</html>