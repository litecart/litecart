<?php

  $widget_discussions_cache_id = cache::cache_id('widget_discussions');
  if (cache::capture($widget_discussions_cache_id, 'file', 21600, true)) {

    $url = document::link('https://www.litecart.net/feeds/discussions.rss');

    $response = @functions::http_fetch($url, null, false, false, true);
    $rss = @simplexml_load_string($response);

    if (!empty($rss->channel->item)) {

      $columns = array();

      $col = 0;
      $count = 0;
      $total = 0;
      foreach ($rss->channel->item as $item) {
        if (!isset($count) || $count == 3) {
          $count = 0;
          $col++;
        }
        $columns[$col][] = $item;
        $count++;
        $total++;
        if ($total == 12) break;
      }
?>
<div class="widget">
  <table style="width: 100%;" class="dataTable">
    <tr class="header">
      <th colspan="4"><?php echo language::translate('title_most_recent_forum_topics', 'Most Recent Forum Topics'); ?></th>
    </tr>
    <tr>
<?php
      foreach ($columns as $column) {
        echo '<td style="vertical-align: top;">' . PHP_EOL
           . '  <table style="width: 100%;">' . PHP_EOL;
        foreach ($column as $item) {
?>
        <tr>
          <td><a href="<?php echo htmlspecialchars((string)$item->link); ?>" target="_blank"><?php echo htmlspecialchars((string)$item->title); ?></a><br/>
            <span style="color: #666;"><?php echo language::strftime('%e %b', strtotime($item->pubDate)); ?> <?php echo language::translate('text_by', 'by'); ?> <?php echo (string)$item->author; ?></span>
          </td>
        </tr>
<?php
        }
        echo '  </table>' . PHP_EOL
           . '</td>' . PHP_EOL;
      }
?>
    </tr>
  </table>
</div>
<?php
  }

    cache::end_capture($widget_discussions_cache_id);
  }
?>