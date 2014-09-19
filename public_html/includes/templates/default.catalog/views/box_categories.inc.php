<div class="box" id="box-categories">
  <div class="heading"><h3><?php echo language::translate('title_categories', 'Categories'); ?></h3></div>
  <div class="content">
    <ul class="listing-wrapper categories">
      <?php foreach ($categories as $category) echo functions::draw_listing_category($category); ?>
    </ul>
  </div>
</div>