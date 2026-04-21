<?php

	document::load_style('app://assets/chartist/chartist.min.css', 'chartist');
	document::load_script('app://assets/chartist/chartist.min.js', 'chartist');

	// Statistics data for the last 30 days (cached for 5 minutes)
	$widget_website_traffic_cache_token = cache::token('widget_website_traffic', ['site'], 'memory', 300);
	if (!$stats = cache::get($widget_website_traffic_cache_token)) {

		$stats = database::query(
			"select count(id) as total_visits, sum(pageviews) as total_pageviews, created_at, date_format(created_at, '%Y-%m-%d') as date
			from ". DB_TABLE_PREFIX ."visitors
			where created_at >= '". date('Y-m-d H:i:s', strtotime('-30 days')) ."'
				group by date
			order by created_at asc;"
		)->fetch_all(function($day){
			return [
				'total_visits' => $day['total_visits'],
				'total_pageviews' => $day['total_pageviews'],
			];
		}, 'date');

		for ($timestamp=time(); strtotime('-1 month') <= $timestamp; $timestamp = strtotime('-1 day', $timestamp)) {

			$stats[date('Y-m-d', $timestamp)]['label'] = date('j', $timestamp);

			if (!isset($stats[date('Y-m-d', $timestamp)]['total_visits'])) {
				$stats[date('Y-m-d', $timestamp)]['total_visits'] = 0;
			}

			if (!isset($stats[date('Y-m-d', $timestamp)]['total_pageviews'])) {
				$stats[date('Y-m-d', $timestamp)]['total_pageviews'] = 0;
			}
		}

		ksort($stats);

		cache::set($widget_website_traffic_cache_token, $stats);
	}

	// Online Visitors
	$visitors = database::query(
		"select * from ". DB_TABLE_PREFIX ."visitors
		where updated_at > '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'
		order by updated_at desc;"
	)->fetch_all(function(&$row){

		if (strtotime($row['updated_at']) > strtotime('-5 minutes')) {
			$row['icon'] = f::draw_fonticon('icon-circle', 'style="color: #66cc66;"');
		} elseif (strtotime($row['updated_at']) > strtotime('-10 minutes')) {
			$row['icon'] = f::draw_fonticon('icon-circle', 'style="color: #ffcc66;"');
		} else {
			$row['icon'] = f::draw_fonticon('icon-clock', 'style="color: #999;"');
		}
	});

?>
<style>
#chart-visits .ct-series .ct-line {
	stroke-width: 1px;
	stroke-dasharray: 5,5;
}

#chart-visits .ct-series .ct-point {
	stroke-width: 4px;
}

#chart-visits .ct-line {
	stroke: #00192e;
}

#chart-visits .ct-point {
	stroke: #00192e;
}

#chart-visits .ct-area {
	fill: #87aefd;
}
</style>

<div class="widget">
	<div class="card">
		<div class="card-header">
			<h2 class="card-title"><?php echo t('title_website_traffic', 'Website Traffic'); ?></h2>
		</div>

		<div class="card-body">
			<div id="chart-visits" style="height: 200px;" title="<?php echo t('title_visits', 'Visits'); ?>"></div>
		</div>

		<div style="max-height: 300px; overflow: hidden auto;">
			<table class="table table-striped data-table">
				<thead>
					<tr>
						<th></th>
						<th class="main"><?php echo t('title_visitor', 'Visitor'); ?></th>
						<th><?php echo t('title_country', 'Country'); ?></th>
						<th><?php echo t('title_pageviews', 'Pageviews'); ?></th>
						<th><?php echo t('title_referrer', 'Referrer'); ?></th>
						<th class="text-center"><?php echo t('title_last_active', 'Last Active'); ?></th>
						<?php if (is_dir(FS_DIR_APP . 'backend/apps/firewall/')) { ?>
						<th></th>
						<?php } ?>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($visitors as $visitor) { ?>
					<tr>
						<td><?php echo $visitor['icon']; ?></td>
						<td>
							<div>
								<a target="_blank" href="https://ip-api.com/#<?php echo urlencode($visitor['ip_address']); ?>" title="<?php echo f::escape_html($visitor['user_agent']); ?>">
									<?php echo f::escape_html($visitor['hostname']); ?>
								</a>
							</div>
							<div>
							<a target="_blank" href="<?php echo f::escape_html($visitor['last_page']); ?>">
								<small>
									<?php echo f::escape_html(f::string_ellipsis($visitor['last_page'], 250)); ?>
								</small>
							</a>
						</div>
						</td>
						<td class="text-center"><?php echo $visitor['country_code']; ?></td>
						<td class="text-center"><?php echo $visitor['pageviews']; ?></td>
						<td>
							<?php if (!empty($visitor['referrer'])) { ?>
							<a target="_blank" href="<?php echo f::escape_html($visitor['referrer']); ?>" title="<?php echo f::escape_html($visitor['referrer']); ?>">
								<?php echo f::escape_html(parse_url($visitor['referrer'], PHP_URL_HOST)); ?>
							</a>
							<?php } else { ?>
							<em><?php echo t('title_direct', 'Direct'); ?></em>
							<?php } ?>
						</td>
						<td class="text-end"><?php echo f::datetime_when($visitor['updated_at']); ?></td>
						<?php if (is_dir(FS_DIR_APP . 'backend/apps/firewall/')) { ?>
						<td>
							<a class="btn btn-default" href="<?php echo document::href_ilink('firewall/edit_blacklist_entry', ['ip_address' => $visitor['ip_address'], 'hostname' => $visitor['hostname'], 'user_agent' => $visitor['user_agent']]); ?>">
								<?php echo f::draw_fonticon('icon-plus'); ?> <?php echo t('title_blacklist', 'Blacklist'); ?>
							</a>
						</td>
						<?php } ?>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
	var data = {
		labels: <?php echo f::format_json(array_column($stats, 'label'), ''); ?>,
		series: <?php echo f::format_json([array_column($stats, 'total_visits')], ''); ?>
	};

	var options = {
		showArea: true,
		lineSmooth: true
	};

	var responsiveOptions = [
		['screen and (max-width: 640px)', {
			seriesBarDistance: 5,
			axisX: {
				labelInterpolationFnc: function (value) {
					return value[0];
				}
			}
		}]
	];

	new Chartist.LineChart('#chart-visits', data, options, responsiveOptions);
</script>