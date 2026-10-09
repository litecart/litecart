<?php

	administrator::require_login();

	if (!empty($_GET['request_id'])) {

		try {

			$request = database::query(
				"select * from ". DB_PREFIX ."webhook_requests
				where id = ". (int)$_GET['request_id'] ."
				limit 1;"
			)->fetch(function($row) {
				$row['headers'] = $row['headers'] ? json_decode($row['headers'], true) : [];
				return $row;
			});

			if (!$request) {
				throw new Exception('Not Found', 404);
			}

			$result = $request;

		} catch (Exception $e) {
			http_response_code($e->getCode());
			$result = ['error' => $e->getMessage()];
			exit;
		}

		header('Content-Type: application/json; charset='. mb_http_output());
		echo f::format_json($result);
		exit;
	}

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	$requests = database::query(
		"select * from ". DB_PREFIX ."webhook_requests
		where true
		". ((!empty($_GET['query'])) ? "and url like '%". database::input($_GET['query']) ."%'" : "") ."
		". ((!empty($_GET['query'])) ? "and last_log like '%". database::input($_GET['query']) ."%'" : "") ."
		". ((!empty($_GET['date_from'])) ? "and created_at >= '". date('Y-m-d H:i:s', strtotime($_GET['date_from'])) ."'" : "") ."
		". ((!empty($_GET['date_to'])) ? "and created_at <= '". date('Y-m-d H:i:s', strtotime($_GET['date_to'])) ."'" : "") ."
		order by created_at desc;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

	f::draw_lightbox();

	function ellipsis($string, $length = 70) {
		if (mb_strlen($string) > $length) {
			return mb_substr($string, 0, $length - 3) . '...';
		}
		return $string;
	}

?>
<div class="container">
	<section id="box-request-history" class="card card-app">

		<div class="card-header">
			<div class="card-title">
				<?= t('title_webhook_requests', 'Webhook Requests') ?>
			</div>
		</div>

		<div class="card-action">
			<a class="btn btn-default" href="<?= document::href_ilink(__APP__.'/edit_request') ?>">
				<?= f::draw_fonticon('add') ?> <?= t('title_create_new_request', 'Create New Request') ?>
			</a>
		</div>

		<?= f::form_begin('filter_form', 'get') ?>

			<div class="card-filter" style="gap: 1em;">
				<ul class="list-inline">

					<li class="expandable">
						<?= f::form_input_search('query', true, ['placeholder' => f::escape_html(t('title_search', 'Search'))]) ?>
					</li>

					<li>
						<div class="input-group" style="width: 480px;">
							<?= f::form_input_datetime('date_from') ?>
							<span class="input-group-text"> - </span>
							<?= f::form_input_datetime('date_to') ?>
						</div>
					</li>

					<li>
							<?= f::form_button('filter', t('title_filter_now', 'Filter')) ?>
					</li>

				</ul>
			</div>

		<?= f::form_end() ?>

		<table class="table table-striped data-table">

			<thead>
				<tr>
					<th><?= t('title_status', 'Status') ?></th>
					<th class="main"><?= t('title_request', 'Request') ?></th>
					<th><?= t('title_last_attempt', 'Last Attempt') ?></th>
					<th><?= t('title_delivered', 'Delivered') ?></th>
					<th><?= t('title_scheduled', 'Scheduled') ?></th>
					<th><?= t('title_created', 'Created') ?></th>
					<th></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($requests as $request) { ?>
				<tr data-id="<?= (int)$request['id'] ?>">
					<td><?= f::escape_html($request['status']) ?></td>
					<td><?= f::escape_html(ellipsis($request['method'] .' '. $request['url'])) ?></td>
					<td><?= $request['last_attempt'] ? f::datetime_format('datetime', $request['last_attempt']) : '-' ?></td>
					<td><?= $request['delivered_at'] ? f::datetime_format('datetime', $request['delivered_at']) : '-' ?></td>
					<td><?= $request['scheduled_at'] ? f::datetime_format('datetime', $request['scheduled_at']) : '-' ?></td>
					<td><?= f::datetime_format('datetime', $request['created_at']) ?></td>
					<td class="text-end">
						<button name="view" type="button" class="btn btn-default btn-sm">
							<?= f::draw_fonticon('icon-search') ?> <?= t('title_details', 'Details') ?>
						</button>
					</td>
					<td class="text-end">
						<?php if (!$request['delivered_at']) { ?>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_request', ['request_id' => $request['id']]) ?>" title="<?= f::escape_html(t('title_edit', 'Edit')) ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
						<?php } ?>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_requests', 'Requests') ?>: <?= f::number_format($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<?php if ($num_pages > 1) { ?>
		<div class="card-footer">
			<?= f::draw_pagination($num_pages) ?>
		</div>
		<?php } ?>

	</div>
</div>

<script>

	$('table button[name="view"]').click(function(e) {
		e.preventDefault();

		var id = $(this).closest('tr').data('id');
		var url = '<?= document::link(null, ['request_id' => '__request__id']) ?>'.replace(/__request__id/, id);

		$.getJSON(url, function(json) {

			$output = $([
				'<div>',
					'<h2><?= t('title_last_log', 'Last Log') ?></h2>',
					'<pre>'+ json.last_log +'</pre>',
				'</div>',
			].join('\n'));

			$.featherlight($output, {
				type: 'html',
				width: 800,
			});
		});
	});

	$('input[name="date_from"], input[name="date_to"]').on('change', function(e) {
		$(this).closest('form').submit();
	});

</script>
