<?php

	$widget_counters_cache_token = cache::token('widget_counters', ['site'], 'memory', 60);
	if (!$stats = cache::get($widget_counters_cache_token)) {

		$stats = [];

		$order_statuses = database::query(
			"select id from ". DB_TABLE_PREFIX ."order_statuses where is_sale;"
		)->fetch_all('id');

		// Sales Today
		$stats['sales_today'] = database::query(
			"select sum(total - total_tax) as sales_today
			from ". DB_TABLE_PREFIX ."orders
			where order_status_id in ('". implode("', '", $order_statuses) ."')
			and created_at >= '". date('Y-m-d H:i:s', strtotime('today')) ."';"
		)->fetch('sales_today');

		// Num Orders Today
		$stats['num_orders_today'] = database::query(
			"select count(id) as num_orders_today
			from ". DB_TABLE_PREFIX ."orders
			where order_status_id in ('". implode("', '", $order_statuses) ."')
			and created_at >= '". date('Y-m-d H:i:s', strtotime('today')) ."';"
		)->fetch('num_orders_today');

		// Users Online
		$stats['users_online'] = database::query(
			"select count(id) as num_users_online
			from ". DB_TABLE_PREFIX ."visitors
			where updated_at >= '". date('Y-m-d H:i:s', strtotime('-15 minutes')) ."';"
		)->fetch('num_users_online');

		// 30 days of sales for sparkline
		$stats['sales_30_days_this_year'] = database::query(
			"select sum(o.total - o.total_tax) as total_sales
			from ". DB_TABLE_PREFIX ."orders o
			where o.order_status_id in ('". implode("', '", $order_statuses) ."')
			and o.created_at >= '". date('Y-m-d H:i:s', strtotime('-30 days')) ."';"
		)->fetch_all('total_sales');

		$stats['sales_30_days_last_year'] = database::query(
			"select sum(o.total - o.total_tax) as total_sales
			from ". DB_TABLE_PREFIX ."orders o
			where o.order_status_id in ('". implode("', '", $order_statuses) ."')
			and o.created_at between '". date('Y-m-d H:i:s', strtotime('-30 days', strtotime('-1 year'))) ."' and '". date('Y-m-d H:i:s', strtotime('-1 year')) ."';"
		)->fetch_all('total_sales');

		$stats['last_year_comparison'] = !empty($stats['sales_30_days_last_year']) ? ((array_sum($stats['sales_30_days_this_year']) - array_sum($stats['sales_30_days_last_year'])) / array_sum($stats['sales_30_days_last_year']) * 100) : 0;

		// Trend
		$stats['sales_14_days_average'] = database::query(
			"select avg(o.total - o.total_tax) as avg_sales
			from ". DB_TABLE_PREFIX ."orders o
			where o.order_status_id in ('". implode("', '", $order_statuses) ."')
			and o.created_at >= '". date('Y-m-d H:i:s', strtotime('-14 days')) ."';"
		)->fetch_all('avg_sales');

		$stats['sales_21_days_average'] = database::query(
			"select avg(o.total - o.total_tax) as avg_sales
			from ". DB_TABLE_PREFIX ."orders o
			where o.order_status_id in ('". implode("', '", $order_statuses) ."')
			and o.created_at >= '". date('Y-m-d H:i:s', strtotime('-21 days')) ."';"
		)->fetch_all('avg_sales');

		$stats['trend_percentage'] = !empty($stats['sales_21_days_average']) ? ((array_sum($stats['sales_14_days_average']) - array_sum($stats['sales_21_days_average'])) / array_sum($stats['sales_21_days_average']) * 100) : 0;

		cache::set($widget_counters_cache_token, $stats);
	}

	// Halt if no stats are available
	if (!$stats) {
		return;
	}

?>
<style>
.text-xxl {
	font-size: 2rem;
	font-weight: bold;
}
</style>

<div id="stats" class="widget">

	<div class="row">

		<div class="col">
			<div class="card">
				<div class="card-header">
					<div class="card-title">
						<?php echo t('title_users_online', 'Users Online'); ?>
					</div>
				</div>

				<div class="card-body">
					<div class="text-xxl">
						<?php echo f::draw_fonticon('icon-circle', 'style="color: #62d100; font-size: .5em;"'); ?> <?php echo f::format_number($stats['users_online']); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="col">
			<div class="card">
				<div class="card-header">
					<div class="card-title">
						<?php echo t('title_orders_today', 'Orders Today'); ?>
					</div>
				</div>

				<div class="card-body">
					<div class="text-xxl">
						<?php echo f::draw_fonticon('icon-orders', 'style="color: #8fc329;"') ?> <?php echo (int)$stats['num_orders_today']; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="col">
			<div class="card">
				<div class="card-header">
					<div class="card-title">
						<?php echo t('title_sales_today', 'Sales Today'); ?>
					</div>
				</div>

				<div class="card-body">
					<div class="text-xxl">
						<?php echo f::draw_fonticon('icon-money-coins', 'style="color: #d9b600;"'); ?> <?php echo currency::format($stats['sales_today'], false, settings::get('store_currency_code')); ?>
					</div>
				</div>
			</div>
		</div>


		<div class="col">
			<div class="card">
				<div class="card-header">
					<div class="card-title">
						<?php echo t('title_last_year_comparison', 'Last Year Comparison'); ?>
					</div>
				</div>

				<div class="card-body">
					<div class="text-xxl">
						<?php if ($stats['last_year_comparison'] >= 0) { ?>
							<?php echo f::draw_fonticon('icon-arrow-northeast', 'style="color: #62d100;"'); ?> +<?php echo number_format($stats['last_year_comparison'], 1); ?>%
						<?php } else { ?>
							<?php echo f::draw_fonticon('icon-arrow-southeast', 'style="color: #d10068;"'); ?> <?php echo number_format($stats['last_year_comparison'], 1); ?>%
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="col">
			<div class="card">
				<div class="card-header">
					<div class="card-title">
						<?php echo t('title_14_days_trend', '14 Days Trend'); ?>
					</div>
				</div>

				<div class="card-body">
					<div class="text-xxl">
						<?php if ($stats['trend_percentage'] >= 0) { ?>
							<?php echo f::draw_fonticon('icon-arrow-northeast', 'style="color: #62d100;"'); ?> +<?php echo number_format($stats['trend_percentage'], 1); ?>%
						<?php } else { ?>
							<?php echo f::draw_fonticon('icon-arrow-southeast', 'style="color: #d10068;"'); ?> <?php echo number_format($stats['trend_percentage'], 1); ?>%
						<?php } ?>
					</div>
				</div>
			</div>
	</div>

</div>