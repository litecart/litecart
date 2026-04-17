<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (empty($_GET['sort'])) {
		$_GET['sort'] = 'created';
	}

	document::$title[] = t('title_event_logs', 'Event Logs');

	breadcrumbs::add(t('title_customers', 'Customers'), document::link(WS_DIR_ADMIN, ['app' => 'customers']));
	breadcrumbs::add(t('title_event_logs', 'Event Logs'));

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['events'])) {
				throw new Exception(t('error_must_select_events', 'You must select events'));
			}

			foreach ($_POST['events'] as $event_id) {
				database::query(
					"delete from ". DB_TABLE_PREFIX ."event_logs
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

	switch($_GET['sort']) {

		case 'session_id':
			$sql_sort = "el.session_id";
			break;

		case 'customer_id':
			$sql_sort = "el.customer_id";
			break;

		case 'type':
			$sql_sort = "el.type";
			break;

		case 'description':
			$sql_sort = "el.description";
			break;

		case 'ip_address':
			$sql_sort = "el.ip_address";
			break;

		case 'expires':
			$sql_sort = "el.expires_at desc";
			break;

		case 'created':
		default:
			$sql_sort = "el.created_at desc, el.id desc";
			break;
	}

	$events = database::query(
		"select el.*, c.email, c.firstname, c.lastname from ". DB_TABLE_PREFIX ."event_logs el
		left join ". DB_TABLE_PREFIX ."customers c on (el.customer_id = c.id)
		where el.id
		". ((!empty($_GET['type'])) ? "and `type` = '". database::input($_GET['type']) ."'" : "") ."
		". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
		". (!empty($_GET['from']) ? "and ca.date_created >= '". date('Y-m-d H:i:s', strtotime($_GET['from'])) ."'" : "") ."
		". (!empty($_GET['to']) ? "and ca.date_created <= '". date('Y-m-d H:i:s', strtotime($_GET['to'])) ."'" : "") ."
		order by $sql_sort;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

	$type_options = database::query(
		"select distinct type from ". DB_TABLE_PREFIX ."event_logs
		order by type;"
	)->fetch_all(function($row) {
		return [$row['type'], $row['type']];
	});

	array_unshift($type_options, ['', '-- '. t('title_all', 'All') .' --']);

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_event_logs', 'Event Logs'); ?>
		</div>
	</div>

	<?php echo f::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<div style="vertical-align: middle; width: 160px;"><?php echo functions::form_select('type', $type_options, true, 'onchange="'. functions::escape_html('this.closest("form").submit();') .'"'); ?></div>
			<div class="expandable"><?php echo f::form_input_search('query', true, 'placeholder="'. t('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"'); ?></div>
			<div>
				<div class="input-group" style="max-width: 450px;">
					<?php echo functions::form_input_datetime('from'); ?>
					<span class="input-group-text"> - </span>
					<?php echo functions::form_input_datetime('to'); ?>
				</div>
			</div>
			<div><?php echo f::form_button('filter', t('title_search', 'Search'), 'submit'); ?></div>
		</div>
	<?php echo f::form_end(); ?>

	<?php echo f::form_begin('events_form', 'post'); ?>

		<table class="table table-striped table-hover table-sortable data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th data-sort="session_id"><?php echo t('title_session_id', 'Session ID'); ?></th>
					<th data-sort="customer_id"><?php echo t('title_customer', 'Customer'); ?></th>
					<th data-sort="type"><?php echo t('title_type', 'Type'); ?></th>
					<th data-sort="description" class="main"><?php echo t('title_description', 'Description'); ?></th>
					<th data-sort="ip_address"><?php echo t('title_remote_host', 'Remote Host'); ?></th>
					<th data-sort="created"><?php echo t('title_date', 'Date'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($events as $event) { ?>
				<tr>
					<td><?php echo f::form_checkbox('events[]', $event['id']); ?></td>
					<td class="text-nowrap"><?php echo f::escape_html(f::string_ellipsis($event['session_id'], -6)); ?></td>
					<td>
						<?php if (!empty($event['customer_id'])) { ?>
							<a class="link" href="<?php echo document::href_link('', ['doc' => 'edit_customer', 'customer_id' => $event['customer_id']], true); ?>">
								<?php echo !empty($event['email']) ? f::escape_html($event['email']) : '#' . $event['customer_id']; ?>
							</a>
							<?php if (!empty($event['firstname']) || !empty($event['lastname'])) { ?>
								<br><small class="text-muted"><?php echo f::escape_html(trim($event['firstname'] . ' ' . $event['lastname'])); ?></small>
							<?php } ?>
						<?php } else { ?>
							<span class="text-muted"><?php echo t('text_guest', 'Guest'); ?></span>
						<?php } ?>
					</td>
					<td><?php echo f::escape_html($event['type']); ?></td>
					<td>
						<?php echo f::escape_html($event['description']); ?>
						<?php if (!empty($event['url'])) { ?>
							<div>
								<small class="text-muted">
									<a href="<?php echo f::escape_html($event['url']); ?>" target="_blank" title="<?php echo f::escape_html($event['url']); ?>">
										<?php echo f::escape_html(f::string_ellipsis(parse_url($event['url'], PHP_URL_PATH) ?: $event['url'], 50)); ?>
									</a>
								</small>
							</div>
						<?php } ?>
						<?php if (!empty($event['data'])) { ?>
							<div>
								<small class="text-muted" title="<?php echo f::escape_html($event['data']); ?>">
									<?php echo f::escape_html(f::string_ellipsis($event['data'], 100)); ?>
								</small>
							</div>
						<?php } ?>
					</td>
					<td class="text-nowrap">
						<?php echo f::escape_html($event['ip_address']); ?>
						<?php if (!empty($event['hostname']) && $event['hostname'] != $event['ip_address']) { ?>
							<div>
								<small class="text-muted" title="<?php echo f::escape_html($event['hostname']); ?>">
									<?php echo f::escape_html(f::string_ellipsis($event['hostname'], 50)); ?>
								</small>
							</div>
						<?php } ?>
					</td>
					<td class="text-nowrap"><?php echo f::datetime_when($event['created_at']); ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_events', 'Events'); ?>: <?php echo f::format_number($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>
				<legend><?php echo t('text_with_selected', 'With selected'); ?>:</legend>

				<ul class="list-inline">
					<li><?php echo f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?></li>
				</ul>
			</fieldset>
		</div>

	<?php echo f::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo f::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
