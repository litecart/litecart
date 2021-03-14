<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_customer_service_links.inc.php'); ?>
        <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_account_links.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}
        {{content}}
      </div>
    </div>
  </div>
</main>