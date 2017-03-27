<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="robots" content="noindex, nofollow" />
{snippet:head_tags}
<link rel="stylesheet" href="{snippet:template_path}css/framework.min.css" />
<link rel="stylesheet" href="{snippet:template_path}css/app.min.css" />
{snippet:style}
</head>
<body>

<div class="page">

  <div id="sidebar">

    <div id="logotype">
      <a href="<?php echo document::href_link(WS_DIR_ADMIN); ?>">
        <img class="center-block img-responsive" src="<?php echo WS_DIR_TEMPLATE; ?>images/logotype.svg" alt="<?php echo settings::get('store_name'); ?>" />
      </a>
    </div>

    <div id="shortcuts" class="text-center">
      <a href="<?php echo document::href_ilink(''); ?>" title="<?php echo language::translate('title_catalog', 'Catalog'); ?>"><?php echo functions::draw_fonticon('fa-chevron-circle-left'); ?></a>
      <a href="<?php echo document::href_link(WS_DIR_ADMIN); ?>" title="<?php echo htmlspecialchars(language::translate('title_home', 'Home')); ?>"><?php echo functions::draw_fonticon('fa-home fa-lg'); ?></a>
      <?php if (settings::get('webmail_link', '')) { ?><a href="<?php echo settings::get('webmail_link'); ?>" target="_blank" title="<?php echo language::translate('title_webmail', 'Webmail'); ?>"><?php echo functions::draw_fonticon('fa-envelope'); ?></a><?php } ?>
      <?php if (settings::get('database_admin_link', '')) { ?><a href="<?php echo settings::get('database_admin_link'); ?>" target="_blank" title="<?php echo language::translate('title_database_manager', 'Database Manager'); ?>"><?php echo functions::draw_fonticon('fa-database'); ?></a><?php } ?>
      <a href="<?php echo document::href_link(WS_DIR_ADMIN . 'logout.php'); ?>" title="<?php echo language::translate('text_logout', 'Logout'); ?>"><?php echo functions::draw_fonticon('fa-sign-out fa-lg'); ?></a>
    </div>

    <div id="search" class="container-fluid">
      <?php echo functions::form_draw_search_field('query', false, 'placeholder="'. htmlspecialchars(language::translate('title_search', 'Search')) .'&hellip;"'); ?>
      <div class="results"></div>
    </div>

    {snippet:box_apps_menu}

    <div id="languages" class="text-center">
      <?php foreach (language::$languages as $language) { ?>
      <?php if ($language['status']) { ?><a href="<?php echo document::href_link(null, array('language' => $language['code']), true); ?>"><img src="<?php echo WS_DIR_IMAGES .'languages/'. $language['code'] .'.png'; ?>" alt="<?php echo htmlspecialchars($language['name']); ?>" style="max-width: 16px;" /></a><?php } ?>
      <?php } ?>
    </div>

    <div id="platform" class="text-center"><?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?></div>

    <div id="copyright" class="text-center">&copy; <?php echo date('2012-Y'); ?> LiteCart<br />
      <a href="http://www.litecart.net" target="_blank">www.litecart.net</a>
    </div>
  </div>

  <main id="main">
    {snippet:notices}
    {snippet:content}
  </main>

</div>

{snippet:foot_tags}
<script src="{snippet:template_path}js/app.min.js"></script>
{snippet:javascript}
</body>
</html>