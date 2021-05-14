<?php
  document::$snippets['head_tags'][] = '<link rel="stylesheet" href="/assets/lightslider/css/lightslider.min.css"></script>';
  document::$snippets['foot_tags'][] = '<script src="/assets/lightslider/js/lightslider.min.js"></script>';
?>
<style>
.lSSlideOuter {
    position: relative;
    overflow: visible;
}

.lSSlideWrapper {
    position: static;
}
</style>

<section id="box-popular-products" class="box white">

  <h2 class="title"><?php echo language::translate('title_popular_products', 'Popular Products'); ?></h2>

  <!--
  <ul data-toggle="slider">
    <li><a href="#">1</a></li>
    <li>2</li>
    <li>3</li>
    <li>4</li>
    <li>5</li>
    <li>6</li>
    <li>7</li>
    <li>8</li>
    <li>9</li>
    <li>10</li>
  </ul>
  -->

  <ul data-toggle="slider">
    <?php foreach ($products as $product) echo '<li>' . functions::draw_listing_product($product) .'</li>'; ?>
    <?php foreach ($products as $product) echo '<li>' . functions::draw_listing_product($product) .'</li>'; ?>
    <?php foreach ($products as $product) echo '<li>' . functions::draw_listing_product($product) .'</li>'; ?>
  </ul>

</section>

<script>
$('[data-toggle="slider"]').lightSlider({
  auto: true,
  //loop: true,
  pauseOnHover: true,
  pause: 3000,
  item: 6,
  //pager: false,
  easing: 'cubic-bezier(0.25, 0, 0.25, 1)',
  speed: 600,
  slideMargin: 15,
  slideMove: 2,
  prevHtml: '<?php echo functions::draw_fonticon('fa-chevron-left', 'style="font-size: 2em;"'); ?>',
  nextHtml: '<?php echo functions::draw_fonticon('fa-chevron-right', 'style="font-size: 2em;"'); ?>',
  galleryMargin: 15,
  //enableDrag: false,
  responsive : [
    {
      breakpoint:800,
      settings: {
        item:3,
        slideMove:1,
        slideMargin:6,
      }
    },
    {
      breakpoint:480,
      settings: {
        item:2,
        slideMove:1
      }
    }
  ]
});
</script>
