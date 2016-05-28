<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="robots" content="noindex, nofollow" />
<link href="{snippet:template_path}styles/loader.css" rel="stylesheet" media="all" />
<link href="{snippet:template_path}styles/theme.css" rel="stylesheet" media="all" />
<!--snippet:head_tags-->
<!--snippet:styles-->
<!--snippet:javascript-->
<script>
var $buoop = {c:2};
function $buo_f(){
  var e = document.createElement("script");
  e.src = "//browser-update.org/update.js";
  document.body.appendChild(e);
};
try {document.addEventListener("DOMContentLoaded", $buo_f,false)}
catch(e){window.attachEvent("onload", $buo_f)}
</script>
</head>
<body>

<div id="body-wrapper">
  <div id="body">
    <div id="content-wrapper">
    <table style="width: 100%;">
      <tr>
        <td id="sidebar" style="vertical-align: top; width: 230px;">
          <div class="logotype">
            <a href="<?php echo document::href_link(WS_DIR_ADMIN); ?>"><img src="<?php echo functions::image_process(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'logotype.png', array('width' => 220, 'height' => 70, 'clipping' => 'FIT_ONLY_BIGGER')); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" /></a>
          </div>
          <div class="header">
            <a href="<?php echo document::href_ilink(''); ?>" title="<?php echo language::translate('title_catalog', 'Catalog'); ?>"><?php echo functions::draw_fonticon('fa-chevron-circle-left'); ?></a>
            <a href="<?php echo document::href_link(WS_DIR_ADMIN); ?>" title="<?php echo htmlspecialchars(language::translate('title_home', 'Home')); ?>"><?php echo functions::draw_fonticon('fa-home fa-lg'); ?></a>
            <?php if (settings::get('webmail_link', '')) { ?><a href="<?php echo settings::get('webmail_link'); ?>" target="_blank" title="<?php echo language::translate('title_webmail', 'Webmail'); ?>"><?php echo functions::draw_fonticon('fa-envelope'); ?></a><?php } ?>
            <?php if (settings::get('database_admin_link', '')) { ?><a href="<?php echo settings::get('database_admin_link'); ?>" target="_blank" title="<?php echo language::translate('title_database_manager', 'Database Manager'); ?>"><?php echo functions::draw_fonticon('fa-database'); ?></a><?php } ?>
            <a href="<?php echo document::href_link(WS_DIR_ADMIN . 'logout.php'); ?>" title="<?php echo language::translate('text_logout', 'Logout'); ?>"><?php echo functions::draw_fonticon('fa-sign-out fa-lg'); ?></a>
          </div>

          <!--snippet:dashboard-->

          <!--snippet:box_apps_menu-->

          <div class="footer">
            <div class="languages" style="text-align: center; margin-bottom: 10px 0 20px 0;">
<?php
  foreach (language::$languages as $language) {
    if ($language['status']) {
      echo '<a href="'. document::href_link(null, array('language' => $language['code']), true) .'"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language['code'] .'.png" alt="'. $language['name'] .'" style="max-width: 16px;" /></a> ';
    }
  }
?>
            </div>

            <div id="platform"><?php echo PLATFORM_NAME; ?>® <?php echo PLATFORM_VERSION; ?></div>

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
      </tr>
    </table>
    </div>
  </div>
</div>

<!--snippet:foot_tags-->
</body>
</html>