<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="keywords" content="{snippet:keywords}" />
<meta name="description" content="{snippet:description}" />
<meta name="robots" content="noindex, nofollow" />
<link href="<!--snippet:template_path-->styles/loader.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<!--snippet:template_path-->styles/theme.css" rel="stylesheet" type="text/css" media="screen" />
<!--[if IE]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie.css" /><![endif]-->
<!--[if IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie9.css" /><![endif]-->
<!--[if lt IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie8.css" /><![endif]-->
<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<!--snippet:head_tags-->
<!--snippet:javascript-->
</head>
<body>

<div id="body-wrapper">
  <div id="body">
    <div id="content-wrapper">
    <table style="width: 100%;">
      <tr>
        <td id="sidebar" style="vertical-align: top;">
          <div class="logotype">
            <a href="<?php echo document::href_link(WS_DIR_ADMIN); ?>"><img src="<?php echo functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'logotype.png', FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 220, 70, 'FIT_ONLY_BIGGER'); ?>" title="<?php echo settings::get('store_name'); ?>" /></a>
          </div>
          <div class="header">
            <a href="<?php echo document::href_link(WS_DIR_ADMIN); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/24x24/home.png'; ?>" width="24" height="24" alt="" title="<?php echo language::translate('title_Home', 'Home'); ?>" /></a>
            <a href="<?php echo document::href_link(WS_DIR_HTTP_HOME); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/24x24/catalog.png'; ?>" width="24" height="24" alt="" title="<?php echo language::translate('title_catalog', 'Catalog'); ?>" /></a>
            <?php if (settings::get('webmail_link', '')) { ?><a href="<?php echo settings::get('webmail_link'); ?>" target="_blank"><img src="<?php echo WS_DIR_IMAGES . 'icons/24x24/mail.png'; ?>" width="24" height="24" alt="" title="<?php echo language::translate('title_webmail', 'Webmail'); ?>" /></a><?php } ?>
            <?php if (settings::get('database_admin_link', '')) { ?><a href="<?php echo settings::get('database_admin_link'); ?>" target="_blank"><img src="<?php echo WS_DIR_IMAGES . 'icons/24x24/database.png'; ?>" width="24" height="24" alt="" title="<?php echo language::translate('title_database_manager', 'Database Manager'); ?>" /></a><?php } ?>
            <a href="<?php echo document::href_link(WS_DIR_ADMIN . 'logout.php'); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/24x24/exit.png'; ?>" width="24" height="24" alt="" title="<?php echo language::translate('text_logout', 'Logout'); ?>" /></a>
          </div>
          
          <!--snippet:dashboard-->
          
          <!--snippet:apps-->
          
          <div class="footer">
            <div class="languages" style="text-align: center; margin-bottom: 10px 0 20px 0;">
<?php
  foreach (language::$languages as $language) {
    if ($language['status']) {
      echo '<a href="javascript:set_language(\''. $language['code'] .'\');"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language['code'] .'.png" alt="'. $language['name'] .'" /></a> ';
    }
  }
?>
<script>
  function set_language(code) {
    var form = $('<?php
      echo str_replace(array("\r", "\n"), '', functions::form_draw_form_begin('language_form', 'post')
                                            . functions::form_draw_hidden_field('set_language', '\'+ code +\'')
                                            . functions::form_draw_form_end()
      );
    ?>');
    $(document.body).append(form);
    form.submit();
  }
</script>
            </div>
            
            <div id="platform"><?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?></div>
            
            <div id="copyright">&copy; <?php echo date('2012-Y'); ?> LiteCart<br />
              <a href="http://www.litecart.net" target="_blank">www.litecart.net</a>
            </div>
          </div>
        </td>
        <td id="column_left" style="vertical-align: top;">
          <!--snippet:column_left-->
        </td>
        <td id="content" style="vertical-align: top;">
          <!--snippet:notices-->
          <!--snippet:content-->
        </td>
        <td id="column_right" style="vertical-align: top;">
          <!--snippet:column_right-->
        </td>
      </tr>
    </table>    
    </div>
  </div>
</div>

<!--snippet:foot_tags-->
</body>
</html>