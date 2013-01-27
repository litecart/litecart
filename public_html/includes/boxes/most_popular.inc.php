<?php
  $system->functions->draw_fancybox('a.fancybox');
  
  $products_query = $system->functions->catalog_products_query(array('sort' => 'popularity', 'limit' => 8));
  
  if ($system->database->num_rows($products_query) == 0) {
    $products_query = $system->functions->catalog_products_query(array('sort' => 'popularity', 'limit' => 8));
  }
  
  if ($system->database->num_rows($products_query) == 0) return;
?>
<div class="box" id="box-popular">
  <div class="heading"><h3><?php echo $system->language->translate('title_popular', 'Popular'); ?></h3></div>
  <div class="content">
    <ul class="listing-wrapper products">
<?php
  while ($listing_product = $system->database->fetch($products_query)) {
    echo $system->functions->draw_listing_product($listing_product);
  }
?>
    </ul>
    <p style="text-align: right"><a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'search.php', array('sort' => 'popularity')); ?>"><?php echo $system->language->translate('title_view_more', 'View more'); ?></a></p>
  </div>
</div>