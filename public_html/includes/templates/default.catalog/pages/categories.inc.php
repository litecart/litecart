<main id="content">
  {snippet:notices}

  <div id="box-categories" class="box">
    <h1><?php echo language::translate('title_categories', 'Categories'); ?></h1>
    <div class="categories row half-gutter">
      <?php foreach ($categories as $category) echo functions::draw_listing_category($category); ?>
    </div>
  </div>
</main>