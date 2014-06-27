<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="keywords" content="{snippet:keywords}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=1024">
<link rel="shortcut icon" href="<?php echo WS_DIR_HTTP_HOME; ?>favicon.ico">
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
    $("meta[name='viewport']").attr("content", "width=640");
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
        <a href="<?php echo document::href_link(WS_DIR_HTTP_HOME . 'index.php'); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" height="50" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" /></a>
      </div>
      
      <div id="site-links-wrapper">
      <?php include (FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'site_links.inc.php'); ?>
      </div>
      
      <div id="region-wrapper">
        <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'region.inc.php'); ?>
      </div>
      
      <?php if (!settings::get('catalog_only_mode')) { ?>
      <div id="cart-wrapper">
        <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'cart.inc.php'); ?>
      </div>
      <?php } ?>
      
    </header>
  </div>
</div>

<div id="page-wrapper">
  <div id="page">
    
    <div id="site-menu-wrapper">
      <?php include (FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'site_menu.inc.php'); ?>
    </div>
    
    <div id="main-wrapper" class="twelve-eighty">
      <div id="main">
        <table style="width: 100%;">
          <tr>
            <td colspan="3" class="top">
              <!--snippet:notices-->
              <!--snippet:top-->
            </td>
          </tr>
          <tr>
            <td class="left" style="vertical-align: top;">
              <!--snippet:column_left-->
            </td>
            <td class="content" style="vertical-align: top;">
              <div id="content-wrapper">
                <div id="content" class="">
                  <!--snippet:content-->
                </div>
              </div>
            </td>
            <td class="right" style="vertical-align: top;">
              <!--snippet:column_right-->
            </td>
          </tr>
          <tr>
            <td colspan="3" class="bottom">
              <!--snippet:bottom-->
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<div id="footer-wrapper">
  <footer id="footer" class="twelve-eighty">
    
    <div id="breadcrumbs-wrapper">
      <!--snippet:breadcrumbs-->
    </div>

    <table>
      <tr>
        <td class="categories">
          <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'footer_categories.inc.php'); ?>
        </td>
        <td class="manufacturers">
          <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'footer_manufacturers.inc.php'); ?>
        </td>
        <td class="account">
          <nav>
            <h4><?php echo language::translate('title_account', 'Account'); ?></h4>
            <ul class="list-vertical">
              <li><a href="<?php echo document::link('select_region.php'); ?>"><?php echo language::translate('title_select_region', 'Select Region'); ?></a></li>
              <?php if (empty(customer::$data['id'])) { ?>
              <li><a href="<?php echo document::link('create_account.php'); ?>"><?php echo language::translate('title_create_account', 'Create Account'); ?></a></li>
              <li><a href="<?php echo document::link('login.php'); ?>"><?php echo language::translate('title_login', 'Login'); ?></a></li>
              <?php } else { ?>
              <li><a href="<?php echo document::link('order_history.php'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
              <li><a href="<?php echo document::link('edit_account.php'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
              <li><a href="javascript:logout();"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
              <script>
                function logout() {
                  var form = $('<?php
                    echo str_replace(array("\r", "\n"), '', functions::form_draw_form_begin('logout_form', 'post')
                                                          . functions::form_draw_hidden_field('logout', 'true')
                                                          . functions::form_draw_form_end()
                    );
                  ?>');
                  $(document.body).append(form);
                  form.submit();
                }
              </script>
              <?php } ?>
            </ul>
          </nav>
        </td>
        <td class="information">
          <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'footer_information.inc.php'); ?>
        </td>
        <td class="contact">
          <h4><?php echo language::translate('title_contact', 'Contact'); ?></h4>
          <p><?php echo nl2br(settings::get('store_postal_address')); ?></p><br />
          <p><?php echo settings::get('store_phone'); ?><br />
          <?php list($account, $domain) = explode('@', settings::get('store_email')); echo "<script>document.write('". $account ."' + '@' + '". $domain ."');</script>"; ?></p>
        </td>
      </tr>
    </table>
  </footer>
  
  <div id="copyright" class="twelve-eighty engraved-text">
    <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo settings::get('store_name'); ?>. All rights reserved &middot; Powered by <a href="http://www.litecart.net" target="_blank">LiteCart</a></p>
  </div>
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