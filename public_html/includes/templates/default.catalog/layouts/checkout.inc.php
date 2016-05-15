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
        <a href="<?php echo document::href_ilink(''); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" height="50" alt="<?php echo settings::get('store_name'); ?>" /></a>
      </div>

      <div id="customer-service-wrapper">
        <span class="title"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></span><br />
        <span class="phone"><?php echo settings::get('store_phone'); ?></span>
      </div>

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

<!--snippet:foot_tags-->
</body>
</html>