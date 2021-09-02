<?php

  $widget_addons_cache_token = cache::token('widget_addons', [], 'memory', 43200);
  if (cache::capture($widget_addons_cache_token, 43200, false)) {

    try {
      $url = 'https://www.litecart.net/feeds/addons';

      $site_info = [
        'platform' => PLATFORM_NAME,
        'version' => PLATFORM_VERSION,
        'name' => settings::get('site_name'),
        'email' => settings::get('site_email'),
        'language_code' => settings::get('site_language_code'),
        'country_code' => settings::get('site_country_code'),
        'url' => document::ilink('f:'),
      ];

      $client = new wrap_http();
      $client->timeout = 10;

      $response = $client->call('POST', $url, $site_info);

      if (!$response) throw new Exception('No response');

      libxml_use_internal_errors(true);
      $rss = simplexml_load_string($response);

      foreach (libxml_get_errors() as $error) throw new Exception($error->message);

      if (!empty($rss->channel->item)) {

        $addons = [];
        foreach ($rss->channel->item as $item) {
          $addons[] = $item;
          if (count($addons) == 20) break;
        }
?>
<style>
#widget-addons .addons {
  columns: auto 250px;
}
#widget-addons .addon {
  margin-bottom: 1em;
  break-inside: avoid;
}
#widget-addons .description {
  opacity: 0.85;
}
#widget-addons .title, #widget-addons .description {
  white-space: nowrap;
  overflow-x: hidden;
  text-overflow: ellipsis;
}
</style>

<div id="widget-addons" class="card card-widget">
  <div class="card-header">
    <div class="card-title">
      <?php echo language::translate('title_latest_addons', 'Latest Add-ons'); ?>
    </div>
  </div>

  <div class="card-body">
    <div class="addons">
      <?php foreach ($addons as $item) { ?>
      <div class="addon">
        <div class="title"><a href="<?php echo htmlspecialchars((string)$item->link); ?>" target="_blank"><?php echo htmlspecialchars((string)$item->title); ?></a></div>
        <div class="description"><?php echo (string)$item->description; ?></div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php
      }
    } catch(Exception $e) {
      // Do nothing
    }
    cache::end_capture($widget_addons_cache_token);
  }
