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

      $addons = array();
      foreach ($rss->channel->item as $item) {
        $addons[] = $item;
        if (count($addons) == 16) break;
      }
?>
<style>
#widget-addons .row [class^="col-"] > * {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
#widget-addons .row [class^="col-"] .description {
  opacity: 0.85;
}
</style>

<div id="widget-addons" class="widget panel panel-default">
  <div class="panel-heading"><?php echo language::translate('title_latest_addons', 'Latest Add-ons'); ?></div>
  <div class="panel-body">
    <div class="row">
      <?php foreach ($addons as $item) { ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="title"><?php //echo strftime('%e %b', strtotime((string)$item->pubDate)) . ' - '; ?><a href="<?php echo htmlspecialchars((string)$item->link); ?>" target="_blank"><?php echo htmlspecialchars((string)$item->title); ?></a></div>
        <div class="description"><?php echo (string)$item->description; ?></div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php
    }
    cache::end_capture($widget_addons_cache_id);
  }
