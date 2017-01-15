<?php
  $widget_discussions_cache_id = cache::cache_id('widget_discussions');
  if (cache::capture($widget_discussions_cache_id, 'file', 21600, true)) {

    $url = document::link('https://www.litecart.net/feeds/discussions.rss');

    $client = new http_client();
    $response = @$client->call($url);
    $rss = @simplexml_load_string($response);

    if (!empty($rss->channel->item)) {

      $columns = array();

      $col = 0;
      $count = 0;
      $total = 0;
      foreach ($rss->channel->item as $item) {
        $col++;
        if (!isset($count) || $count == 4) {
          $count = 0;
        }
        $columns[$col][] = $item;
        $count++;
        $total++;
        if ($total == 16) break;
      }
?>
<div class="widget panel panel-default">
  <div class="panel-heading"><?php echo language::translate('title_most_recent_forum_topics', 'Most Recent Forum Topics'); ?></div>
  <div class="panel-body">
      <div class="row">
      <?php foreach (array_keys($columns) as $key) { ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <ul class="list-unstyled">
          <?php foreach ($columns[$key] as $item) { ?>
          <li style="margin-bottom: 0.5em; white-space: word-wrap; text-overflow: ellipsis;">
            <a href="<?php echo htmlspecialchars((string)$item->link); ?>" target="_blank"><?php echo htmlspecialchars((string)$item->title); ?></a><br/>
            <span style="color: #777;"><?php echo strftime('%e %b', strtotime($item->pubDate)); ?> <?php echo language::translate('text_by', 'by'); ?> <?php echo (string)$item->author; ?></span>
          </li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php
      }
    cache::end_capture($widget_discussions_cache_id);
  }
?>