<section id="box-brand-links" class="box">

  <h2 class="title"><?php echo language::translate('title_brands', 'Brands'); ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <?php foreach ($brands as $brand) { ?>
    <li><a class="nav-item<?php echo !empty($brand['active']) ? ' class="active"' : ''; ?>" href="<?php echo functions::escape_html($brand['link']); ?>"><?php echo $brand['name']; ?></a></li>
    <?php } ?>
  </ul>

</section>
