<main id="main" class="container">
  {{breadcrumbs}}
  {{notices}}

  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">

        <h1 style="margin-top: 0;"><?php echo $main_category['name']; ?></h1>

        <?php if ($image) { ?>
        <div style="margin-bottom: 2em;">
          <a href="<?php echo document::href_ilink('category', ['category_id' => $main_category['id']]); ?>">
            <?php echo functions::draw_thumbnail($image, 480, 0, 'category'); ?>
          </a>
        </div>
        <?php } ?>

        <?php include 'app://frontend/partials/box_category_tree.inc.php'; ?>
        <?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">

        <article id="box-category" class="card">
          <div class="card-header">
            <h2 class="card-title"><?php echo $h1_title; ?></h2>
          </div>

          <div class="card-body">

            <?php if ($description) { ?>
            <p class="description">{{description}}</p>
            <?php } ?>

            <nav class="nav nav-pills" style="margin-bottom: 1em;">
              <a class="nav-item" href="<?php echo !empty($parent_id) ? document::href_ilink('category', ['category_id' => $parent_id]) : document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('fa-angle-left'); ?> <?php echo language::translate('title_back', 'Back'); ?></a>
              <?php foreach ($subcategories as $subcategory) { ?><a class="nav-item" href="<?php echo document::href_ilink('category', ['category_id' => $subcategory['id']]); ?>"><?php echo $subcategory['name']; ?></a><?php } ?>
            </nav>

            <?php include 'app://frontend/partials/box_filter.inc.php'; ?>

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