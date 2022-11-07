<div class="fourteen-forty container">
  <main id="content">
    {snippet:notices}

    <section id="box-manufacturers" class="card">

      <div class="card-header">
        <h1 class="card-title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h1>
      </div>

      <div class="card-body">
        <div class="listing products columns">

          <?php foreach ($manufacturers as $manufacturer) { ?>
          <article class="manufacturer">
            <a class="link" href="<?php echo functions::escape_html($manufacturer['link']); ?>">
              <div class="image-wrapper">
                <img class="img-responsive" src="<?php echo document::href_rlink(FS_DIR_APP . $manufacturer['image']['thumbnail']); ?>" srcset="<?php echo document::href_rlink(FS_DIR_APP . $manufacturer['image']['thumbnail']); ?> 1x, <?php echo document::href_rlink(FS_DIR_APP . $manufacturer['image']['thumbnail_2x']); ?> 2x" alt="<?php echo functions::escape_html($manufacturer['name']); ?>" />
              </div>
              <h3 class="caption"><?php echo $manufacturer['name']; ?></h3>
            </a>
          </article>
          <?php } ?>

        </div>
      </div>
    </section>
  </main>
</div>