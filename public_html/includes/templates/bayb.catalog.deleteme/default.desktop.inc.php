<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="keywords" content="{snippet:keywords}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=device-width">
<link rel="stylesheet" href="<!--snippet:template_path-->styles/stylesheet.css" media="screen" />
<script type="text/javascript" src="<?php echo WS_DIR_EXT; ?>jquery/jquery-1.8.0.min.js"></script>
<!--snippet:head_tags-->
<!--snippet:javascript-->
</head>
<body>

<div id="page">

  <div id="header-wrapper">
  
    <header id="header" class="">
    
      <div id="logotype-wrapper">
        <a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'index.php'); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" border="0" title="<?php echo $system->settings->get('store_name'); ?>" /></a>
        <?php /*<script>
          $(function() {
            $('img[data-hover]').hover(function() {
              $(this)
                .attr('tmp', $(this).attr('src'))
                .attr('src', $(this).attr('data-hover'))
                .attr('data-hover', $(this).attr('tmp'))
                .removeAttr('tmp');
            }).each(function() {
              $('<img />').attr('src', $(this).attr('data-hover'));
            });;
          });
        </script>
        */?>
      </div>
      
      <div id="cart-wrapper">
        <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'cart.inc.php'); ?>
      </div>
      
    </header>
    
    <div id="navigation" class="box-gradient1 shadow rounded-corners-top">
      
      <div id="breadcrumbs-wrapper">
        <nav id="breadcrumbs">
          <!--snippet:breadcrumbs-->
        </nav>
      </div>
      
      <div id="checkout-button-wrapper">
        <?php echo $system->functions->form_draw_button('checkout', $system->language->translate('title_checkout', 'Checkout') .' &gt;&gt;', 'button', 'onclick="location=\''. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php') .'\'"') . $system->functions->form_draw_form_end(); ?>
      </div>
      
    </div>
    
  </div>
  
  <div id="main">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
      <tr>
        <td valign="top">
        
          <aside id="column-left-wrapper">
            <!--snippet:column_left-->
          </aside>
          
        </td>
        
        <td valign="top" width="100%">
        
          <div id="leaderboard-wrapper">
            <!--snippet:leaderboard-->
          </div>
          
          <div id="content-wrapper">
            <div id="content" class="">
              <!--snippet:alerts-->
              <!--snippet:content-->
            </div>
          </div>
          
        </td>
        
        <td valign="top">
        
          <aside id="column-right-wrapper" class="shadow">
            <!--snippet:column_right-->
          </aside>
          
        </td>
      </tr>
    </table>
  </div>
  
  <div id="footer-wrapper">
  
    <footer id="footer" class="box-gradient1 shadow rounded-corners-bottom inner-shadow">
      <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
          <td valign="top">
            <nav class="categories">
              <p><strong><?php echo $system->language->translate('title_categories', 'Categories'); ?></strong></p>
              <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'footer_categories.inc.php'); ?>
            </nav>
          </td>
          <td valign="top">
            <nav class="manufacturers">
              <p><strong><?php echo $system->language->translate('title_manufacturers', 'Manufacturers'); ?></strong></p>
              <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'footer_manufacturers.inc.php'); ?>
            </nav>
          </td>
          <td valign="top">
            <nav class="account">
              <p><strong><?php echo $system->language->translate('title_account', 'Account'); ?></strong></p>
              <ul class="navigation-vertical">
                <?php if (empty($system->customer->data['id'])) { ?>
                <li><a href="<?php echo $system->document->href_link('login.php'); ?>"><?php echo $system->language->translate('title_login', 'Login'); ?></a></li>
                <li><a href="<?php echo $system->document->href_link('create_account.php'); ?>"><?php echo $system->language->translate('title_create_account', 'Create Account'); ?></a></li>
                <?php } else { ?>
                <li><a href="<?php echo $system->document->href_link('order_history.php'); ?>"><?php echo $system->language->translate('title_order_history', 'Order History'); ?></a></li>
                <li><a href="<?php echo $system->document->href_link('edit_account.php'); ?>"><?php echo $system->language->translate('title_edit_account', 'Edit Account'); ?></a></li>
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
          <td valign="top">
            <nav class="information">
              <p><strong><?php echo $system->language->translate('title_information', 'Information'); ?></strong></p>
              <ul class="navigation-vertical">
              <?php
                $pages_query = $system->database->query(
                  "select p.id, pi.title from ". DB_TABLE_PAGES ." p
                  left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". $system->language->selected['code'] ."')
                  where dock_support
                  order by p.priority, pi.title;"
                );
                while ($page = $system->database->fetch($pages_query)) {
                  echo '<li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'page.php', array('page_id' => $page['id'])) .'">'. $page['title'] .'</a></li>' . PHP_EOL;
                }
              ?>
              </ul>
            </nav>
          </td>
          <td valign="top" width="175">
            <div class="contact">
              <p><strong><?php echo $system->language->translate('title_contact', 'Contact'); ?></strong></p>
              <p><?php echo nl2br($system->settings->get('store_postal_address')); ?></p>
              <p><?php echo $system->settings->get('store_phone'); ?></p>
              <p><?php echo $system->settings->get('store_email'); ?></p>
            </div>
          </td>
        </tr>
      </table>
    </footer>
  </div>
  
  <div id="copyright">
    <p><a href="http://www.tim-international.net" target="blank">Layout, design and development by T. Almroth / TiM-International.net e-commerce solutions on behalf of Bayb Shop</a> &middot; <a href="http://www.tim-international.net" target="_blank">Powered by LiteCart&trade;</a> &middot; Copyright &copy; <?php echo date('Y'); ?> <?php echo $system->settings->get('store_name'); ?> - All rights reserved</p>
  </div>
  
</div>

</body>
</html>