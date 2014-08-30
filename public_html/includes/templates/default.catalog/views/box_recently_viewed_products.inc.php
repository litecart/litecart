<aside class="column-left box shadow rounded-corners">
  <div id="box-recently-viewed-products" class="box">
    <div class="heading"><h3><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h3></div>
    <div class="content">
      <ul class="list-horizontal">
        <?php foreach ($products as $product) { ?>
        <li><a href="<?php echo htmlspecialchars($product['link']); ?>"><img src="<?php echo htmlspecialchars($product['thumbnail']); ?>" alt="" /></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
</aside>