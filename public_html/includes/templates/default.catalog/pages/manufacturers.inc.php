<div id="content">
  {snippet:notices}

  <section id="box-manufacturers" class="box">

    <h1 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h1>

    <div class="listing products">

      <?php foreach ($manufacturers as $manufacturer) { ?>
      <article class="manufacturer hover-light">
        <a class="link" href="<?php echo htmlspecialchars($manufacturer['link']); ?>">
          <div class="image-wrapper">
            <img class="img-responsive" src="<?php echo htmlspecialchars($manufacturer['image']['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($manufacturer['image']['thumbnail']); ?> 1x, <?php echo htmlspecialchars($manufacturer['image']['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($manufacturer['name']); ?>" />
          </div>
          <h3 class="caption"><?php echo $manufacturer['name']; ?></h3>
        </a>
      </article>
      <?php } ?>

    </div>
  </section>
</div>
