<!DOCTYPE html>
<html lang="{snippet:language}" dir="{snippet:text_direction}">
<head>
  <title>{snippet:title}</title>
  <meta charset="{snippet:charset}">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/variables.css'); ?>">
  <link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/framework.css'); ?>">
  <link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/app.css'); ?>">

  <!-- averlon; es gibt nur noch ein template - deshalb wurden die datein verschoben und umbenannt -->
  <link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/admin.variables.css'); ?>">
  <link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/admin.framework.css'); ?>">
  <link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/admin.app.css'); ?>">

  <link rel="stylesheet" href="<?php echo document::href_rlink(FS_DIR_TEMPLATE . 'css/av.css'); ?>">

  {snippet:head_tags}
  {snippet:style}
</head>
<body>
  <div id="page-container">

    <nav id="site-menu">
      <div class="fourteen-forty">
        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_site_menu.inc.php'); ?>
      </div>
    </nav>

    <div class="fourteen-forty">
      <main id="content">
        {snippet:notices}
        {snippet:breadcrumbs}

        <div class="row">
          <div id="account-confirmation" class="card col- center">

            <div class="card-header">
              <h2 class="card-title"><?php echo language::translate('title_customer_email_confirmation', 'Customer e-Mail confirmation'); ?></h2>
            </div>

            <div class="card-body">
              <p class="av_account_confirmation"><?php echo language::translate('av_account_confirmation', 'Your e-Mail address has been confirmed. You account has been activated!'); ?></p>
            </div>
          </div>

        </div>
      </main>
    </div>

    <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_site_footer.inc.php'); ?>
  </div>
</body>
</html>