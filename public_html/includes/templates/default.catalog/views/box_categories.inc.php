<div id="box-categories" class="box">
  <h1 class="title"><?php echo language::translate('title_categories', 'Categories'); ?></h1>
  <div class="content">
    <ul class="listing-wrapper categories">
      <?php foreach ($categories as $category) echo functions::draw_listing_category($category); ?>
    </ul>
  </div>
</div>