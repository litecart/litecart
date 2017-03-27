<aside id="sidebar">
  <div id="column-left">
    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_customer_service_links.inc.php'); ?>
    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_account_links.inc.php'); ?>
  </div>
</aside>

<main id="content">
  {snippet:notices}

  <div id="box-customer-service" class="box">
    <?php echo $content; ?>
  </div>

</main>