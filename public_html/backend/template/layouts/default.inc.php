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
    {{content}}
  </main>
</div>

{{foot_tags}}
<script src="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'js/app.min.js'); ?>"></script>
{{javascript}}

<script>
  $('input[name="dark_mode"]').click(function(){
    if ($(this).val() == 1) {
      document.cookie = 'dark_mode=1;path=/;expires=<?php echo date('r', strtotime('+ 3 months')); ?>';
      $('body').addClass('dark-mode');
    } else {
      document.cookie = 'dark_mode=0;path=/;expires=<?php echo date('r', strtotime('+ 3 months')); ?>';
      $('body').removeClass('dark-mode');
    }
  });
</script>
</body>
</html>