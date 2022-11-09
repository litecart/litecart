<!DOCTYPE html>
<html lang="{snippet:language}" dir="{snippet:text_direction}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/app.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/checkout.min.css'); ?>" />
{snippet:head_tags}
{snippet:style}
</head>
<body>

<header id="header" class="fourteen-forty container">

  <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
    <img src="<?php echo document::href_link('images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
  </a>

  <div class="middle hidden-xs text-center">
    <a class="btn btn-default btn-lg" href="<?php echo document::href_ilink(''); ?>">
      <?php echo functions::draw_fonticon('fa-arrow-left'); ?> <?php echo language::translate('title_back_to_store', 'Back To Store'); ?>
    </a>
  </div>

  <div class="customer-service hidden-xs">
    <div class="title"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></div>
    <?php if (settings::get('store_phone')) { ?>
    <div class="phone"><?php echo functions::draw_fonticon('fa-phone'); ?> <?php echo settings::get('store_phone'); ?></div>
    <?php } else { ?>
    <div class="email"><?php echo functions::draw_fonticon('fa-envelope'); ?> <?php echo settings::get('store_email'); ?></div>
    <?php } ?>
  </div>

</header>

{snippet:content}

{snippet:foot_tags}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{snippet:javascript}
</body>
</html>