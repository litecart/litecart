<style>
#box-categories .category {
  box-shadow: 0 10px 20px rgb(0 0 0 / 15%);
}
</style>

<section id="box-categories" class="box">

  <!--<h2><?php echo language::translate('title_categories', 'Categories'); ?></h2>-->

  <div class="listing categories">
    <?php foreach ($categories as $category) echo functions::draw_listing_category($category); ?>
  </div>

</section>