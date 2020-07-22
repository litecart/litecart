<main id="main" class="container">
  <div id="sidebar">
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_customer_service_links.inc.php'); ?>
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_account_links.inc.php'); ?>
  </div>

  <div id="content">
    {snippet:notices}

    <section id="box-customer-service" class="box">
      <?php echo $content; ?>
    </section>

  </div>
</main>