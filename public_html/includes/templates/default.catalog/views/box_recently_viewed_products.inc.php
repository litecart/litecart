<div id="box-recently-viewed-products" class="box">
  <h3 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h3>
  <div class="content">
    <ul class="list-horizontal">
      <?php foreach ($products as $product) { ?>
      <li><a href="<?php echo htmlspecialchars($product['link']); ?>"><img src="<?php echo htmlspecialchars($product['thumbnail']); ?>" alt="" /></a></li>
      <?php } ?>
    </ul>
  </div>
</div>