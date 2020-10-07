<div id="content">
  {snippet:notices}

  <section id="box-brands" class="box">

    <h1 class="title"><?php echo language::translate('title_brands', 'Manufacturers'); ?></h1>

    <div class="listing products">

      <?php foreach ($brands as $brand) { ?>
      <article class="brand hover-light">
        <a class="link" href="<?php echo htmlspecialchars($brand['link']); ?>">
          <div class="image-wrapper">
            <img class="img-responsive" src="<?php echo htmlspecialchars($brand['image']['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($brand['image']['thumbnail']); ?> 1x, <?php echo htmlspecialchars($brand['image']['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($brand['name']); ?>" />
          </div>
          <h3 class="caption"><?php echo $brand['name']; ?></h3>
        </a>
      </article>
      <?php } ?>

    </div>
  </section>
</div>
