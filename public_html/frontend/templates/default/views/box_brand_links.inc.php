<section id="box-brand-links" class="box">

  <h2 class="title"><?php echo language::translate('title_brands', 'Brands'); ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <?php foreach ($brands as $brand) { ?>
    <li<?php echo (!empty($brand['active']) ? ' class="active"' : ''); ?>><a href="<?php echo htmlspecialchars($brand['link']); ?>"><?php echo $brand['name']; ?></a></li>
    <?php } ?>
  </ul>

</section>
