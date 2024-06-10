<main id="main" class="container">
  <div id="content">
    {{notices}}

    <section id="box-brands">

      <h1 class="title"><?php echo language::translate('title_brands', 'Brands'); ?></h1>

      <div class="listing brands columns">

        <?php foreach ($brands as $brand) { ?>
        <article class="brand hover-light">
          <a class="link" href="<?php echo functions::escape_html($brand['link']); ?>">
            <?php echo functions::draw_thumbnail($brand['image'], 320, 100, 'fit', 'alt="'. functions::escape_attr($brand['name']) .'"'); ?>
            <h3 class="caption"><?php echo $brand['name']; ?></h3>
          </a>
        </article>
        <?php } ?>

      </div>
    </section>
  </div>
  </main>