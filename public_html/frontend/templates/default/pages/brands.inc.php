<main id="main">
  <div id="content">
    {{notices}}

    <section id="box-brands">

      <h1 class="title"><?php echo language::translate('title_brands', 'Manufacturers'); ?></h1>

      <div class="listing brands columns">

        <?php foreach ($brands as $brand) { ?>
        <article class="brand hover-light">
          <a class="link" href="<?php echo htmlspecialchars($brand['link']); ?>">
            <div class="image-wrapper">
              <img class="img-responsive" src="<?php echo document::href_link(WS_DIR_STORAGE . $brand['image']['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_STORAGE . $brand['image']['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_STORAGE . $brand['image']['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($brand['name']); ?>" />
            </div>
            <h3 class="caption"><?php echo $brand['name']; ?></h3>
          </a>
        </article>
        <?php } ?>

      </div>
    </section>
  </div>
  </main>