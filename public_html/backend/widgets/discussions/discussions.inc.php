<?php

	$widget_discussions_cache_token = cache::token('widget_discussions', [], 'memory', 43200);
	if (!$discussions = cache::get($widget_discussions_cache_token, 43200, true)) {

		try {

			$discussions = [];

			$client = new http_client();
			$client->timeout = 10;

			$response = $client->call('GET', 'https://www.litecart.net/feeds/discussions.rss', [
				'platform' => PLATFORM_NAME,
				'version' => PLATFORM_VERSION,
			]);

			if (!$response) {
				throw new Exception('No response');
			}

			libxml_use_internal_errors(true);
			$rss = simplexml_load_string($response);

			foreach (libxml_get_errors() as $error) {
				throw new Exception($error->message);
			}

			if (empty($rss->channel->item)) {
				throw new Exception('No discussions found');
			}

			foreach ($rss->channel->item as $item) {

				$discussions[] = [
					'title' => (string)$item->title,
					'link' => (string)$item->link,
					'date' => (string)$item->pubDate,
					'author' => (string)$item->author,
				];

				if (count($discussions) == 20) break;
			}

		} catch (Exception $e) {
			// Do nothing
		}

		cache::set($widget_discussions_cache_token, $discussions);
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
			<?php echo t('title_most_recent_forum_topics', 'Most Recent Forum Topics'); ?>
		</div>
	</div>

	<div class="card-body">
		<div class="topics">
			<?php foreach ($discussions as $discussion) { ?>
			<div class="topic">
				<div class="title">
					<a href="<?php echo functions::escape_attr($discussion['link']); ?>" target="_blank">
						<?php echo functions::escape_html($discussion['title']); ?>
					</a>
				</div>
				<div class="description">
					<?php echo strtr(t('text_posted_date_by_author', 'Posted {date} by {author}'), [
						'{date}' => functions::datetime_format('%e %b', strtotime($discussion['date'])),
						'{author}' => $discussion['author']
					]); ?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>