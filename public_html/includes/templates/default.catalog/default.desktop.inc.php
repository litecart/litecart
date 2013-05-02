<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="keywords" content="{snippet:keywords}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=1024">
<link rel="shortcut icon" href="<?php echo WS_DIR_HTTP_HOME; ?>favicon.ico">
<link rel="stylesheet" href="<!--snippet:template_path-->styles/loader.css" media="all" />
<!--[if IE]><link rel="stylesheet" type="text/css" href="<!--snippet:template_path-->styles/ie.css" /><![endif]-->
<!--[if IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie9.css" /><![endif]-->
<!--[if lt IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie8.css" /><![endif]-->
<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="//code.jquery.com/jquery-migrate-1.1.1.min.js"></script>
<script type="text/javascript">
  if (typeof jQuery == 'undefined') document.write(unescape("%3Cscript src='<?php echo WS_DIR_EXT; ?>jquery/jquery-1.9.1.min.js' type='text/javascript'%3E%3C/script%3E"));
  if (typeof jQuery.migrateTrace == 'undefined') document.write(unescape("%3Cscript src='<?php echo WS_DIR_EXT; ?>jquery/jquery-migrate-1.1.1.min.js' type='text/javascript'%3E%3C/script%3E"));
  if (/iphone|ipod|android|blackberry|opera mini|opera mobi|skyfire|maemo|windows phone|palm|iemobile|symbian|symbianos|fennec/i.test(navigator.userAgent.toLowerCase())) {
    $("meta[name='viewport']").attr("content", "width=640");
  }
</script>
<!--snippet:head_tags-->
<!--snippet:javascript-->
</head>
<body>

<div id="page-wrapper">
  <div id="page">

    <div id="header-wrapper">
      <header id="header" class="rounded-corners-top">
      
        <div id="logotype-wrapper">
          <a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'index.php'); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" height="60" alt="<?php echo $system->settings->get('store_name'); ?>" /></a>
        </div>

        <div id="languages-wrapper">
          <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'languages.inc.php'); ?>
        </div>
        
        <div id="currencies-wrapper">
          <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'currencies.inc.php'); ?>
        </div>
        
        <div id="site-links-wrapper">
        <?php include (FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'site_links.inc.php'); ?>
        </div>
        
        <div id="cart-wrapper">
          <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'cart.inc.php'); ?>
        </div>
        
      </header>
    </div>
    
    <div id="site-menu-wrapper">
      <?php include (FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'site_menu.inc.php'); ?>
    </div>
    
    <div id="main-wrapper">
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
    
    <div id="breadcrumbs-wrapper">
      <!--snippet:breadcrumbs-->
    </div>

    <div id="footer-wrapper">
      <footer id="footer" class="box-gradient1 shadow rounded-corners">
        <table>
          <tr>
            <td class="categories">
              <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'footer_categories.inc.php'); ?>
            </td>
            <td class="manufacturers">
              <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'footer_manufacturers.inc.php'); ?>
            </td>
            <td class="account">
              <nav>
                <p><strong><?php echo $system->language->translate('title_account', 'Account'); ?></strong></p>
                <ul class="list-vertical">
                  <li><a href="<?php echo $system->document->link('select_region.php'); ?>"><?php echo $system->language->translate('title_select_region', 'Select Region'); ?></a></li>
                  <?php if (empty($system->customer->data['id'])) { ?>
                  <li><a href="<?php echo $system->document->link('create_account.php'); ?>"><?php echo $system->language->translate('title_create_account', 'Create Account'); ?></a></li>
                  <li><a href="<?php echo $system->document->link('login.php'); ?>"><?php echo $system->language->translate('title_login', 'Login'); ?></a></li>
                  <?php } else { ?>
                  <li><a href="<?php echo $system->document->link('order_history.php'); ?>"><?php echo $system->language->translate('title_order_history', 'Order History'); ?></a></li>
                  <li><a href="<?php echo $system->document->link('edit_account.php'); ?>"><?php echo $system->language->translate('title_edit_account', 'Edit Account'); ?></a></li>
                  <li><a href="javascript:logout();"><?php echo $system->language->translate('title_logout', 'Logout'); ?></a></li>
                  <script>
                    function logout() {
                      var form = $('<?php
                        echo str_replace(array("\r", "\n"), '', $system->functions->form_draw_form_begin('logout_form', 'post')
                                                              . $system->functions->form_draw_hidden_field('logout', 'true')
                                                              . $system->functions->form_draw_form_end()
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
              <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'footer_information.inc.php'); ?>
            </td>
            <td class="contact">
              <p><strong><?php echo $system->language->translate('title_contact', 'Contact'); ?></strong></p>
              <p><?php echo nl2br($system->settings->get('store_postal_address')); ?></p>
              <p><?php echo $system->settings->get('store_phone'); ?></p>
              <p><?php echo $system->settings->get('store_email'); ?></p>
            </td>
          </tr>
        </table>
      </footer>
      
      <div id="copyright" class="engraved-text">
        <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo $system->settings->get('store_name'); ?>. All rights reserved &middot; Powered by <a href="http://www.litecart.net" target="_blank">LiteCart&trade;</a></p>
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
    
  </div>
</div>

<!--snippet:foot_tags-->

</body>
</html>