<?php
  $products_query = $system->functions->catalog_products_query(array('campaign' => true, 'sort' => 'rand', 'limit' => 5));
  if ($system->database->num_rows($products_query) == 0) return;
  
  $system->functions->draw_fancybox('a.fancybox');
?>

<div class="box" id="box-campaigns">
  <div class="heading"><h3><?php echo $system->language->translate('title_campaigns', 'Campaigns'); ?></h3></div>
  <div class="content">
    <div class="listing-wrapper">
<?php
  while ($listing_product = $system->database->fetch($products_query)) {
    echo $system->functions->draw_listing_product($listing_product);
  }
?>
    </div>
  </div>
</div>