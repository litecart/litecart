<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (!empty($_POST['enable']) || !empty($_POST['disable'])) {

		try {

			if (!empty($_POST['reviews'])) {

				foreach ($_POST['reviews'] as $key => $value) {
					$_POST['reviews'][$key] = database::input($value);
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."reviews
					set status = '". (!empty($_POST['enable']) ? 1 : 0) ."'
					where id in ('". implode("', '", $_POST['reviews']) ."');"
				);
			}

			reload(303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_GET['query'])) {
		$sql_find = [
			"pr.id = '". database::input($_GET['query']) ."'",
			"pr.customer_email like '%". database::input($_GET['query']) ."%'",
			"pr.customer_name like '%". database::input($_GET['query']) ."%'",
			"pr.title like '%". database::input($_GET['query']) ."%'",
			"pr.description like '%". database::input($_GET['query']) ."%'",
			"pi.name like '%". database::input($_GET['query']) ."%'",
		];
	}

	$reviews = database::query(
		"select pr.*, pi.name as product_name
		from ". DB_TABLE_PREFIX ."reviews pr
		left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = pr.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
		". (!empty($sql_find) ? "where (". implode(" or ", $sql_find) .")" : "") ."
		order by date_updated desc;"
	)->fetch_page(function(&$review){
		$review['title'] = json_decode($review['title'], true) ?: [];
		$review['description'] = json_decode($review['description'], true) ?: [];
		$review['attachments'] = json_decode($review['attachments'], true) ?: [];
	}, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);


?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_reviews', 'Reviews'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo f::form_button_link(document::ilink(__APP__.'/reviews_csv'), t('title_import_export_csv', 'Import/Export CSV')); ?>
		<?php echo f::form_button_link(document::ilink(__APP__.'/edit_review'), t('title_create_new_review', 'Create New Review'), '', 'add'); ?>
	</div>

	<div class="card-filter">
		<?php echo f::form_begin('search_form', 'get'); ?>
			<ul class="list-inline">
				<li class="expandable"><?php echo f::form_input_search('query', true, 'placeholder="'. t('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"'); ?></li>
				<li><?php echo f::form_button('filter', t('title_search', 'Search'), 'submit'); ?></li>
			</ul>
		<?php echo f::form_end(); ?>
	</div>

	<?php echo f::form_begin('reviews_form', 'post'); ?>

		<table class="table table-striped data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th><?php echo t('title_id', 'ID'); ?></th>
					<th><?php echo t('title_product', 'Product'); ?></th>
					<th><?php echo t('title_customer', 'Customer'); ?></th>
					<th class="main"><?php echo t('title_title', 'Title'); ?></th>
					<th><?php echo t('title_rating', 'Rating'); ?></th>
					<th><?php echo t('title_created', 'Created'); ?> / <?php echo t('title_updated', 'Updated'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($reviews as $review) { ?>
				<tr class="<?php echo $review['status'] ? false : ' semi-transparent'; ?>">
					<td><?php echo f::form_checkbox('reviews[]', $review['id']); ?></td>
					<td><?php echo f::draw_fonticon('fa-circle', 'style="color: '. (!empty($review['status']) ? '#99cc66' : '#ff6666') .';"'); ?></td>
					<td><?php echo $review['id']; ?></td>
					<td>
						<a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_review', ['review_id' => $review['id']]); ?>">
							<?php echo $review['product_name']; ?>
						</a>
					</td>
					<td><?php echo !empty($review['customer_name']) ? $review['customer_name'] : '<em>'. t('title_guest', 'Guest') .'</em>'; ?></td>
					<td><?php echo $review['title']; ?></td>
					<td class="text-center"><?php echo $review['rating']; ?></td>
					<td><?php echo $review['date_updated'] > $review['date_created'] ? $review['date_updated'] : $review['date_created']; ?></td>
					<td>
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_review', ['review_id' => $review['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>">
							<?php echo f::draw_fonticon('fa-pencil'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_reviews', 'Reviews'); ?>: <?php echo f::format_number($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>
				<legend><?php echo t('text_with_selected', 'With selected'); ?></legend>

				<div class="btn-group">
					<?php echo f::form_button('enable', t('title_enable', 'Enable'), 'submit', '', 'on'); ?>
					<?php echo f::form_button('disable', t('title_disable', 'Disable'), 'submit', '', 'off'); ?>
				</div>
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
	$('.data-table input[name^="reviews["]').change(function() {
		if ($('.data-table input[name^="reviews["]:checked').length > 0) {
			$('fieldset').prop('disabled', false);
		} else {
			$('fieldset').prop('disabled', true);
		}
	}).trigger('change');
</script>
