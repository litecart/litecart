<div id="box-information-links" class="box">

  <h2 class="title"><?php echo language::translate('title_information', 'Information'); ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <?php foreach ($pages as $page) { ?>
    <li<?php echo (!empty($page['active']) ? ' class="active"' : ''); ?>><a href="<?php echo htmlspecialchars($page['link']); ?>"><?php echo $page['title']; ?></a></li>
    <?php } ?>
  </ul>

</div>