<main id="main">
  <div id="content">
    {{notices}}

    <section id="box-brands">

      <h1 class="title"><?php echo language::translate('title_brands', 'Brands'); ?></h1>

      <div class="listing brands columns">

        <?php foreach ($brands as $brand) { ?>
        <article class="brand hover-light">
          <a class="link" href="<?php echo functions::escape_html($brand['link']); ?>">
            <img class="responsive" src="<?php echo document::href_rlink($brand['image']['thumbnail']); ?>" srcset="<?php echo document::href_rlink($brand['image']['thumbnail']); ?> 1x, <?php echo document::href_rlink($brand['image']['thumbnail_2x']); ?> 2x" alt="<?php echo functions::escape_html($brand['name']); ?>" />
            <h3 class="caption"><?php echo $brand['name']; ?></h3>
          </a>
        </article>
        <?php } ?>

      </div>
    </section>
  </div>
  </main>