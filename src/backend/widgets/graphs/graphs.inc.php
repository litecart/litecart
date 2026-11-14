<?php

	$widget_graphs_cache_token = cache::token('widget_graphs', ['site'], 'memory', 300);
	if (cache::capture($widget_graphs_cache_token)) {

		// Monthly Sales

		$monthly_sales = [];

		database::query(
			"select count(id) as num_orders,
				sum(total - total_tax) as total_sales,
				date_format(created_at, '%Y') as year,
				date_format(created_at, '%m') as month
			from ". DB_PREFIX ."orders
			where order_status_id in (
				select id from ". DB_PREFIX ."order_statuses
				where is_sale
			)
			and created_at between '". date('Y-m-01 00:00:00', strtotime('-36 months')) ."' and '". date('Y-m-t 23:59:59') ."'
			group by year, month
			order by year, month asc;"
		)->each(function($order) use (&$monthly_sales) {
			$monthly_sales[$order['year']][$order['month']] = $order;
		});

		for ($timestamp = date('Y', strtotime('-36 months')); $timestamp < strtotime('Dec 31'); $timestamp = strtotime('+1 months', $timestamp)) {
			$year = date('Y', $timestamp);
			$month = date('m', $timestamp);
			$monthly_sales[$year][$month]['year'] = $year;
			$monthly_sales[$year][$month]['month'] = $month;
			$monthly_sales[$year][$month]['label'] = f::datetime_format('%b', $timestamp);
			if (!isset($monthly_sales[$year][$month]['num_orders'])) {
				$monthly_sales[$year][$month]['num_orders'] = 0;
			}
			if (!isset($monthly_sales[$year][$month]['total_sales'])) {
				$monthly_sales[$year][$month]['total_sales'] = 0;
			}
		}

		ksort($monthly_sales);
		foreach (array_keys($monthly_sales) as $year) {
			ksort($monthly_sales[$year]);
		}

		// Daily Sales
		switch (true) {

			// Western Week
			case (class_exists('IntlCalendar', false) && IntlCalendar::createInstance(null, language::$selected['locale'])->getFirstDayOfWeek() == IntlCalendar::DOW_SUNDAY):
				$daily_sales = [7 => [], 1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => []];
				break;

			// Middle-Eastern Week
			case (class_exists('IntlCalendar', false) && IntlCalendar::createInstance(null, language::$selected['locale'])->getFirstDayOfWeek() == IntlCalendar::DOW_SATURDAY):
				$daily_sales = [6 => [], 7 => [], 1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
				break;

			// ISO-8601 Week
			default:
				$daily_sales = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];
				break;
		}

		database::query(
			"select round(sum(total - total_tax)) as total_sales,
				weekday(created_at)+1 as weekday
			from ". DB_PREFIX ."orders
			where order_status_id in (
				select id from ". DB_PREFIX ."order_statuses
				where is_sale
			)
			and (created_at >= '". date('Y-m-d 00:00:00', strtotime('Monday this week')) ."')
			group by weekday
			order by weekday asc;"
		)->each(function($order) use (&$daily_sales) {
			$daily_sales[$order['weekday']]['total_sales'] = $order['total_sales'];
		});

		database::query(
			"select round(sum(total - total_tax) / count(distinct(date(created_at))), 2) as average_sales,
				weekday(created_at)+1 as weekday,
				group_concat(total - total_tax)
			from ". DB_PREFIX ."orders
			where order_status_id in (
				select id from ". DB_PREFIX ."order_statuses
				where is_sale
			)
			and (created_at > '". date('Y-m-d H:i:s', strtotime('-3 months', strtotime('Monday this week'))) ."' and created_at < '". date('Y-m-d 00:00:00', strtotime('Monday this week')) ."')
			group by weekday
			order by weekday asc;"
		)->each(function($order) use (&$daily_sales) {
			$daily_sales[$order['weekday']]['average_sales'] = $order['average_sales'];
		});

		for ($timestamp=time(); strtotime('-7 days') < $timestamp; $timestamp = strtotime('-1 day', $timestamp)) {
			$daily_sales[date('N', $timestamp)]['label'] = f::datetime_format('%a', $timestamp);

			if (!isset($daily_sales[date('N', $timestamp)]['total_sales'])) {
				$daily_sales[date('N', $timestamp)]['total_sales'] = 0;
			}
			if (!isset($daily_sales[date('N', $timestamp)]['average_sales'])) {
				$daily_sales[date('N', $timestamp)]['average_sales'] = 0;
			}
		}

?>
<style>
#widget-graphs .card-body {
	padding-left: 1em;
	padding-right: 1em;
}

