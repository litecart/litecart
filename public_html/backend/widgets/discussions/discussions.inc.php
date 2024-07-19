<?php

	$widget_discussions_cache_token = cache::token('widget_discussions', [], 'memory', 43200);
	if (cache::capture($widget_discussions_cache_token, 43200, true)) {

		try {

			$url = 'https://www.litecart.net/feeds/discussions.rss';

			$client_info = [
				'platform' => PLATFORM_NAME,
				'version' => PLATFORM_VERSION,
			];

			$client = new http_client();
			$client->timeout = 10;
			$response = $client->call('GET', $url, $client_info);

			if (!$response) throw new Exception('No response');

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
#widget-discussions .topics {
	columns: auto 275px;
}
#widget-discussions .topic {
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

<div id="widget-discussions" class="widget card">
	<div class="card-header">
		<div class="card-title">
			<?php echo language::translate('title_most_recent_forum_topics', 'Most Recent Forum Topics'); ?>
		</div>
	</div>

	<div class="card-body">
		<div class="topics">
			<?php foreach ($discussions as $item) { ?>
			<div class="topic">
				<div class="title"><a href="<?php echo functions::escape_html((string)$item->link); ?>" target="_blank"><?php echo functions::escape_html((string)$item->title); ?></a></div>
				<div class="description"><?php echo strtr(language::translate('text_posted_date_by_author', 'Posted %date by %author'), ['%date' => language::strftime('%e %b', strtotime($item->pubDate)), '%author' => (string)$item->author]); ?></div>
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
