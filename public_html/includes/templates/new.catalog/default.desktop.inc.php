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
  
    <header id="header" class="rounded-corners-top">
    
      <div id="logotype-wrapper">
        <a href="<?php echo $system->document->link(WS_DIR_HTTP_HOME . 'index.php'); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" border="0" height="60" title="<?php echo $system->settings->get('store_name'); ?>" /></a>
      </div>

      <div id="languages-wrapper">
        <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'languages.inc.php'); ?>
      </div>
      
      <div id="currencies-wrapper">
        <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'currencies.inc.php'); ?>
      </div>
      
      <div id="search-wrapper">
        <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'search.inc.php'); ?>
      </div>
      
      <div id="site-menu-wrapper">
      <?php include (FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'site_menu.inc.php'); ?>
      </div>
      
    </header>
    
    <div id="navigation" class="box-gradient1 rounded-corners shadow">
    
      <div id="cart-wrapper">
        <?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'cart.inc.php'); ?>
      </div>
      
      <div id="top-menu-wrapper">
        <?php include (FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'top_menu.inc.php'); ?>
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
        
          <aside id="column-right-wrapper" class="shadow rounded-corners-bottom">
            <!--snippet:column_right-->
          </aside>
          
        </td>
      </tr>
    </table>
  </div>
  
  <div id="footer-wrapper">
  
    <div id="breadcrumbs-wrapper">
      <nav id="breadcrumbs">
        <!--snippet:breadcrumbs-->
      </nav>
    </div>
    
    <footer id="footer" class="box-gradient1 shadow rounded-corners">
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
            <nav class="information">
              <p><strong><?php echo $system->language->translate('title_information', 'Information'); ?></strong></p>
              <ul>
              <?php
                $pages_query = $system->database->query(
                  "select p.id, pi.title from ". DB_TABLE_PAGES ." p
                  left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". $system->language->selected['code'] ."')
                  where dock_support
                  order by p.priority, pi.title;"
                );
                while ($page = $system->database->fetch($pages_query)) {
                  echo '    <li><a href="'. $system->document->link(WS_DIR_HTTP_HOME . 'page.php', array('page_id' => $page['id'])) .'">'. $page['title'] .'</a></li>' . PHP_EOL;
                }
              ?>
              </ul>
            </nav>
          </td>
          <td valign="top">
            <div class="contact">
              <p><strong><?php echo $system->language->translate('title_contact', 'Contact'); ?></strong></p>
              <p><?php echo nl2br($system->settings->get('store_postal_address')); ?></p>
              <p><?php echo $system->settings->get('store_phone'); ?></p>
              <p><?php echo $system->settings->get('store_email'); ?></p>
            </div>
          </td>
          <td valign="top">
          </td>
        </tr>
      </table>
    </footer>
  </div>
  
  <div id="copyright" class="engraved-text">
    <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo $system->settings->get('store_name'); ?>. All rights reserved. &middot; Designed and developed by <a href="http://www.tim-international.net" target="blank">T. Almroth / TiM-International.net</a> &middot; Powered by <a href="#">LiteCart&trade;</a></p>
  </div>
  
  <!--<p><!--snippet:stats--></p>-->
  
</div>

</body>
</html>