<?php
  
  $cache_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'discussions.cache';
  
  if (file_exists($cache_file) && filemtime($cache_file) > strtotime('-6 hours')) {
    echo file_get_contents($cache_file);
    return;
  }
  
  ob_start();
    
  $url = document::link('http://forums.litecart.net/feed/rss/');
  
  $rss = @functions::http_fetch($url);
  $rss = @simplexml_load_string($rss);
  
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
            <span style="color: #666;"><?php echo strftime('%e %b', strtotime($item->pubDate)); ?> <?php echo language::translate('text_by', 'by'); ?> <?php echo (string)$item->author; ?></span>
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
  
  $buffer = ob_get_clean();
  
  file_put_contents($cache_file, $buffer);
  
  echo $buffer;
?>