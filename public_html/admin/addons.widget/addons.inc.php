<?php

  $widget_addons_cache_token = cache::token('widget_addons', ['language'], 'file', 43200);
  if (cache::capture($widget_addons_cache_token, 43200, true)) {

    try {
      $url = document::link('https://www.litecart.net/feeds/addons');

      $store_info = [
        'platform' => PLATFORM_NAME,
        'version' => PLATFORM_VERSION,
        'name' => settings::get('store_name'),
        'email' => settings::get('store_email'),
        'language_code' => settings::get('store_language_code'),
        'country_code' => settings::get('store_country_code'),
        'url' => document::ilink(''),
      ];

      $client = new wrap_http();
      $client->timeout = 10;

      $response = $client->call('POST', $url, $store_info);

      if (!$response) throw new Exception('No response');

      libxml_use_internal_errors(true);
      $rss = simplexml_load_string($response);

      foreach (libxml_get_errors() as $error) throw new Exception($error->message);

      if (!empty($rss->channel->item)) {

        $addons = [];
        foreach ($rss->channel->item as $item) {
          $addons[] = $item;
          if (count($addons) == 16) break;
        }
?>
<style>
#widget-addons .row [class^="col-"] .description {
  opacity: 0.85;
}
</style>

<div id="widget-addons" class="widget panel card-default">
  <div class="card-header">
    <div class="card-title">
      <?php echo language::translate('title_latest_addons', 'Latest Add-ons'); ?>
    </div>
  </div>

  <div class="card-body">
    <div class="row">
      <?php foreach ($addons as $item) { ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="title"><a href="<?php echo functions::escape_html((string)$item->link); ?>" target="_blank"><?php echo functions::escape_html((string)$item->title); ?></a></div>
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
