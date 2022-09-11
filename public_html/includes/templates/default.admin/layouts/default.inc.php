<!DOCTYPE html>
<html lang="{snippet:language}" dir="{snippet:text_direction}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="robots" content="noindex, nofollow" />
<meta name="viewport" content="width=1600">
<link rel="stylesheet" href="<?php echo document::rlink(FS_DIR_TEMPLATE . 'css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::rlink(FS_DIR_TEMPLATE . 'css/app.min.css'); ?>" />
{snippet:head_tags}
{snippet:style}
</head>
<body>

<div id="backend-wrapper">
  <input id="sidebar-compressed" type="checkbox" hidden />

  <div id="sidebar" class="hidden-print">

    <div id="logotype">
      <a href="<?php echo document::href_link(WS_DIR_ADMIN); ?>">
        <img class="center-block img-responsive" src="<?php echo WS_DIR_TEMPLATE; ?>images/logotype.svg" alt="<?php echo settings::get('store_name'); ?>" />
      </a>
    </div>

    <div id="search" class="container-fluid">
      <?php echo functions::form_draw_search_field('query', false, 'placeholder="'. functions::escape_html(language::translate('title_search', 'Search')) .'&hellip;" autocomplete="off"'); ?>
      <div class="results"></div>
    </div>

    {snippet:box_apps_menu}

    <div id="platform" class="text-center"><?php echo PLATFORM_NAME; ?>® <?php echo PLATFORM_VERSION; ?></div>

    <div id="copyright" class="text-center">Copyright &copy; <?php echo date('2012-Y'); ?><br />
      <a href="https://www.litecart.net" target="_blank">www.litecart.net</a>
    </div>
  </div>

  <main id="main">
    <ul id="top-bar" class="hidden-print">
      <li>
        <div>
          <label class="nav-toggle" for="sidebar-compressed" >
            <?php echo functions::draw_fonticon('fa-bars'); ?>
          </label>
        </div>
      </li>

      <li>
        {snippet:breadcrumbs}
      </li>

      <li style="flex-grow: 1;"></li>

      <li class="language dropdown">
        <a href="#" data-toggle="dropdown" class="dropdown-toggle"><img src="<?php echo document::href_link(WS_DIR_APP . 'images/languages/'. language::$selected['code'] .'.png'); ?>" alt="<?php echo language::$selected['code']; ?>" title="<?php echo functions::escape_html(language::$selected['name']); ?>" style="max-height: 1em;" /> <b class="caret"></b></a>
        <ul class="dropdown-menu">
          <?php foreach (language::$languages as $language) { ?>
          <li>
            <a href="<?php echo document::href_link(null, ['language' => $language['code']], true); ?>">
              <img src="<?php echo document::href_link(WS_DIR_APP . 'images/languages/'. $language['code'] .'.png'); ?>" alt="<?php echo $language['code']; ?>" style="max-height: 1em;" /> <?php echo $language['name']; ?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </li>

<!--
      <li>
        <a href="<?php echo document::href_link(WS_DIR_ADMIN); ?>" title="<?php echo functions::escape_html(language::translate('title_dashboard', 'Dashboard')); ?>">
          <?php echo functions::draw_fonticon('fa-dashboard'); ?>
        </a>
      </li>
-->


      <?php if ($webmail_link = settings::get('webmail_link', '')) { ?>
      <li>
        <a href="<?php echo $webmail_link; ?>" target="_blank" title="<?php echo language::translate('title_webmail', 'Webmail'); ?>">
          <?php echo functions::draw_fonticon('fa-envelope'); ?>
        </a>
      </li>
      <?php } ?>

      <?php if ($control_panel_link = settings::get('control_panel_link', '')) { ?>
      <li>
        <a href="<?php echo $control_panel_link; ?>" target="_blank" title="<?php echo language::translate('title_control_panel', 'Control Panel'); ?>">
          <?php echo functions::draw_fonticon('fa-cogs'); ?>
        </a>
      </li>
      <?php } ?>

      <?php if ($database_admin_link = settings::get('database_admin_link')) { ?>
      <li>
        <a href="<?php echo $database_admin_link; ?>" target="_blank" title="<?php echo language::translate('title_database_manager', 'Database Manager'); ?>">
          <?php echo functions::draw_fonticon('fa-database'); ?>
        </a>
      </li>
      <?php } ?>

      <li>
        <a href="<?php echo document::href_ilink(''); ?>" title="<?php echo language::translate('title_frontend', 'Frontend'); ?>">
          <?php echo functions::draw_fonticon('fa-desktop'); ?> <?php echo language::translate('title_frontend', 'Frontend'); ?>
        </a>
      </li>

      <li>
        <a class="help" href="https://wiki.litecart.net/" target="_blank" title="<?php echo language::translate('title_help', 'Help'); ?>">
          <?php echo functions::draw_fonticon('fa-question-circle'); ?> <?php echo language::translate('title_help', 'Help'); ?>
        </a>
      </li>

      <li>
        <a href="<?php echo document::href_link(WS_DIR_ADMIN . 'logout.php'); ?>" title="<?php echo language::translate('title_logout', 'Logout'); ?>">
          <?php echo functions::draw_fonticon('fa-sign-out'); ?> <?php echo language::translate('title_logout', 'Logout'); ?>
        </a>
      </li>

    </ul>

    <div id="content">
      {snippet:notices}
      {snippet:content}
    </div>
  </main>
</div>

{snippet:foot_tags}
<script src="<?php echo document::rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{snippet:javascript}
</body>
</html>