<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['webhooks'])) {
				throw new Exception(t('error_must_select_webhooks', 'You must select webhooks'));
			}

			foreach ($_POST['webhooks'] as $webhook_id) {
				$webhook = new ent_webhook($webhook_id);
				$webhook->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$webhook->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			header('Location: '. document::link());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$webhooks = database::prepare(
		"select * from ". DB_TABLE_PREFIX ."webhooks
		order by status desc, url asc;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

?>
<div class="card card-app">

	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_webhooks', 'Webhooks'); ?>
		</div>
	</div>

	<div class="card-filter">
		<div class="list-inline pull-end">
		</ul>
	</div>

	<div class="card-action">
			<?php echo f::form_button_link(document::link('', ['doc' => 'edit_webhook'], true), t('title_create_new_webhook', 'Create New Webhook'), '', 'add'); ?>
	</div>

	<?php echo f::form_begin('webhooks_form', 'post'); ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th><?php echo t('title_event', 'Event'); ?></th>
					<th class="main"><?php echo t('title_url', 'URL'); ?></th>
					<th><?php echo t('title_last_sent', 'Last Sent'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($webhooks as $webhook) { ?>
				<tr class="<?php echo empty($webhook['status']) ? 'semi-transparent' : null; ?>">
					<td><?php echo f::form_checkbox('webhooks[]', $webhook['id']); ?></td>
					<td><?php echo f::draw_fonticon('fa-circle', 'style="color: '. (!empty($webhook['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
					<td><?php echo $webhook['event']; ?></td>
					<td><a class="link" href="<?php echo document::href_link('', ['doc' => 'edit_webhook', 'webhook_id' => $webhook['id']], true); ?>"><?php echo f::escape_html($webhook['url']); ?></a></td>
					<td class="text-end"><?php echo (!empty($webhook['date_sent'])) ? language::strftime(language::$selected['format_datetime'], strtotime($webhook['date_sent'])) : '-'; ?></td>
					<td class="text-end">
						<a href="<?php echo document::href_link('', ['doc' => 'edit_webhook', 'webhook_id' => $webhook['id']], true); ?>" title="<?php echo t('title_edit', 'Edit'); ?>">
							<?php echo f::draw_fonticon('fa-pencil'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_webhooks', 'Webhooks'); ?>: <?php echo language::number_format($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<?php echo f::form_button('enable', t('title_enable', 'Enable'), 'submit', '', 'on'); ?>
			<?php echo f::form_button('disable', t('title_disable', 'Disable'), 'submit', '', 'off'); ?>
		</div>

	<?php echo f::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo f::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>
