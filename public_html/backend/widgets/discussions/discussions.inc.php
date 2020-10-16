<?php
  $widget_discussions_cache_token = cache::token('widget_discussions', [], 'memory', 43200);
  if (cache::capture($widget_discussions_cache_token, 43200, true)) {

    try {

      $url = document::link('https://www.litecart.net/feeds/discussions.rss');

      $client = new wrap_http();
      $client->timeout = 10;
      $response = @$client->call('GET', $url);
      libxml_use_internal_errors(true);
      $rss = simplexml_load_string($response);

      foreach (libxml_get_errors() as $error) throw new Exception($error->message);

      if (!empty($rss->channel->item)) {

        $discussions = [];
        foreach ($rss->channel->item as $item) {
          $discussions[] = $item;
          if (count($discussions) == 20) break;
        }
?>
<style>
#widget-discussions .threads {
  columns: auto 250px;
}
#widget-discussions .thread {
  margin-bottom: 1em;
  break-inside: avoid;
}
#widget-discussions .description {
  opacity: 0.85;
}
#widget-discussions .title, #widget-discussions .description {
  white-space: nowrap;
  overflow-x: hidden;
  text-overflow: ellipsis;
}
</style>

<div id="widget-discussions" class="widget panel panel-default">
  <div class="panel-heading">
    <?php echo language::translate('title_most_recent_forum_topics', 'Most Recent Forum Topics'); ?>
  </div>

  <div class="panel-body">
      <div class="threads">
      <?php foreach ($discussions as $item) { ?>
      <div class="thread">
        <div class="title"><a href="<?php echo htmlspecialchars((string)$item->link); ?>" target="_blank"><?php echo htmlspecialchars((string)$item->title); ?></a></div>
        <div class="description"><?php echo language::strftime('%e %b', strtotime($item->pubDate)); ?> <?php echo language::translate('text_by', 'by'); ?> <?php echo (string)$item->author; ?></div>
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
    cache::end_capture($widget_discussions_cache_token);
  }
