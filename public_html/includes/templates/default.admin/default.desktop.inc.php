<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{snippet:language}" lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={snippet:charset}" />
<meta name="keywords" content="{snippet:keywords}" />
<meta name="description" content="{snippet:description}" />
<meta name="robots" content="noindex, nofollow" />

<link href="<!--snippet:template_path-->styles/desktop.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo WS_DIR_EXT; ?>jquery/jquery-1.8.0.min.js"></script>
<!--snippet:head_tags-->
<!--snippet:javascript-->
</head>
<body>

<div id="body-wrapper">
  <div id="body">
    <div id="content-wrapper">
    <table style="width: 100%;">
      <tr>
        <td id="sidebar">
          <div class="logotype">
            <a href="<?php echo $system->document->href_link(WS_DIR_ADMIN); ?>"><img src="<?php echo $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'logotype.png', FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 220, 70, 'FIT_ONLY_BIGGER'); ?>" title="<?php echo $system->settings->get('store_name'); ?>" /></a>
          </div>
          <div class="header">
            <a href="<?php echo $system->document->href_link(WS_DIR_ADMIN); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/48x48/home.png'; ?>" width="24" height="24" alt="<?php echo $system->language->translate('title_back_to_index', 'Back To Index'); ?>" title="<?php echo $system->language->translate('title_back_to_index', 'Back To Index'); ?>" /></a>
            <a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/48x48/cart.png'; ?>" width="24" height="24" alt="<?php echo $system->language->translate('text_go_to_store_front', 'Go to store front'); ?>" title="<?php echo $system->language->translate('text_go_to_store_front', 'Go to store front'); ?>" /></a>
            <?php if ($system->settings->get('database_admin_link')) { ?>
              <a href="<?php echo $system->settings->get('database_admin_link'); ?>" target="_blank"><img src="<?php echo WS_DIR_IMAGES . 'icons/48x48/database.png'; ?>" width="24" height="24" alt="<?php echo $system->language->translate('title_database_manager', 'Database Manager'); ?>" title="<?php echo $system->language->translate('title_database_manager', 'Database Manager'); ?>" /></a>
            <?php } ?>
          </div>
          
          <!--snippet:dashboard-->
          
          <!--snippet:sidebar_content-->
          <div class="footer">
            <div class="languages" style="text-align: center; margin-bottom: 10px 0 20px 0;">
<?php
  foreach ($system->language->languages as $language) {
    if ($language['status']) {
      echo '<a href="javascript:set_language(\''. $language['code'] .'\');"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language['code'] .'.png" alt="'. $language['name'] .'" /></a> ';
    }
  }
?>
<script type="text/javascript">
  function set_language(code) {
    var form = $('<?php
      echo str_replace(array("\r", "\n"), '', $system->functions->form_draw_form_begin('language_form', 'post')
                                            . $system->functions->form_draw_hidden_field('set_language', '\'+ code +\'')
                                            . $system->functions->form_draw_form_end()
      );
    ?>');
    $(document.body).append(form);
    form.submit();
  }
</script>
            </div>
            <div class="stats"><!--snippet:stats--></div>
          </div>
        </td>
        <td id="content">
          <!--snippet:notices-->
          <!--snippet:content-->
        </td>
      </tr>
    </table>    
    </div>
  </div>
</div>

<div id="copyright">Copyright &copy; <?php echo date('Y'); ?> <a href="http://www.tim-international.net" target="_blank">LiteCart&trade;</a></div>

</body>
</html>