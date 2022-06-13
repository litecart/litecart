<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include 'app://frontend/partials/box_customer_service_links.inc.php'; ?>
        <?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
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