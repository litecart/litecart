<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_brand_links.inc.php'); ?>
        <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_recently_viewed_products.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}
        {{breadcrumbs}}

        <article id="box-brand" class="box box-default">

          <h1 class="title">{{title}}</h1>

          <?php if ($_GET['page'] == 1 && $description) { ?>
          <p class="description">{{description}}</p>
          <?php } ?>

          <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_filter.inc.php'); ?>

          <?php if ($products) { ?>
          <section class="listing products">
            <?php foreach ($products as $product) echo functions::draw_listing_product($product, 'column', ['brand_id']); ?>
          </section>
          <?php } ?>

          {{pagination}}
        </article>
      </div>
    </div>
  </div>
</main>