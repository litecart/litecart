<aside class="column-left shadow rounded-corners">
  <div class="box" id="box-information-links">
    <div class="heading"><h3><?php echo language::translate('title_customer_service', 'Customer Service'); ?></h3></div>
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