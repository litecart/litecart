<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<meta name="description" content="{{description}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/variables.css'); ?>">
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/framework.min.css'); ?>">
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/app.min.css'); ?>">
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/checkout.min.css'); ?>">
{{head_tags}}
</head>
<body>

<div id="page">

  <header>
    <?php if ($important_notice) { ?>
    <div id="important-notice">
      <div class="container">
        <?php echo $important_notice; ?>
      </div>
    </div>
    <?php } ?>

    <div id="header" class="container">
      <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
        <img src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>">
      </a>

      <div class="middle hidden-xs text-center">
        <a class="btn btn-default btn-lg" href="<?php echo document::href_ilink(''); ?>">
          <?php echo functions::draw_fonticon('fa-arrow-left'); ?> <?php echo language::translate('title_back_to_store', 'Back To Store'); ?>
        </a>
      </div>

      <div class="contact-info hidden-xs">
        <div class="title"><?php echo language::translate('title_contact', 'Contact'); ?></div>
        <?php if (settings::get('store_phone')) { ?>
        <div class="phone"><?php echo functions::draw_fonticon('fa-phone'); ?> <?php echo settings::get('store_phone'); ?></div>
        <?php } else { ?>
        <div class="email"><?php echo functions::draw_fonticon('fa-envelope'); ?> <?php echo settings::get('store_email'); ?></div>
        <?php } ?>
      </div>
    </div>
  </header>

  <main>
    {{content}}
  </main>

</div>

{{foot_tags}}
<script src="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/js/app.min.js'); ?>"></script>
</body>
</html>