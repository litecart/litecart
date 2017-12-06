<div id="box-manufacturers-links" class="box">

  <h2 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h2>

  <ul class="nav nav-pills nav-primary nav-stacked">
    <?php foreach ($manufacturers as $manufacturer) { ?>
    <li<?php echo (!empty($manufacturer['active']) ? ' class="active"' : ''); ?>><a href="<?php echo htmlspecialchars($manufacturer['link']); ?>"><?php echo $manufacturer['name']; ?></a></li>
    <?php } ?>
  </ul>

</div>
