<?php

	administrator::require_login();

	document::$title[] = t('title_order_statuses', 'Order Statuses');

	breadcrumbs::add(t('title_orders', 'Orders'), document::ilink(__APP__.'/orders'));
	breadcrumbs::add(t('title_order_statuses', 'Order Statuses'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (!empty($_POST['change'])) {

		try {

			if (empty($_POST['from_order_status_id'])) {
				throw new Exception(t('error_must_select_from_order_status', 'You must select "from" order status'));
			}

			$num_orders = 0;

			database::query(
				"select id from ". DB_PREFIX ."orders
				where order_status_id = ". (int)$_POST['from_order_status_id'] .";"
			)->each(function($order) use (&$num_orders) {
				$order = new ent_order($order['id']);
				$order->data['order_status_id'] = (int)$_POST['to_order_status_id'];
				$order->save();
				$num_orders++;
			});

			notices::add('success', strtr(t('success_changed_order_status_for_n_orders', 'Changed order status for {n} orders'), [
				'{n}' => $num_orders
			]));

			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows, Total Number of Rows, Total Number of Pages

	$order_statuses = database::query(
		"select os.*, json_value(os.name, '$.". database::input(language::$selected['code']) ."') as name, o.num_orders
		from ". DB_PREFIX ."order_statuses os
		left join (
			select order_status_id, count(id) as num_orders
			from ". DB_PREFIX ."orders
			group by order_status_id
		) o on (o.order_status_id = os.id)
		order by field(state, 'created', 'on_hold', 'ready', 'delayed', 'processing', 'completed', 'dispatched', 'in_transit', 'delivered', 'returning', 'returned', 'cancelled', ''), os.priority, name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

	foreach ($order_statuses as $key => $order_status) {

		if (empty($order_status['icon'])) {
			$order_statuses[$key]['icon'] = 'icon-circle-o-thin';
		}

		if (empty($order_status['color'])) {
			$order_statuses[$key]['color'] = '#cccccc';
		}
	}

	// Pagination
	$num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));

	$states = [
		'created' => t('title_created', 'Created'),
		'on_hold' => t('title_on_hold', 'On Hold'),
		'ready' => t('title_ready', 'Ready'),
		'delayed' => t('title_delayed', 'Delayed'),
		'processing' => t('title_processing', 'Processing'),
		'completed' => t('title_completed', 'Completed'),
		'dispatched' => t('title_dispatched', 'Dispatched'),
		'in_transit' => t('title_in_transit', 'In Transit'),
		'delivered' => t('title_delivered', 'Delivered'),
		'returning' => t('title_returning', 'Returning'),
		'returned' => t('title_returned', 'Returned'),
		'cancelled' => t('title_cancelled', 'Cancelled'),
	];

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_order_statuses', 'Order Statuses') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_order_status'), t('title_create_new_order_status', 'Create New Order Status'), '', 'create') ?>
		</ul>
	</div>

	<?= f::form_begin('order_statuses_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_status_state', 'State') ?></th>
					<th><?= t('title_stock_action', 'Stock Action') ?></th>
					<th><?= t('title_hidden', 'Hidden') ?></th>
					<th><?= t('title_sales', 'Sales') ?></th>
					<th><?= t('title_archived', 'Archived') ?></th>
					<th><?= t('title_track', 'Track') ?></th>
					<th><?= t('title_notify', 'Notify') ?></th>
					<th><?= t('title_orders', 'Orders') ?></th>
					<th><?= t('title_priority', 'Priority') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($order_statuses as $order_status) { ?>
				<tr>
					<td><?= f::form_checkbox('order_statuses[]', $order_status['id']) ?></td>
					<td><?= $order_status['id'] ?></td>
					<td class="text-center"><?= f::draw_fonticon($order_status['icon'], 'style="color: '. $order_status['color'] .';"') ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_order_status', ['order_status_id' => $order_status['id']]) ?>"><?= $order_status['name'] ?></a></td>
					<td><?= strtr($order_status['state'], $states) ?></td>
					<td class="text-center"><?= strtr($order_status['stock_action'], ['none' => t('title_none', 'None'), 'reserve' => t('title_reserve', 'Reserve'), 'commit' => t('title_commit', 'Commit')]) ?></td>
					<td class="text-center"><?= !empty($order_status['hidden']) ? f::draw_fonticon('icon-check') : '-' ?></td>
					<td class="text-center"><?= !empty($order_status['is_sale']) ? f::draw_fonticon('icon-check') : '-' ?></td>
					<td class="text-center"><?= !empty($order_status['is_archived']) ? f::draw_fonticon('icon-check') : '-' ?></td>
					<td class="text-center"><?= !empty($order_status['is_trackable']) ? f::draw_fonticon('icon-check') : '-' ?></td>
					<td class="text-center"><?= !empty($order_status['notify']) ? f::draw_fonticon('icon-check') : '-' ?></td>
					<td class="text-center">
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/orders', ['order_status_id' => $order_status['id']]) ?>" title="<?= t('title_view', 'View') ?>">
							<?= f::draw_fonticon('icon-square-out') ?> <?= f::format_number($order_status['num_orders'], 0) ?>
						</a>
					</td>
					<td class="text-center"><?= (int)$order_status['priority'] ?></td>
					<td>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_order_status', ['order_status_id' => $order_status['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_order_statuses', 'Order Statuses') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?= f::form_end() ?>

	<div class="card-body">
		<?= f::form_begin('order_statuses_form', 'post') ?>

			<fieldset id="actions">
				<legend><?= t('text_change_status_for_orders', 'Change status for orders') ?></legend>

				<div class="grid">
					<div class="col-md-2">
						<label class="form-label"><?= t('title_from_order_status', 'From Order Status') ?></label>
						<?= f::form_select_order_status('from_order_status_id', true) ?>
					</div>

					<div class="col-md-2">
						<label class="form-label"><?= t('title_to_order_status', 'To Order Status') ?></label>
						<?= f::form_select_order_status('to_order_status_id', true) ?>
					</div>

					<div class="col-md-1">
						<br>
						<?= f::form_button('change', [1, t('title_change', 'Change')], 'submit') ?>
					</div>
				</div>
			</fieldset>

		<?= f::form_end() ?>
	</div>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>