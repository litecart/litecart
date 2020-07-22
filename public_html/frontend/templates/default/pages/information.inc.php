<main id="main" class="container">
  <div id="sidebar">
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_information_links.inc.php'); ?>
  </div>

  <div id="content">
    {snippet:notices}

    <section id="box-information" class="box">
      <?php echo $content; ?>
    </section>

  </div>
</main>
