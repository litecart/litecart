<div id="box-information-links" class="box">

  <h2 class="title"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></h2>

  <ul class="list-unstyled">
    <?php foreach ($pages as $page) { ?>
    <li<?php echo (!empty($page['active']) ? ' class="active"' : ''); ?>><a href="<?php echo htmlspecialchars($page['link']); ?>"><?php echo $page['title']; ?></a></li>
    <?php } ?>
  </ul>

</div>