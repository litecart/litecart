<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}
	if (empty($_GET['sort'])) $_GET['sort'] = 'date_created';

	document::$title[] = language::translate('title_shopping_carts', 'Shopping Carts');

	breadcrumbs::add(language::translate('title_shopping_carts', 'Shopping Carts'), document::ilink());

	if (!empty($_POST['delete'])) {
		try {

			if (!empty($_POST['shopping_carts'])) {
				foreach ($_POST['shopping_carts'] as $shopping_cart_id) {
					$shopping_cart = new ent_shopping_cart($shopping_cart_id);
					$shopping_cart->delete();
				}
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::link());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_GET['query'])) {
		$sql_where_query = [
			"sc.id = '". database::input($_GET['query']) ."'",
			"sc.uid = '". database::input($_GET['query']) ."'",
			"sc.billing_email like '%". database::input($_GET['query']) ."%'",
			"sc.billing_tax_id like '%". database::input($_GET['query']) ."%'",
			"sc.shipping_tax_id like '%". database::input($_GET['query']) ."%'",
			"concat(sc.billing_company, '\\n', sc.billing_firstname, ' ', sc.billing_lastname, '\\n', sc.billing_address1, '\\n', sc.billing_address2, '\\n', sc.billing_postcode, '\\n', sc.billing_city) like '%". database::input($_GET['query']) ."%'",
			"concat(sc.shipping_company, '\\n', sc.shipping_firstname, ' ', sc.shipping_lastname, '\\n', sc.shipping_address1, '\\n', sc.shipping_address2, '\\n', sc.shipping_postcode, '\\n', sc.shipping_city) like '%". database::input($_GET['query']) ."%'",
			"sc.id in (
				select cart_id from ". DB_TABLE_PREFIX ."shopping_carts_items
				where name like '%". database::input($_GET['query']) ."%'
				or sku like '%". database::input($_GET['query']) ."%'
			)",
		];
	}

	switch($_GET['sort']) {
		case 'id':
			$sql_sort = "sc.id desc";
			break;
		case 'country':
			$sql_sort = "sc.billing_country_code";
			break;
		default:
			$sql_sort = "sc.date_created desc, sc.id desc";
			break;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$shopping_carts = database::query(
		"select sc.*, sci.num_items from ". DB_TABLE_PREFIX ."shopping_carts sc
		left join (
			select cart_id, count(id) as num_items
			from ". DB_TABLE_PREFIX ."shopping_carts_items
			group by cart_id
		) sci on (sc.id = sci.cart_id)
		where sc.id
		". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
		order by $sql_sort;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_shopping_carts', 'Shopping Carts'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_shopping_cart', ['redirect_url' => $_SERVER['REQUEST_URI']]), language::translate('title_create_new_shopping_cart', 'Create New Shopping Cart'), '', 'add'); ?>
	</div>

	<?php echo functions::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></div>
			<?php echo functions::form_button('filter', language::translate('title_search', 'Search'), 'submit'); ?>
		</div>
	<?php echo functions::form_end(); ?>

	<?php echo functions::form_begin('shopping_carts_form', 'post'); ?>

		<table class="table table-striped table-hover table-sortable data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-check-square-o', 'data-toggle="checkbox-toggle"'); ?></th>
					<th data-sort="id"><?php echo language::translate('title_id', 'ID'); ?></th>
					<th data-sort="customer" class="main"><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
					<th data-sort="country"><?php echo language::translate('title_country', 'Country'); ?></th>
					<th data-sort="items"><?php echo language::translate('title_items', 'Items'); ?></th>
					<th class="text-center"><?php echo language::translate('title_subtotal', 'Subtotal'); ?></th>
					<th data-sort="date_created"><?php echo language::translate('title_date', 'Date'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($shopping_carts as $shopping_cart) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('shopping_carts['.$shopping_cart['id'].']', $shopping_cart['id'], true); ?></td>
					<td><?php echo $shopping_cart['id']; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_shopping_cart', ['cart_id' => $shopping_cart['id'], 'redirect_url' => $_SERVER['REQUEST_URI']]); ?>"><?php echo ($shopping_cart['billing_company']) ? $shopping_cart['billing_company'] : $shopping_cart['billing_firstname'] .' '. $shopping_cart['billing_lastname']; ?><?php if (empty($shopping_cart['customer_id'])) echo ' <em>('. language::translate('title_guest', 'Guest') .')</em>'; ?></a> <span style="opacity: 0.5;"><?php echo $shopping_cart['billing_tax_id']; ?></span></td>
					<td><?php if (!empty($shopping_cart['billing_country_code'])) echo reference::country($shopping_cart['billing_country_code'])->name; ?></td>
					<td class="text-end"><?php echo $shopping_cart['num_items']; ?></td>
					<td class="text-end"><?php echo currency::format($shopping_cart['subtotal'], false, $shopping_cart['currency_code']); ?></td>
					<td class="text-end"><?php echo language::strftime('datetime', $shopping_cart['date_created']); ?></td>
					<td><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_shopping_cart', ['cart_id' => $shopping_cart['id'], 'redirect_url' => $_SERVER['REQUEST_URI']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('icon-pencil'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="8"><?php echo language::translate('title_shopping_carts', 'Shopping Carts'); ?>: <?php echo language::number_format($num_rows); ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?php echo language::translate('text_with_selected', 'With selected'); ?></legend>

				<ul class="list-inline">
					<li><?php echo functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?></li>
				</ul>
			</fieldset>
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
			e.preventDefault()
			$(this).closest('form').submit()
		}
	})

	$('.data-table :checkbox').change(function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length)
	}).first().trigger('change')
</script>