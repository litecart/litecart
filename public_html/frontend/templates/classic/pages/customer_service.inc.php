<main id="main">
  <div id="sidebar">
    <div id="column-left">
      <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_customer_service_links.inc.php'); ?>
      <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_account_links.inc.php'); ?>
    </div>
  </div>

  <div id="content">
    {{breadcrumbs}}
    {{notices}}

    <section id="box-customer-service">
      <?php echo $content; ?>
    </section>
  </div>
</main>