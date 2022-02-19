<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_category_tree.inc.php'); ?>
        <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_recently_viewed_products.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}
        {{breadcrumbs}}

        <article id="box-category" class="card">
          <div class="card-header">
            <div class="row" style="margin-bottom: 0;">
              <?php if ($_GET['page'] == 1 && $image) { ?>
              <div class="col-md-4">
                <div class="thumbnail">
                  <img src="<?php echo document::href_link($image['thumbnail_1x']); ?>" />
                </div>
              </div>
              <?php } ?>

              <div class="<?php echo $image ? 'col-md-8' : 'col-md-12'; ?>">
                <h1 class="card-title"><?php echo $h1_title; ?></h1>

                <?php if ($_GET['page'] == 1 && trim(strip_tags($description))) { ?>
                <p class="description">{{description}}</p>
                <?php } ?>
              </div>
            </div>
          </div>

          <div class="card-body">
            <ul class="nav nav-pills">
              <li><a href="<?php echo !empty($parent_id) ? document::href_ilink('category', ['category_id' => $parent_id]) : document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('fa-angle-left'); ?> <?php echo language::translate('title_back', 'Back'); ?></a></li>
              <?php foreach ($subcategories as $subcategory) { ?><li><a href="<?php echo document::href_ilink('category', ['category_id' => $subcategory['id']]); ?>"><?php echo $subcategory['name']; ?></a></li><?php } ?>
            </ul>

            <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_filter.inc.php'); ?>

            <section class="listing products <?php echo (isset($_GET['list_style']) && $_GET['list_style'] == 'rows') ? 'rows' : 'columns'; ?>">
              <?php foreach ($products as $product) echo functions::draw_listing_product($product, ['category_id']); ?>
            </section>
          </div>

          <?php if ($pagination) { ?>
          <div class="card-footer">
            {{pagination}}
          </div>
          <?php } ?>
        </article>
      </div>
    </div>
  </div>
</main>