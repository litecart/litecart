<?php
  
  $url = document::link('http://www.litecart.net/feeds/addons', array('whoami' => document::link(WS_DIR_HTTP_HOME), 'version' => PLATFORM_VERSION));
  $rss = functions::http_fetch($url);
  
  $rss = simplexml_load_string($rss);
  
  if (empty($rss->channel->item)) return;
  
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
      <th colspan="4" align="left"><?php echo language::translate('title_latest_addons', 'Latest Add-ons'); ?></th>
    </tr>
    <tr>
<?php
  foreach ($columns as $column) {
    echo '<td style="vertical-align: top;">' . PHP_EOL
       . '  <table style="width: 100%;">' . PHP_EOL;
    foreach ($column as $item) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
        <tr>
          <td><?php //echo strftime('%e %b', strtotime((string)$item->pubDate)) . ' - '; ?><a href="<?php echo (string)$item->link; ?>" target="_blank"><?php echo (string)$item->title; ?></a><br/>
            <span style="color: #666;"><?php echo (string)$item->description; ?></span>
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