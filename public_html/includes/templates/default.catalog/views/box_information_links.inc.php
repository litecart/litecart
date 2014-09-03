<aside class="column-left shadow rounded-corners">
  <div id="box-information-links" class="box">
    <div class="heading"><h3><?php echo language::translate('title_information', 'Information'); ?></h3></div>
    <div class="content">
      <nav>
        <ul class="list-vertical">
          <?php foreach ($pages as $page) { ?>
          <li<?php echo ((isset($_GET['page_id']) && $_GET['page_id'] == $page['id']) ? ' class="active"' : ''); ?>><a href="<?php echo htmlspecialchars($page['link']); ?>"><?php echo $page['title']; ?></a></li>
          <?php } ?>
        </ul>
      </nav>
    </div>
  </div>
</aside>