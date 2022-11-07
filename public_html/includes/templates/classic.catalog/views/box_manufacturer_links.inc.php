<section id="box-manufacturer-links" class="box">

  <h2 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <?php foreach ($manufacturers as $manufacturer) { ?>
    <li<?php echo (!empty($manufacturer['active']) ? ' class="active"' : ''); ?>><a href="<?php echo functions::escape_html($manufacturer['link']); ?>"><?php echo $manufacturer['name']; ?></a></li>
    <?php } ?>
  </ul>

</section>
