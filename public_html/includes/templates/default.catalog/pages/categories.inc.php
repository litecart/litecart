<main id="content">
  <!--snippet:notices-->

  <div id="box-categories" class="box">
    <h3><?php echo language::translate('title_categories', 'Categories'); ?></h3>
    <div class="categories row half-gutter">
      <?php foreach ($categories as $category) echo functions::draw_listing_category($category); ?>
    </div>
  </div>
</main>