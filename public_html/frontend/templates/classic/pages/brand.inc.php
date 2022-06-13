<main id="main">
  <div id="sidebar">
    <?php include 'app://frontend/partials/box_brand_links.inc.php'; ?>
    <?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
  </div>

  <div id="content">
    {{notices}}
    {{breadcrumbs}}

    <article id="box-brand" class="box">

      <h1 class="title"><?php echo $title; ?></h1>

      <?php if ($_GET['page'] == 1 && $description) { ?>
      <p class="description"><?php echo $description; ?></p>
      <?php } ?>

      <?php include 'app://frontend/partials/box_filter.inc.php'; ?>

      <?php if ($products) { ?>
      <section class="listing products <?php echo functions::escape_html($_GET['list_style']); ?>">
        <?php foreach ($products as $product) echo functions::draw_listing_product($product, ['brand_id']); ?>
      </section>
      <?php } ?>

      <?php echo $pagination; ?>
    </article>
  </div>
</main>