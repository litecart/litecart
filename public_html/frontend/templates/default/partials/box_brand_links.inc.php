<section id="box-brand-links" class="card">

  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_brands', 'Brands'); ?></h2>
  </div>

  <div class="card-body">
    <ul class="nav nav-stacked nav-pills">
      <?php foreach ($brands as $brand) { ?>
      <li<?php echo (!empty($brand['active']) ? ' class="active"' : ''); ?>><a href="<?php echo functions::escape_html($brand['link']); ?>"><?php echo $brand['name']; ?></a></li>
      <?php } ?>
    </ul>
  </div>

</section>
