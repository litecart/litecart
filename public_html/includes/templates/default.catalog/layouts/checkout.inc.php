<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="{snippet:template_path}css/framework.min.css" media="all" />
<link rel="stylesheet" href="{snippet:template_path}css/app.min.css" media="all" />
<link rel="stylesheet" href="{snippet:template_path}css/checkout.min.css" media="all" />
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script><![endif]-->
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script><![endif]-->
{snippet:head_tags}
{snippet:style}
</head>
<body>

<div class="twelve-eighty">
  <header id="header" class="<?php echo !empty(document::$settings['fixed_header']) ? ' fixed' : ''; ?>">

    <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
      <img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" style="max-width: 250px; max-height: 60px;" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
    </a>

    <div class="customer-service">
      <div class="title"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></div>
      <div class="phone"><?php echo settings::get('store_phone'); ?></div>
    </div>
  </header>
</div>

<div id="page" class="shadow">
  <div id="main" class="twelve-eighty">

    <main id="content">
      {snippet:content}
    </main>

  </div>
</div>

{snippet:foot_tags}
<script src="{snippet:template_path}js/app.min.js"></script>
{snippet:javascript}
</body>
</html>