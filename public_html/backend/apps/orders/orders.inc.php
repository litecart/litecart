<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (!isset($_GET['order_status_id'])) {
		$_GET['order_status_id'] = '';
	}

	if (!empty($_GET['date_from'])) {
		$_GET['date_from'] = date('Y-m-d', strtotime($_GET['date_from']));
	}

	if (!empty($_GET['date_to'])) {
		$_GET['date_to'] = date('Y-m-d', strtotime($_GET['date_to']));
	}

	if (empty($_GET['sort'])) {
		$_GET['sort'] = 'created_at';
	}

	document::$title[] = language::translate('title_orders', 'Orders');

	breadcrumbs::add(language::translate('title_orders', 'Orders'), document::ilink());

	if (isset($_POST['star']) || isset($_POST['unstar'])) {
		database::query(
			"update ". DB_TABLE_PREFIX ."orders
			set starred = ". (isset($_POST['star']) ? 1 : 0) ."
			where id = ". (int)$_POST['order_id'] ."
			limit 1;"
		);
		exit;
	}

	if (!empty($_POST['action'])) {

		try {

			if (empty($_POST['orders'])) {
				throw new Exception(language::translate('error_must_select_orders', 'You must select orders to perform the operation'));
			}

			sort($_POST['orders']);
			$_POST['orders'] = array_unique($_POST['orders']);
			$_POST['orders'] = array_reverse($_POST['orders']);

			switch ($_POST['action']) {

					// Set Order Status
				case 'set_order_status':

					if (empty($_POST['order_status_id'])) {
						throw new Exception(language::translate('error_must_select_order_status', 'You must select an order status'));
					}

					foreach ($_POST['orders'] as $order_id) {
						$order = new ent_order($order_id);
						$order->data['order_status_id'] = $_POST['order_status_id'];
						$order->save();
					}

					break;

				case 'book_shipping':

					foreach ($_POST['orders'] as $order_id) {

						try {

							$order = new ent_order($order_id);

							if (!$module_id = preg_replace('#^(.*)?:.*$#', '$1', $order->data['shipping_option']['id'])) {
								throw new Exception('Unknown shipping module for order '. $order->data['id']);
							}

							if (!class_exists($module_id)) {
								throw new Exception('Could not instantiate shipping module '. $module_id);
							}

							$module = new $module_id;

							if (!method_exists($module, 'book')) {
								throw new Exception('Method book() not found in shipping module '. $module_id);
							}

							$result = call_user_func([$module, 'book'], $order);

							if (!empty($result['error'])) {
								throw new Exception($result['error']);
							}

							notices::add('success', language::translate('success_changes_saved', 'Changes saved'));

						} catch (Exception $e) {
							notices::add('errors', $e->getMessage());
						}
					}

					break;

				case 'cancel_payment':

					foreach ($_POST['orders'] as $order_id) {

						try {

							$order = new ent_order($order_id);

							if (!$module_id = preg_replace('#^(.*)?:.*$#', '$1', $order->data['payment_option']['id'])) {
								throw new Exception('Unknown payment module for order '. $order->data['id']);
							}

							if (!class_exists($module_id)) {
								throw new Exception('Could not instantiate payment module '. $module_id);
							}

							$module = new $module_id;

							if (!method_exists($module, 'cancel')) {
								throw new Exception('Method cancel() not found in payment module '. $module_id);
							}

							$result = call_user_func([$module, 'cancel'], $order);

							if (!empty($result['error'])) {
								throw new Exception($result['error']);
							}

							notices::add('success', language::translate('success_changes_saved', 'Changes saved'));

						} catch (Exception $e) {
							notices::add('errors', $e->getMessage());
						}
					}

					break;

				// Perform Action from Order Module
				default:

					list($module_id, $action_id) = explode(':', $_POST['order_action']);

					$actions = (new mod_order())->actions();

					if (!method_exists($order_action->modules[$module_id], $actions[$module_id]['actions'][$action_id]['function'])) {
						throw new Exception(language::translate('error_method_doesnt_exist', 'The method doesn\'t exist'));
					}

					sort($_POST['orders']);

					if ($result = call_user_func([$order_action->modules[$module_id], $actions[$module_id]['actions'][$action_id]['function']], $_POST['orders'])) {
						echo $result;
						return;
					}

					break;
			}

			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows
	$orders = [];

	if (!empty($_GET['query'])) {
		$sql_where_query = [
			"o.id = '". database::input($_GET['query']) ."'",
			"o.no like '%". database::input($_GET['query']) ."%'",
			"o.reference like '%". database::input($_GET['query']) ."%'",
			"o.customer_email like '%". database::input($_GET['query']) ."%'",
			"o.customer_tax_id like '%". database::input($_GET['query']) ."%'",
			"o.shipping_tax_id like '%". database::input($_GET['query']) ."%'",
			"concat(o.customer_company, '\\n', o.customer_firstname, ' ', o.customer_lastname, '\\n', o.customer_address1, '\\n', o.customer_address2, '\\n', o.customer_postcode, '\\n', o.customer_city) like '%". database::input($_GET['query']) ."%'",
			"concat(o.shipping_company, '\\n', o.shipping_firstname, ' ', o.shipping_lastname, '\\n', o.shipping_address1, '\\n', o.shipping_address2, '\\n', o.shipping_postcode, '\\n', o.shipping_city) like '%". database::input($_GET['query']) ."%'",
			"o.payment_option_id like '%". database::input($_GET['query']) ."%'",
			"o.payment_option_name like '%". database::input($_GET['query']) ."%'",
			"o.payment_transaction_id like '". database::input($_GET['query']) ."'",
			"o.shipping_option_id like '%". database::input($_GET['query']) ."%'",
			"o.shipping_option_name like '%". database::input($_GET['query']) ."%'",
			"o.shipping_tracking_id like '". database::input($_GET['query']) ."'",
			"o.id in (
				select order_id from ". DB_TABLE_PREFIX ."orders_items
				where name like '%". database::input($_GET['query']) ."%'
				or sku like '%". database::input($_GET['query']) ."%'
			)",
		];
	}

	switch($_GET['sort']) {

		case 'id':
			$sql_sort = "o.starred desc, o.id desc";
			break;

		case 'country':
			$sql_sort = "o.starred desc, o.customer_country_code";
			break;

		case 'customer':
			$sql_sort = "o.starred desc, if(o.customer_company, o.customer_company, concat(o.customer_firstname, ' ', o.customer_lastname)) asc";
			break;

		case 'order_status':
			$sql_sort = "o.starred desc, field(os.state, 'created', 'on_hold', 'ready', 'delayed', 'processing', 'dispatched', 'in_transit', 'completed', 'delivered', 'returning', 'returned', 'cancelled'), name";
			break;

		case 'payment_method':
			$sql_sort = "o.starred desc, o.payment_option_name asc";
			break;

		default:
			$sql_sort = "if(o.starred, 1, 0) desc, o.created_at desc, o.id desc";
			break;
	}

	switch ($_GET['order_status_id']) {

		case '':
			$sql_where_order_status = "and (os.is_archived is null or os.is_archived = 0 or unread = 1)";
			break;

		case 'archived':
			$sql_where_order_status = "and (os.is_archived = 1)";
			break;

		case 'all':
			break;

		default:
			$sql_where_order_status = "and o.order_status_id = ". (!empty($_GET['order_status_id']) ? (int)$_GET['order_status_id'] : '');
			break;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$orders = database::query(
		"select o.*, os.color as order_status_color, os.icon as order_status_icon,
			json_value(os.name, '$.". database::input(language::$selected['code']) ."') as order_status_name,
			if (o.notes, 1, 0) as has_notes
		from ". DB_TABLE_PREFIX ."orders o
		left join ". DB_TABLE_PREFIX ."order_statuses os on (os.id = o.order_status_id)
		where o.id
		". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
		". fallback($sql_where_order_status) ."
		". (!empty($_GET['date_from']) ? "and o.created_at >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'" : '') ."
		". (!empty($_GET['date_to']) ? "and o.created_at <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'" : '') ."
		order by $sql_sort;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

	foreach ($orders as $i => $order) {

		// Order Status Icon and Color
		if (empty($order['order_status_id'])) {
			$order['order_status_icon'] = 'icon-minus';
			$order['order_status_color'] = '#ccc';
		}

		if (empty($order['order_status_icon'])) {
			$order['order_status_icon'] = 'icon-circle-o-thin';
		}

		if (empty($order['order_status_color'])) {
			$order['order_status_color'] = '#ccc';
		}

		// CSS Classes
		$order['css_classes'] = [];

		if (empty($order['order_status_id'])) {
			$order['css_classes'][]= 'semi-transparent';
		}

		if (!empty($order['unread'])) {
			$order['css_classes'][]= 'bold';
		}

		// Tags
		$order['tags'] = [];

		if ($order['customer_country_code'] != settings::get('store_country_code')) {
			$order['tags'][] = $order['customer_country_code'];
		}

		if (!empty($order['shipping_country_code']) && $order['shipping_country_code'] != $order['customer_country_code']) {
			$order['tags'][] = $order['shipping_country_code'];
		}

		// Order Items
		$order['items'] = database::query(
			"select oi.*, si.quantity as stock_quantity
			from ". DB_TABLE_PREFIX ."orders_items oi
			left join ". DB_TABLE_PREFIX ."products_stock_options pso on (pso.id = oi.stock_option_id)
			left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
			where oi.order_id = ". (int)$order['id'] .";"
		)->fetch_all(function($item){
			if ($item['stock_quantity'] !== null) {
				$item['sufficient_stock'] = null;
			} else if ($item['quantity'] <= $item['stock_quantity']) {
				$item['sufficient_stock'] = true;
			} else {
				$item['sufficient_stock'] = false;
			}
		});

		if (in_array(false, array_column($order['items'], 'sufficient_stock'), true)) {
			$order['sufficient_stock'] = false;
			$order['sufficient_stock_icon'] = functions::draw_fonticon('icon-check', 'style="color: #88cc44;"');

		} else if (array_unique(array_column($order['items'], 'sufficient_stock'), true) == [true]) {
			$order['sufficient_stock'] = true;
			$order['sufficient_stock_icon'] = functions::draw_fonticon('icon-times', 'style="color: #ff6644;"');
		} else {
			$order['sufficient_stock'] = null;
			$order['sufficient_stock_icon'] = '';
		}

		$orders[$i] = $order;
	}

	// Order Statuses
	$order_status_options = [
		[
			'label' => language::translate('title_collections', 'Collections'),
			'options' => [
				'' => language::translate('title_current', 'Current Orders'),
				'archived' => language::translate('title_archived_orders', 'Archived Orders'),
				'all' => language::translate('title_all_orders', 'All Orders'),
			],
		],
		[
			'label' => language::translate('title_order_statuses', 'Order Statuses'),
			'options' => [],
		],
	];

	database::query(
		"select os.*, json_value(os.name, '$.". database::input(language::$selected['code']) ."') as name, o.num_orders
		from ". DB_TABLE_PREFIX ."order_statuses os
		left join (
			select order_status_id, count(id) as num_orders
			from ". DB_TABLE_PREFIX ."orders
			group by order_status_id
		) o on (o.order_status_id = os.id)
		order by field(state, 'created', 'on_hold', 'ready', 'delayed', 'processing', 'dispatched', 'in_transit', 'delivered', 'returning', 'returned', 'cancelled', ''), name asc;"
	)->each(function($order_status) use (&$order_status_options) {
		$order_status_options[1]['options'][$order_status['id']] = $order_status['name'] .' ('. language::number_format($order_status['num_orders']) .')';
	});

	// Actions
	$actions = [];

	$mod_order = new mod_order();
	if ($modules = $mod_order->actions()) {
		foreach ($modules as $module) {
			$actions[] = $module;
		}
	}

?>
<style>
table tr.bold {
	font-weight: bold;
}

table .icon-star:hover,
table .icon-star-o:hover {
	transform: scale(1.5);
}

table .tag {
	font-family: monospace;
	font-size: 0.8em;
	opacity: 0.75;
	border: 1px solid var(--default-border-color);
	padding: .25em .5em;
	border-radius: var(--border-radius);
}

#order-actions li {
	vertical-align: middle;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_orders', 'Orders'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_order', ['redirect_url' => $_SERVER['REQUEST_URI']]), language::translate('title_create_new_order', 'Create New Order'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<?php echo functions::form_select_optgroup('order_status_id', $order_status_options, true, 'style="width: auto;"'); ?>
			<div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></div>
			<div class="input-group" style="max-width: 380px;">
				<?php echo functions::form_input_date('date_from', true); ?>
				<span class="input-group-text"> - </span>
				<?php echo functions::form_input_date('date_to', true); ?>
			</div>
			<?php echo functions::form_button('filter', language::translate('title_search', 'Search'), 'submit'); ?>
		</div>
	<?php echo functions::form_end(); ?>

	<?php echo functions::form_begin('orders_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th data-sort="id" class="text-end"><?php echo language::translate('title_order_no', 'Order No'); ?></th>
					<th></th>
					<th data-sort="customer" class="main"><?php echo language::translate('title_customer', 'Customer'); ?></th>
					<th><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
					<th data-sort="payment_method"><?php echo language::translate('title_payment_method', 'Payment Method'); ?></th>
					<th class="text-center"><?php echo language::translate('title_amount', 'Amount'); ?></th>
					<th data-sort="order_status" class="text-center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
					<th class="text-end" data-sort="created_at"><?php echo language::translate('title_created_at', 'Created At'); ?></th>
					<th></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($orders as $order) { ?>
				<tr class="<?php echo implode(' ', $order['css_classes']); ?>" data-id="<?php echo $order['id']; ?>">
					<td><?php echo functions::form_checkbox('orders[]', $order['id'], true); ?></td>
					<td><?php echo functions::draw_fonticon($order['order_status_icon'], 'style="color: '. $order['order_status_color'] .';"'); ?></td>
					<td class="text-center"><?php echo $order['no']; ?></td>
					<td><?php echo !empty($order['starred']) ? functions::draw_fonticon('icon-star', 'style="color: #f2b01e;"') : functions::draw_fonticon('icon-star-o', 'style="color: #ccc;"'); ?></td>
					<td>
						<a class="link" href="<?php echo document::href_ilink(__APP__.'/order', ['order_id' => $order['id'], 'redirect_url' => $_SERVER['REQUEST_URI']]); ?>">
							<?php echo functions::draw_fonticon($order['customer_company'] ? 'icon-building' : 'icon-user', 'style="opacity: .5;"'); ?>
							<?php echo $order['customer_company'] ?: $order['customer_firstname'] .' '. $order['customer_lastname']; ?><?php if (!$order['customer_id']) echo ' <em>('. language::translate('title_guest', 'Guest') .')</em>'; ?>
						</a>

						<?php foreach ($order['tags'] as $tag) echo '<code class="tag">'. functions::escape_html($tag) .'</code>'; ?>

						<?php if ($order['has_notes']) { ?>
						<?php echo functions::draw_fonticon('icon-sticky-note', 'title="'. language::translate('title_notes', 'Notes') .'" style="color: #f2b01e; margin-left: .5em;"'); ?>
						<?php } ?>
					</td>
					<td class="text-center"><?php $order['sufficient_stock_icon'] ?: '-'; ?></td>
					<td><?php echo $order['payment_option_name']; ?></td>
					<td class="text-end"><?php echo currency::format($order['total'], false, $order['currency_code'], $order['currency_value']); ?></td>
					<td class="text-center"><?php echo $order['order_status_id'] ? $order['order_status_name'] : language::translate('title_uncompleted', 'Uncompleted'); ?></td>
					<td class="text-end"><?php echo functions::datetime_when($order['created_at']); ?></td>
					<td>
						<div class="dropdown dropdown-end">
							<div class="btn btn-default btn-sm dropdown-toggle"  data-toggle="dropdown">
								<?php echo functions::draw_fonticon('icon-print'); ?>
							</div>
							<nav class="dropdown-menu">
								<a class="dropdown-item" href="<?php echo  document::href_ilink('f:printable_packing_slip', ['order_id' => $order['id'], 'public_key' => $order['public_key']]); ?>" target="_blank">
									<?php echo functions::escape_html(language::translate('title_packing_slip', 'Packing Slip')); ?>
								</a>
								<a class="dropdown-item" href="<?php echo document::href_ilink('f:printable_order_copy', ['order_id' => $order['id'], 'public_key' => $order['public_key']]); ?>" target="_blank" title="">
									<?php echo functions::escape_html(language::translate('title_order_copy', 'Order Copy')); ?>
								</a>
							</nav>
						</div>
					</td>
					<td>
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_order', ['order_id' => $order['id'], 'redirect_url' => $_SERVER['REQUEST_URI']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="12"><?php echo language::translate('title_orders', 'Orders'); ?>: <?php echo language::number_format($num_rows); ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<ul id="actions" class="list-inline">

				<li>
					<fieldset>
						<legend><?php echo language::translate('title_set_order_status', 'Set Order Status'); ?></legend>
						<div class="form-group">
							<div class="input-group">
								<?php echo functions::form_select_order_status('order_status_id', true); ?>
								<button class="btn btn-default" name="action" value="set_order_status" type="submit" formtarget="_self">
									<?php echo language::translate('title_set', 'Set'); ?>
								</button>
							</div>
						</div>
					</fieldset>
				</li>

				<li>
					<fieldset>
						<legend><?php echo language::translate('title_shipping', 'Shipping'); ?></legend>

						<div>
							<?php echo functions::form_button('action', ['book_shipping', language::translate('title_book_shipping', 'Book Shipping')]); ?>
						</div>

					</fieldset>
				</li>

				<li>
					<fieldset>
						<legend><?php echo language::translate('title_payment', 'Payment'); ?></legend>

						<div>
							<?php echo functions::form_button('action', ['cancel_payment', language::translate('title_cancel_payment', 'Cancel Payment')]); ?>
						</div>
					</fieldset>
				</li>

				<?php foreach ($actions as $module) { ?>
				<li>
					<fieldset title="<?php echo functions::escape_html($module['description']); ?>">
						<legend><?php echo $module['name']; ?></legend>
						<div class="btn-group">
							<?php foreach ($module['actions'] as $action) echo functions::form_button('action', [$module['id'].':'.$action['id'], $action['title']], 'submit', 'formtarget="'. functions::escape_attr($action['target']) .'" title="'. functions::escape_attr($action['description']) .'"'); ?>
						</div>
					</fieldset>
				</li>
				<?php } ?>

			</ul>
		</div>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo functions::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

<script>
	$('input[name="query"]').keypress(function(e) {
		if (e.which == 13) {
			e.preventDefault();
			$(this).closest('form').submit();
		}
	});

	$('form[name="search_form"] select').on('change', function() {
		$(this).closest('form').submit();
	});

	$('.data-table :checkbox').on('change', function() {
		$('#actions fieldset').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');

	$('table').on('click', '.icon-star-o', function(e) {
		e.stopPropagation();
		let $star = $(this);
		$.post('', 'star&order_id='+$star.closest('tr').data('id'), function(data) {
			$star.replaceWith('<?php echo functions::draw_fonticon('icon-star', 'style="color: #f2b01e;"'); ?>');
		});
		return false;
	});

	$('table').on('click', '.icon-star', function(e) {
		e.stopPropagation();
		let $star = $(this);
		$.post('', 'unstar&order_id='+$star.closest('tr').data('id'), function(data) {
			$star.replaceWith('<?php echo functions::draw_fonticon('icon-star-o', 'style="color: #ccc;"'); ?>');
		});
		return false;
	});

	$('#actions button').on('click', function(e) {
		if (!confirm('<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>')) {
			e.preventDefault();
			return false;
		}
	});
</script>