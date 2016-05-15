<?php

  $widget_addons_cache_id = cache::cache_id('widget_addons');
  if (cache::capture($widget_addons_cache_id, 'file', 43200, true)) {

    $url = document::link('https://www.litecart.net/feeds/addons', array('whoami' => document::ilink(''), 'version' => PLATFORM_VERSION));

    $store_info = array(
      'platform' => PLATFORM_NAME,
      'version' => PLATFORM_VERSION,
      'name' => settings::get('store_name'),
      'email' => settings::get('store_email'),
      'language_code' => settings::get('store_language_code'),
      'country_code' => settings::get('store_country_code'),
      'url' => document::ilink(''),
    );

    $response = @functions::http_fetch($url, $store_info, false, false, true);
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
      <th colspan="4"><?php echo language::translate('title_latest_addons', 'Latest Add-ons'); ?></th>
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
          <td><?php //echo language::strftime('%e %b', strtotime((string)$item->pubDate)) . ' - '; ?><a href="<?php echo htmlspecialchars((string)$item->link); ?>" target="_blank"><?php echo htmlspecialchars((string)$item->title); ?></a><br/>
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
<?php
    }

    cache::end_capture($widget_addons_cache_id);
  }
?>