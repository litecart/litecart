<style>
#box-categories .category {
  box-shadow: 0 10px 20px rgb(0 0 0 / 15%);
  margin-bottom: var(--gutter-size);
}
</style>

<section id="box-categories">

  <div class="listing categories">
    <?php foreach ($categories as $category) echo functions::draw_listing_category($category); ?>
  </div>

</section>