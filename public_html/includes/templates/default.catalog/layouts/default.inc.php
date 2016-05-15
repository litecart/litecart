<?php
// Cookie acceptance - By EU law
  if (empty($_COOKIE['cookies_accepted'])) {
    if (!isset(document::$snippets['bottom'])) document::$snippets['bottom'] = '';
    document::$snippets['bottom'] .= '<div id="cookies-acceptance-wrapper">' . PHP_EOL
                                   . '  <div id="cookies-acceptance" class="twelve-eighty">' . PHP_EOL
                                   . '    ' . language::translate('terms_cookies_acceptance', 'We rely on cookies to provide our services. By using our services, you agree to our use of cookies.') .' '. functions::form_draw_button('accept_cookies', language::translate('title_ok', 'OK'), 'button') . PHP_EOL
                                   . '  </div>' . PHP_EOL
                                   . '</div>' . PHP_EOL
                                   . '<script src="'. WS_DIR_EXT .'jquery/jquery.cookie.min.js"></script>' . PHP_EOL
                                   . '<script>' . PHP_EOL
                                   . '  $("button[name=\'accept_cookies\']").click(function(){' . PHP_EOL
                                   . '    $("#cookies-acceptance-wrapper").fadeOut();' . PHP_EOL
                                   . '    $.cookie("cookies_accepted", "1", {path: "'. WS_DIR_HTTP_HOME .'", expires: 365});' . PHP_EOL
                                   . '  });' . PHP_EOL
                                   . '</script>';
  }
?>
<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="{snippet:template_path}styles/loader.css" media="all" />
<link rel="stylesheet" href="{snippet:template_path}styles/theme.css" media="all" />
<!--[if IE]><link rel="stylesheet" href="{snippet:template_path}styles/ie.css" media="all" /><![endif]-->
<!--[if IE 9]><link rel="stylesheet" href="{snippet:template_path}styles/ie9.css" media="all" /><![endif]-->
<!--[if lt IE 9]><link rel="stylesheet" href="{snippet:template_path}styles/ie8.css" media="all" /><![endif]-->
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script><![endif]-->
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script><![endif]-->
<!--snippet:head_tags-->
<!--snippet:styles-->
<!--snippet:javascript-->
<style>
<?php
  if (!empty(document::$settings['fixed_header'])) {
    echo '#header-wrapper { position: fixed !important; }' . PHP_EOL;
  } else {
    echo '#header-wrapper { position: absolute !important; box-shadow: none !important; background: none; }' . PHP_EOL;
    echo '#page-wrapper { padding-top: 80px; }' . PHP_EOL;
  }
?>
</style>
</head>
<body>

<div id="header-wrapper" class="shadow">
  <div style="padding: 0px 10px;">
    <header id="header" class="twelve-eighty">

      <div id="logotype-wrapper">
        <a href="<?php echo document::href_ilink(''); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" style="max-width: 220px; max-height: 50px;" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" /></a>
      </div>

      <div id="region-wrapper">
        <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_region.inc.php'); ?>
      </div>

      <?php if (!settings::get('catalog_only_mode')) { ?>
      <div id="cart-wrapper">
        <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_cart.inc.php'); ?>
      </div>
      <?php } ?>

    </header>
  </div>
</div>

<div id="page-wrapper">
  <div id="page">

    <div id="site-menu-wrapper">
      <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_site_menu.inc.php'); ?>
    </div>

    <div id="main-wrapper" class="twelve-eighty">
      <div id="main">

        <div class="top">
          <!--snippet:notices-->
          <!--snippet:top-->
        </div>

        <div class="middle">

          <div class="left">
            <!--snippet:column_left-->
          </div>

          <div class="content">
            <!--snippet:content-->
          </div>
        </div>

        <div class="bottom">
          <!--snippet:bottom-->
        </div>
      </div>
    </div>
  </div>
</div>

<div id="footer-wrapper">
  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_site_footer.inc.php'); ?>
</div>

<a id="scroll-up" href="#">
  <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
</a>

<script>
  $(window).scroll(function(){
    if ($(this).scrollTop() > 100) {
      $('#scroll-up').fadeIn();
    } else {
      $('#scroll-up').fadeOut();
    }
  });

  $('#scroll-up').click(function(){
    $("html, body").animate({scrollTop: 0}, 1000, 'swing');
    return false;
  });
</script>

<!--snippet:foot_tags-->
</body>
</html>