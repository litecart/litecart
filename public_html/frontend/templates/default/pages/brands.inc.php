<main id="main" class="container">
  <div id="content">
    {{notices}}

    <section id="box-brands" class="card">

      <div class="card-header">
        <h1 class="card-title"><?php echo language::translate('title_brands', 'Brands'); ?></h1>
      </div>

      <div class="card-body">
        <div class="listing brands">

          <?php foreach ($brands as $brand) { ?>
          <article class="brand">
            <a class="link" href="<?php echo functions::escape_html($brand['link']); ?>">
              <!--<?php echo functions::draw_thumbnail($brand['image'], 320, 100, 'fit', 'alt="'. functions::escape_html(functions::escape_html($brand['name'])) .'"'); ?>-->
              <div class="caption"><?php echo $brand['name']; ?></div>
            </a>
          </article>
          <?php } ?>

        </div>
      </div>
    </section>
  </div>
</main>
