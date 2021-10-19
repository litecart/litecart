<main id="main">
  <div id="sidebar">
    <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_information_links.inc.php'); ?>
  </div>

  <div id="content">
    {{breadcrumbs}}
    {{notices}}

    <section id="box-information">
      <?php echo $content; ?>
    </section>
  </div>
</main>