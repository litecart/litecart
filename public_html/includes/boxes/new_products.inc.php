<?php
  $system->functions->draw_fancybox('a.fancybox');
?>
<div class="box" id="box-latest-products">
  <div class="heading"><h3><?php echo $system->language->translate('title_new_products', 'New Products'); ?></h3></div>
  <div class="content">
    <ul class="listing-wrapper products">
<?php
  $products_query = $system->functions->catalog_products_query(array('sort' => 'date', 'limit' => 8));
  while ($listing_product = $system->database->fetch($products_query)) {
    echo $system->functions->draw_listing_product($listing_product);
  }
?>
    </ul>
    <div style="margin-top: 10px; text-align: right"><a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'search.php', array('sort' => 'date')); ?>"><?php echo $system->language->translate('title_view_more', 'View more'); ?></a></div>
  </div>
</div>