<?php
// Cookie acceptance - By EU law
  if (empty($_COOKIE['cookies_accepted'])) {
    if (!isset(document::$snippets['top'])) document::$snippets['top'] = '';
    document::$snippets['top'] =  '<div id="cookies-acceptance-wrapper">' . PHP_EOL
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
                                . '</script>'
                                . document::$snippets['top'];
  }
?>
<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="keywords" content="{snippet:keywords}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=1024">
<link rel="stylesheet" href="{snippet:template_path}styles/loader.css" media="all" />
<link rel="stylesheet" href="{snippet:template_path}styles/theme.css" media="all" />
<!--[if IE]><link rel="stylesheet" href="{snippet:template_path}styles/ie.css" /><![endif]-->
<!--[if IE 9]><link rel="stylesheet" href="{snippet:template_path}styles/ie9.css" /><![endif]-->
<!--[if lt IE 9]><link rel="stylesheet" href="{snippet:template_path}styles/ie8.css" /><![endif]-->
<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<!--snippet:head_tags-->
<!--snippet:javascript-->
<script>
  if (/iphone|ipod|android|blackberry|opera mini|opera mobi|skyfire|maemo|windows phone|palm|iemobile|symbian|symbianos|fennec/i.test(navigator.userAgent.toLowerCase())) {
    $("meta[name='viewport']").attr("content", "width=320");
  }
</script>
<style>
<?php
  $settings = unserialize(settings::get('store_template_catalog_settings'));
  
  if (!empty($settings['fixed_header'])) {
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
      </div>
    </div>
  </div>
</div>

<div id="footer-wrapper">
  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_site_footer.inc.php'); ?>
</div>

<a href="#" id="scroll-up">Scroll</a>
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