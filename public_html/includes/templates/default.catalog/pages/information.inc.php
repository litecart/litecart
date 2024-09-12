<div class="fourteen-forty">
  <div class="layout row">

    <div class="col-md-3">
      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_information_links.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <main id="content">
        {snippet:breadcrumbs}
        {snippet:notices}

        <section id="box-information" class="card">
          <div class="card-body">
            <?php echo $content; ?>
          </div>
        </section>

      </main>
    </div>
  </div>
</div>