#chart-sales-monthly {
	--chart-series-1: #e6e9ef;
	--chart-series-2: #bcc6d4;
	--chart-highlight-from: #3ba5c6;
	--chart-highlight-to: #7152e5;
	--chart-label-color: #8b95a3;
}

#chart-sales-daily {
	--chart-series-1: #e6e9ef;
	--chart-series-2: #3ba5c6;
	--chart-label-color: #8b95a3;
}

.dark-mode #chart-sales-monthly {
	--chart-series-1: #343d5a;
	--chart-series-2: #4a5578;
	--chart-highlight-from: #4e90ad;
	--chart-highlight-to: #7b6cf6;
}

.dark-mode #chart-sales-daily {
	--chart-series-1: #424d6e;
	--chart-series-2: #4e90ad;
}
</style>

<div id="widget-graphs" class="widget">
	<div class="grid">
		<div class="col-md-8">
			<div class="card">
				<div class="card-header">
					<div class="card-title"><?= t('title_monthly_sales', 'Monthly Sales') ?></div>
				</div>

				<div class="card-body">
					<div id="chart-sales-monthly" style="width: 100%; height: 250px;" title="<?= f::escape_html(t('title_monthly_sales', 'Monthly Sales')) ?>"></div>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card">
				<div class="card-header">
					<div class="card-title"><?= t('title_daily_sales', 'Daily Sales') ?></div>
				</div>

				<div class="card-body">
					<div id="chart-sales-daily" style="width: 100%; height: 250px" title="<?= f::escape_html(t('title_daily_sales', 'Daily Sales')) ?>"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
waitFor('Chart', function(Chart) {

	var numberFormat = new Intl.NumberFormat(<?= f::format_json(language::$selected['code'], '') ?>);

	new Chart('#chart-sales-monthly', {
		type: 'bar',
		legend: true,
		labels: <?= f::format_json(array_column($monthly_sales[date('Y')], 'label'), '') ?>,
		series: [
			{
				name: <?= f::format_json(date('Y') - 2, '') ?>,
				data: <?= f::format_json(array_column($monthly_sales[date('Y')-2], 'total_sales'), '') ?>
			},
			{
				name: <?= f::format_json(date('Y') - 1, '') ?>,
				data: <?= f::format_json(array_column($monthly_sales[date('Y')-1], 'total_sales'), '') ?>
			},
			{
				name: <?= f::format_json(date('Y'), '') ?>,
				data: <?= f::format_json(array_column($monthly_sales[date('Y')], 'total_sales'), '') ?>,
				gradient: ['--chart-highlight-from', '--chart-highlight-to']
			}
		],
		axisY: {
			format: function(value) {
				return numberFormat.format(value);
			}
		}
	});

	new Chart('#chart-sales-daily', {
		type: 'bar',
		legend: true,
		labels: <?= f::format_json(array_column($daily_sales, 'label'), '') ?>,
		series: [
			{
				name: <?= f::format_json(t('title_sales_this_week', 'This week'), '') ?>,
				data: <?= f::format_json(array_column($daily_sales, 'total_sales'), '') ?>
			},
			{
				name: <?= f::format_json(t('title_sales_average', '3 month average'), '') ?>,
				data: <?= f::format_json(array_column($daily_sales, 'average_sales'), '') ?>
			}
		],
		axisY: {
			format: function(value) {
				return numberFormat.format(value);
			}
		}
	});

});
</script>
<?php
		cache::end_capture($widget_graphs_cache_token);
	}
?>
