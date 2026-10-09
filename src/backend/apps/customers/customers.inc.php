<?php

	administrator::require_login();

	document::$title[] = t('title_customers', 'Customers');

	breadcrumbs::add(t('title_customers', 'Customers'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (empty($_GET['sort'])) {
		$_GET['sort'] = 'created_at';
	}

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['customers'])) {
				throw new Exception(t('error_must_select_customers', 'You must select customers'));
			}

			foreach ($_POST['customers'] as $customer_id) {
				$customer = new ent_customer($customer_id);
				$customer->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$customer->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['customers'])) {
				throw new Exception(t('error_must_select_customers', 'You must select customers'));
			}

			foreach ($_POST['customers'] as $customer_id) {
				$customer = new ent_customer($customer_id);
				$customer->delete();
			}

			notices::add('success', strtr(t('success_deleted_n_customers', 'Deleted {n} customers'), [
				'{n}' => count($_POST['customers'])
			]));

			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows

	if (!empty($_GET['query'])) {
		$sql_find = [
			"c.id = '". database::input($_GET['query']) ."'",
			"c.email like '%". database::input($_GET['query']) ."%'",
			"c.tax_id like '%". database::input($_GET['query']) ."%'",
			"c.company like '%". database::input($_GET['query']) ."%'",
			"concat(c.firstname, ' ', c.lastname) like '%". database::input($_GET['query']) ."%'",
		];
	}

	$sql_sort = match($_GET['sort']) {
		'id' => "c.id desc",
		'email' => "c.email",
		'name' => "c.firstname, c.lastname",
		'company' => "c.firstname, c.lastname",
		'group' => "c.group_id, c.email",
		default => "c.created_at desc, c.id desc",
	};

	// Table Rows, Total Number of Rows, Total Number of Pages
	$customers = database::query(
		"select c.*, cg.name as group_name from ". DB_PREFIX ."customers c
		left join ". DB_PREFIX ."customer_groups cg on (c.group_id = cg.id)
		where c.id
		". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
		". (!empty($_GET['group_id']) ? "and c.group_id = ". (int)$_GET['group_id'] : "") ."
		order by $sql_sort;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_customers', 'Customers') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink('customers/edit_customer'), t('title_create_new_customer', 'Create New Customer'), '', 'create') ?>
	</div>

	<?= f::form_begin('search_form', 'get') ?>

		<div class="card-filter">
			<div><?= f::form_select_customer_group('group_id', true, ['style' => 'min-width: 200px;']) ?></div>
			<div class="expandable"><?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]) ?></div>
			<?= f::form_button('filter', t('title_search', 'Search'), 'submit') ?>
		</div>

	<?= f::form_end() ?>

	<?= f::form_begin('customers_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center" style="width: 40px;"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th style="width: 40px;"></th>
					<th data-sort="id" style="width: 50px;"><?= t('title_id', 'ID') ?></th>
					<th data-sort="name"><?= t('title_person_name', 'Name') ?></th>
					<th data-sort="email"><?= t('title_email', 'Email') ?></th>
					<th data-sort="company"><?= t('title_company_name', 'Company Name') ?></th>
					<th class="main"><?= t('title_last_hostname', 'Last Hostname') ?></th>
					<th class="text-center" data-sort="group"><?= t('title_customer_group', 'Customer Group') ?></th>
					<th data-sort="created_at" class="text-end"><?= t('title_date_registered', 'Date Registered') ?></th>
					<th style="width: 50px;"></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($customers as $customer) { ?>
				<tr class="<?= empty($customer['status']) ? 'semi-transparent' : '' ?>">
					<td><?= f::form_checkbox('customers[]', $customer['id']) ?></td>
					<td><?= f::draw_fonticon($customer['status'] ? 'on' : 'off') ?></td>
					<td><?= $customer['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/customer', ['customer_id' => $customer['id']]) ?>">
						<?= f::draw_fonticon($customer['company'] ? 'icon-building' : 'icon-user', 'style="opacity: .5;"') ?>
						<?= f::escape_html($customer['company'] ?: $customer['firstname'] .' '. $customer['lastname']) ?>
					</a></td>
					<td><?= f::escape_html($customer['email']) ?></td>
					<td><?= f::escape_html($customer['company']) ?></td>
					<td><?= f::escape_html($customer['last_hostname']) ?></td>
					<td class="text-center"><?= f::escape_html($customer['group_name']) ?></td>
					<td class="text-end"><?= f::datetime_when($customer['created_at']) ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_customer', ['customer_id' => $customer['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_customers', 'Customers') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?= t('text_with_selected', 'With selected') ?>:
				</legend>

				<div class="flex">

					<div div class="btn-group">
						<?= f::form_button_predefined('enable') ?>
						<?= f::form_button_predefined('disable') ?>
					</div>

					<?= f::form_button_predefined('delete') ?>

				</div>
			</fieldset>
		</div>

	<?= f::form_end() ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>

<script>
	$('select[name="group_id"] option[value=""]').text('-- <?= t('title_all', 'All') ?> --');
	$('select[name="group_id"]').on('change', function() {
		$(this).closest('form').submit();
	});

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
