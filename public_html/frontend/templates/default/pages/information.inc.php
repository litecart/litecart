<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_information_links.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}

        <section id="box-information" class="box box-default">
          {{content}}
        </section>
    </div>
  </div>
</main>
