<?php

	administrator::require_login();

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (empty($_GET['sort'])) {
		$_GET['sort'] = 'created';
	}

	document::$title[] = t('title_event_logs', 'Event Logs');

	breadcrumbs::add(t('title_customers', 'Customers'), document::ilink('customers/customers'));
	breadcrumbs::add(t('title_event_logs', 'Event Logs'));

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['events'])) {
				throw new Exception(t('error_must_select_events', 'You must select events'));
			}

			foreach ($_POST['events'] as $event_id) {
				database::query(
					"delete from ". DB_PREFIX ."event_logs
					where id = ". (int)$event_id ."
					limit 1;"
				);
			}

			notices::add('success', strtr(t('success_deleted_n_events', 'Deleted %n events'), ['%n' => count($_POST['events'])]));

			reload(303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows
	$events = [];

	if (!empty($_GET['query'])) {
		$sql_find = [
			"el.session_id like '%". database::input($_GET['query']) ."%'",
			"el.customer_id = '". database::input($_GET['query']) ."'",
			"ca.customer_email like '". database::input($_GET['query']) ."'",
			"el.type like '%". database::input($_GET['query']) ."%'",
			"el.description like '%". database::input($_GET['query']) ."%'",
			"el.url like '%". database::input($_GET['query']) ."%'",
			"el.ip_address like '%". database::input($_GET['query']) ."%'",
			"el.hostname like '%". database::input($_GET['query']) ."%'",
			"el.fingerprint like '%". database::input($_GET['query']) ."%'",
		];
	}

	$sql_sort = match($_GET['sort']) {
		'session_id' => "el.session_id",
		'customer_id' => "el.customer_id",
		'type' => "el.type",
		'description' => "el.description",
		'ip_address' => "el.ip_address",
		'expires' => "el.expires_at desc",
		'created' => "el.created_at desc, el.id desc",
		default => "el.created_at desc, el.id desc",
	};

	$events = database::query(
		"select el.*, c.email, c.firstname, c.lastname from ". DB_PREFIX ."event_logs el
		left join ". DB_PREFIX ."customers c on (el.customer_id = c.id)
		where el.id
		". ((!empty($_GET['type'])) ? "and `type` = '". database::input($_GET['type']) ."'" : "") ."
		". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
		". (!empty($_GET['from']) ? "and ca.created_at >= '". date('Y-m-d H:i:s', strtotime($_GET['from'])) ."'" : "") ."
		". (!empty($_GET['to']) ? "and ca.created_at <= '". date('Y-m-d H:i:s', strtotime($_GET['to'])) ."'" : "") ."
		order by $sql_sort;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

	$type_options = database::query(
		"select distinct type from ". DB_PREFIX ."event_logs
		order by type;"
	)->fetch_all(function($row) {
		return [$row['type'], $row['type']];
	});

	array_unshift($type_options, ['', '-- '. t('title_all', 'All') .' --']);

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_event_logs', 'Event Logs') ?>
		</div>
	</div>

	<?= f::form_begin('search_form', 'get') ?>
		<div class="card-filter">
			<div style="vertical-align: middle; width: 160px;"><?= f::form_select('type', $type_options, true, ['onchange' => '$(this).closest(\'form\').submit();']) ?></div>
			<div class="expandable"><?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]) ?></div>
			<div>
				<div class="input-group" style="max-width: 450px;">
					<?= f::form_input_datetime('from') ?>
					<span class="input-group-text"> - </span>
					<?= f::form_input_datetime('to') ?>
				</div>
			</div>
			<div><?= f::form_button('filter', t('title_search', 'Search'), 'submit') ?></div>
		</div>
	<?= f::form_end() ?>

	<?= f::form_begin('events_form', 'post') ?>

		<table class="table table-striped table-hover table-sortable data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th data-sort="session_id"><?= t('title_session_id', 'Session ID') ?></th>
					<th data-sort="customer_id"><?= t('title_customer', 'Customer') ?></th>
					<th data-sort="type"><?= t('title_type', 'Type') ?></th>
					<th data-sort="description" class="main"><?= t('title_description', 'Description') ?></th>
					<th data-sort="ip_address"><?= t('title_remote_host', 'Remote Host') ?></th>
					<th data-sort="created"><?= t('title_date', 'Date') ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($events as $event) { ?>
				<tr>
					<td><?= f::form_checkbox('events[]', $event['id']) ?></td>
					<td class="text-nowrap"><?= f::escape_html(f::string_ellipsis($event['session_id'], -6)) ?></td>
					<td>
						<?php if (!empty($event['customer_id'])) { ?>
							<a class="link" href="<?= document::href_link('', ['doc' => 'edit_customer', 'customer_id' => $event['customer_id']], true) ?>">
								<?= !empty($event['email']) ? f::escape_html($event['email']) : '#' . $event['customer_id'] ?>
							</a>
							<?php if (!empty($event['firstname']) || !empty($event['lastname'])) { ?>
								<br><small class="text-muted"><?= f::escape_html(trim($event['firstname'] . ' ' . $event['lastname'])) ?></small>
							<?php } ?>
						<?php } else { ?>
							<span class="text-muted"><?= t('text_guest', 'Guest') ?></span>
						<?php } ?>
					</td>
					<td><?= f::escape_html($event['type']) ?></td>
					<td>
						<?= f::escape_html($event['description']) ?>
						<?php if (!empty($event['url'])) { ?>
							<div>
								<small class="text-muted">
									<a href="<?= f::escape_html($event['url']) ?>" target="_blank" title="<?= f::escape_html($event['url']) ?>">
										<?= f::escape_html(f::string_ellipsis(parse_url($event['url'], PHP_URL_PATH) ?: $event['url'], 50)) ?>
									</a>
								</small>
							</div>
						<?php } ?>
						<?php if (!empty($event['data'])) { ?>
							<div>
								<small class="text-muted" title="<?= f::escape_html($event['data']) ?>">
									<?= f::escape_html(f::string_ellipsis($event['data'], 100)) ?>
								</small>
							</div>
						<?php } ?>
					</td>
					<td class="text-nowrap">
						<?= f::escape_html($event['ip_address']) ?>
						<?php if (!empty($event['hostname']) && $event['hostname'] != $event['ip_address']) { ?>
							<div>
								<small class="text-muted" title="<?= f::escape_html($event['hostname']) ?>">
									<?= f::escape_html(f::string_ellipsis($event['hostname'], 50)) ?>
								</small>
							</div>
						<?php } ?>
					</td>
					<td class="text-nowrap"><?= f::datetime_when($event['created_at']) ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_events', 'Events') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>
				<legend><?= t('text_with_selected', 'With selected') ?>:</legend>

				<ul class="list-inline">
					<li><?= f::form_button_predefined('delete') ?></li>
				</ul>
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
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
