<aside class="column-left shadow rounded-corners">
  <div id="box-information-links" class="box">
    <h3 class="title"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></h3>
    <div class="content">
      <nav>
        <ul class="list-vertical">
          <?php foreach ($pages as $page) { ?>
          <li<?php echo (!empty($page['active']) ? ' class="active"' : ''); ?>><a href="<?php echo htmlspecialchars($page['link']); ?>"><?php echo $page['title']; ?></a></li>
          <?php } ?>
        </ul>
      </nav>
    </div>
  </div>
</aside>