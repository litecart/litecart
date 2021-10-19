<main id="main">
  <div id="sidebar">
    <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_category_tree.inc.php'); ?>
    <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_recently_viewed_products.inc.php'); ?>
  </div>

  <div id="content">
    {{notices}}
    {{breadcrumbs}}

    <article id="box-category">

      <div class="row">
        <?php if ($_GET['page'] == 1 && $image) { ?>
        <div class="hidden-xs hidden-sm col-md-4">
          <div class="thumbnail">
            <img src="<?php echo document::href_link(WS_DIR_STORAGE . $image['thumbnail_1x']); ?>" />
          </div>
        </div>
        <?php } ?>

        <div class="<?php echo $image ? 'col-md-8' : 'col-md-12'; ?>">
          <h1 class="title"><?php echo $h1_title; ?></h1>

          <?php if ($_GET['page'] == 1 && trim(strip_tags($description))) { ?>
          <p class="description"><?php echo $description; ?></p>
          <?php } ?>
        </div>
      </div>

      <ul class="nav nav-pills">
        <li><a href="<?php echo !empty($parent_id) ? document::href_ilink('category', ['category_id' => $parent_id]) : document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('fa-arrow-left'); ?> <?php echo language::translate('title_back', 'Back'); ?></a></li>
        <?php foreach ($subcategories as $subcategory) { ?><li><a href="<?php echo document::href_ilink('category', ['category_id' => $subcategory['id']]); ?>"><?php echo $subcategory['name']; ?></a></li><?php } ?>
      </ul>

      <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_filter.inc.php'); ?>

      <section class="listing products <?php echo htmlspecialchars($_GET['list_style']); ?>">
        <?php foreach ($products as $product) echo functions::draw_listing_product($product, ['category_id']); ?>
      </section>

      <?php echo $pagination; ?>
    </article>
  </div>
</main>