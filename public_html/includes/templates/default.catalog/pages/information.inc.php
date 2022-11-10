<div class="fourteen-forty container">
  <div class="layout row">

    <div class="hidden-xs hidden-sm col-md-3">
      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_information_links.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <main id="content">
        {snippet:breadcrumbs}
        {snippet:notices}

        <section id="box-information">
          <?php echo $content; ?>
        </section>

      </main>
    </div>
  </div>
</div>
