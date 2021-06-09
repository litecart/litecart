<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}" />
<meta name="robots" content="noindex, nofollow" />
<meta name="viewport" content="width=1600">
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/app.min.css'); ?>" />
{{head_tags}}
{{style}}
</head>
<body<?php echo !empty($_COOKIE['dark_mode']) ? ' class="dark-mode"' : ''; ?>>

<div id="backend-wrapper">
  <input id="sidebar-compressed" type="checkbox" hidden />

  <div id="sidebar" class="hidden-print">

    <div id="logotype">
      <a href="<?php echo document::href_ilink(''); ?>">
        <img class="center-block img-responsive" src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'images/logotype.svg'); ?>" alt="<?php echo settings::get('site_name'); ?>" />
      </a>
    </div>

    <div id="search">
      <?php echo functions::form_draw_search_field('query', false, 'placeholder="'. htmlspecialchars(language::translate('title_search', 'Search')) .'&hellip;" autocomplete="off"'); ?>
      <div class="results"></div>
    </div>

    {{box_apps_menu}}

    <div id="platform" class="text-center"><?php echo PLATFORM_NAME; ?>® <?php echo PLATFORM_VERSION; ?></div>

    <div id="copyright" class="text-center">Copyright &copy; <?php echo date('2012-Y'); ?><br />
      <a href="https://www.litecart.net" target="_blank">www.litecart.net</a>
    </div>
  </div>

  <main id="main">
    <ul id="top-bar" class="hidden-print">
      <li>
        <div>
          <label class="nav-toggle" for="sidebar-compressed">
            <?php echo functions::draw_fonticon('fa-bars'); ?>
          </label>
        </div>
      </li>

      <li>
        {{breadcrumbs}}
      </li>

      <li style="flex-grow: 1;"></li>

      <li>
        <div class="btn-group btn-block btn-group-inline" data-toggle="buttons">
          <label class="btn btn-default btn-sm<?php echo empty($_COOKIE['dark_mode']) ? ' active' : ''; ?>"><input type="radio" name="dark_mode" value="0" <?php echo empty($_COOKIE['dark_mode']) ? ' checked ' : ''; ?>/> <?php echo language::translate('title_light', 'Light'); ?></label>
          <label class="btn btn-default btn-sm<?php echo !empty($_COOKIE['dark_mode']) ? ' active' : ''; ?>"><input type="radio" name="dark_mode" value="1" <?php echo !empty($_COOKIE['dark_mode']) ? ' checked ' : ''; ?>/> <?php echo language::translate('title_dark', 'Dark'); ?></label>
        </div>
      </li>

      <li class="language dropdown">
        <a href="#" data-toggle="dropdown" class="dropdown-toggle"><img src="<?php echo document::href_link(WS_DIR_APP . 'assets/languages/'. language::$selected['code'] .'.png'); ?>" alt="<?php echo language::$selected['code']; ?>" title="<?php echo htmlspecialchars(language::$selected['name']); ?>" style="max-height: 1em;" /> <b class="caret"></b></a>
        <ul class="dropdown-menu">
          <?php foreach (language::$languages as $language) { ?>
          <li>
            <a href="<?php echo document::href_ilink(null, ['language' => $language['code']]); ?>">
              <img src="<?php echo document::href_link(WS_DIR_APP . 'assets/languages/'. $language['code'] .'.png'); ?>" alt="<?php echo $language['code']; ?>" style="max-height: 1em;" /> <?php echo $language['name']; ?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </li>

      <?php if ($webmail_link = settings::get('webmail_link', '')) { ?>
      <li>
        <a href="<?php echo $webmail_link; ?>" target="_blank" title="<?php echo language::translate('title_webmail', 'Webmail'); ?>">
          <?php echo functions::draw_fonticon('fa-envelope'); ?>
        </a>
      </li>
      <?php } ?>

      <?php if ($control_card_link = settings::get('control_card_link', '')) { ?>
      <li>
        <a href="<?php echo $control_card_link; ?>" target="_blank" title="<?php echo language::translate('title_control_card', 'Control Panel'); ?>">
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
        <a href="<?php echo document::href_ilink('f:'); ?>" title="<?php echo language::translate('title_frontend', 'Frontend'); ?>">
          <?php echo functions::draw_fonticon('fa-desktop'); ?> <?php echo language::translate('title_frontend', 'Frontend'); ?>
        </a>
      </li>

      <li>
        <a class="help" href="https://wiki.litecart.net/" target="_blank" title="<?php echo language::translate('title_help', 'Help'); ?>">
          <?php echo functions::draw_fonticon('fa-question-circle'); ?> <?php echo language::translate('title_help', 'Help'); ?>
        </a>
      </li>

      <li>
        <a href="<?php echo document::href_ilink('logout'); ?>" title="<?php echo language::translate('title_sign_out', 'Sign Out'); ?>">
          <?php echo functions::draw_fonticon('fa-sign-out'); ?> <?php echo language::translate('title_sign_out', 'Sign Out'); ?>
        </a>
      </li>
    </ul>

    <div id="content">
      {{notices}}
      {{content}}
    </div>
  </main>
</div>

{{foot_tags}}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{{javascript}}

<script>
  $('input[name="dark_mode"]').click(function(){
    var now = new Date();
    var expires = new Date(now.getTime() + (365 * 24 * 60 * 60 * 1000));
    if ($(this).val() == 1) {
      document.cookie = 'dark_mode=1;expires=0';
      $('body').addClass('dark-mode');
    } else {
      document.cookie = 'dark_mode=0;expires=0';
      $('body').removeClass('dark-mode');
    }
  });
</script>
</body>
</html>