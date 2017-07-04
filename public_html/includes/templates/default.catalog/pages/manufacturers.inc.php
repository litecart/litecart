<main id="content">
  {snippet:notices}

  <div id="box-manufacturers" class="box">

    <h1 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h1>

    <div class="manufacturers row half-gutter">

      <?php foreach($manufacturers as $manufacturer) { ?>
      <div class="col-md-fourths">
        <div class="thumbnail">
          <a class="link" href="<?php echo htmlspecialchars($manufacturer['link']); ?>" title="<?php echo htmlspecialchars($manufacturer['name']); ?>">
            <div class="image-wrapper">
              <img class="img-responsive" src="<?php echo htmlspecialchars($manufacturer['image']['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($manufacturer['image']['thumbnail']); ?> 1x, <?php echo htmlspecialchars($manufacturer['image']['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($manufacturer['name']); ?>" />
            </div>
            <div class="caption"><?php echo $manufacturer['name']; ?></div>
          </a>
        </div>
      </div>
      <?php } ?>

    </div>
  </div>
</main>
