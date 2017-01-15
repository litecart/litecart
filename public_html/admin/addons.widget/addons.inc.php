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

    $client = new http_client();
    $response = @$client->call($url, $store_info);
    $rss = @simplexml_load_string($response);

    if (!empty($rss->channel->item)) {

      $columns = array();

      $col = 0;
      $count = 0;
      $total = 0;
      foreach ($rss->channel->item as $item) {
        $col++;
        if (!isset($count) || $count == 6) {
          $count = 0;
        }
        $columns[$col][] = $item;
        $count++;
        $total++;
        if ($total == 18) break;
      }
?>
<div class="widget panel panel-default">
  <div class="panel-heading"><?php echo language::translate('title_latest_addons', 'Latest Add-ons'); ?></div>
  <div class="panel-body">
    <div class="row">
      <?php foreach (array_keys($columns) as $key) { ?>
      <div class="col-sm-4 col-md-3 col-lg-2">
        <ul class="list-unstyled">
          <?php foreach ($columns[$key] as $item) { ?>
          <li style="margin-bottom: 0.5em;">
            <?php //echo strftime('%e %b', strtotime((string)$item->pubDate)) . ' - '; ?><a href="<?php echo htmlspecialchars((string)$item->link); ?>" target="_blank"><?php echo htmlspecialchars((string)$item->title); ?></a><br/>
            <span style="color: #666;"><?php echo (string)$item->description; ?></span>
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
    cache::end_capture($widget_addons_cache_id);
  }
?>