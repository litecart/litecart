<?php

	administrator::require_login();

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
					"update ". DB_PREFIX ."reviews
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
		from ". DB_PREFIX ."reviews pr
		left join ". DB_PREFIX ."products_info pi on (pi.product_id = pr.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
		". (!empty($sql_find) ? "where (". implode(" or ", $sql_find) .")" : "") ."
		order by updated_at desc;"
	)->fetch_page(function(&$review){
		$review['title'] = json_decode($review['title'], true) ?: [];
		$review['description'] = json_decode($review['description'], true) ?: [];
		$review['attachments'] = json_decode($review['attachments'], true) ?: [];
	}, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);


?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_reviews', 'Reviews') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/reviews_csv'), t('title_import_export_csv', 'Import/Export CSV')) ?>
		<?= f::form_button_link(document::ilink(__APP__.'/edit_review'), t('title_create_new_review', 'Create New Review'), '', 'add') ?>
	</div>

	<div class="card-filter">
		<?= f::form_begin('search_form', 'get') ?>
			<ul class="list-inline">
				<li class="expandable"><?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]) ?></li>
				<li><?= f::form_button('filter', t('title_search', 'Search'), 'submit') ?></li>
			</ul>
		<?= f::form_end() ?>
	</div>

	<?= f::form_begin('reviews_form', 'post') ?>

		<table class="table table-striped data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th><?= t('title_product', 'Product') ?></th>
					<th><?= t('title_customer', 'Customer') ?></th>
					<th class="main"><?= t('title_title', 'Title') ?></th>
					<th><?= t('title_rating', 'Rating') ?></th>
					<th><?= t('title_created', 'Created') ?> / <?= t('title_updated', 'Updated') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($reviews as $review) { ?>
				<tr class="<?= $review['status'] ? false : ' semi-transparent' ?>">
					<td><?= f::form_checkbox('reviews[]', $review['id']) ?></td>
					<td><?= f::draw_fonticon('icon-circle', 'style="color: '. (!empty($review['status']) ? '#99cc66' : '#ff6666') .';"') ?></td>
					<td><?= $review['id'] ?></td>
					<td>
						<a class="link" href="<?= document::href_ilink(__APP__.'/edit_review', ['review_id' => $review['id']]) ?>">
							<?= $review['product_name'] ?>
						</a>
					</td>
					<td><?= !empty($review['customer_name']) ? $review['customer_name'] : '<em>'. t('title_guest', 'Guest') .'</em>' ?></td>
					<td><?= $review['title'] ?></td>
					<td class="text-center"><?= $review['rating'] ?></td>
					<td><?= $review['updated_at'] > $review['created_at'] ? $review['updated_at'] : $review['created_at'] ?></td>
					<td>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_review', ['review_id' => $review['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_reviews', 'Reviews') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>
				<legend><?= t('text_with_selected', 'With selected') ?></legend>

				<div class="btn-group">
					<?= f::form_button_predefined('enable') ?>
					<?= f::form_button_predefined('disable') ?>
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
	$('.data-table input[name^="reviews["]').change(function() {
		if ($('.data-table input[name^="reviews["]:checked').length > 0) {
			$('fieldset').prop('disabled', false);
		} else {
			$('fieldset').prop('disabled', true);
		}
	}).trigger('change');
</script>
