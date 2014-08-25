<aside class="shadow rounded-corners">
  <div class="box" id="box-information-links">
    <div class="heading"><h3><?php echo language::translate('title_customer_service', 'Customer Service'); ?></h3></div>
    <div class="content">
      <nav>
        <ul class="list-vertical">
          <?php foreach ($pages as $page) echo '<li'. ((isset($_GET['page_id']) && $_GET['page_id'] == $page['id']) ? ' class="active"' : '') .'><a href="'. htmlspecialchars($page['link']) .'">'. $page['title'] .'</a></li>' . PHP_EOL; ?>
        </ul>
      </nav>
    </div>
  </div>
</aside>