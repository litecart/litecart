<main id="main" class="container">
  <div id="content">
    {{notices}}

    <section id="box-brands" class="card">

      <div class="card-header">
        <h1 class="card-title"><?php echo language::translate('title_brands', 'Brands'); ?></h1>
      </div>

      <div class="card-body">
        <div class="listing products">

          <?php foreach ($brands as $brand) { ?>
          <article class="brand hover-light">
            <a class="link" href="<?php echo functions::escape_html($brand['link']); ?>">
              <div class="image-wrapper">
                <img class="img-responsive" src="<?php echo functions::escape_html($brand['image']['thumbnail']); ?>" srcset="<?php echo functions::escape_html($brand['image']['thumbnail']); ?> 1x, <?php echo functions::escape_html($brand['image']['thumbnail_2x']); ?> 2x" alt="<?php echo functions::escape_html($brand['name']); ?>" />
              </div>
              <h3 class="caption"><?php echo $brand['name']; ?></h3>
            </a>
          </article>
          <?php } ?>

        </div>
      </div>
    </section>
  </div>
</main>
