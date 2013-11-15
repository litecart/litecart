<?php
  if (!is_object($product)) return;
  
  $product_groups = array();
  if ($product->product_group_ids) {
    foreach ($product->product_group_ids as $product_group) {
      $product_groups[] = "find_in_set('". $system->database->input($product_group) ."', p.product_groups)";
    }
  }
  
  $keywords = array();
  if ($product->keywords != '') {
    foreach (explode(',', $product->keywords) as $keyword) {
      $keyword = trim($keyword);
      if (empty($keyword)) continue;
      $keywords[] = $keyword;
    }
  }
  
  $products_query = $system->functions->catalog_products_query(array(
    'product_name' => $product->name[$system->language->selected['code']],
    'categories' => isset($_GET['category_id']) ? array($_GET['category_id']) : array_keys($product->categories),
    'manufacturers' => array($product->manufacturer_id),
    'product_groups' => $product_groups,
    'exclude_products' => $product->id,
    'keywords' => $keywords,
    'sort' => 'occurrences',
    'limit' => 8,
  ));
  if ($system->database->num_rows($products_query) == 0) return;
  
  $system->functions->draw_fancybox('a.fancybox');
?>
<div class="box" id="box-similar-products">
  <div class="heading"><h3><?php echo $system->language->translate('title_similar_products', 'Similar Products'); ?></h3></div>
  <div class="content">
    <ul class="listing-wrapper products">
<?php
  while ($listing_product = $system->database->fetch($products_query)) {
    if (empty($listing_product['occurrences'])) break;
    echo $system->functions->draw_listing_product($listing_product);
  }
?>
    </ul>
  </div>
</div>