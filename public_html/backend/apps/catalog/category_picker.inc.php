<?php
	document::$layout = 'ajax';

	$breadcrumbs = [];
	if (!empty($_GET['parent_id'])) {
		foreach (reference::category($_GET['parent_id'])->path as $id => $category) {
			$breadcrumbs[] = [
				'id' => $id,
				'name' => $category->name,
			];
		}
	}

	$categories = database::query(
		"select c.id, ci.name from ". DB_TABLE_PREFIX ."categories c
		left join ". DB_TABLE_PREFIX ."categories_info ci on (c.id = ci.category_id and ci.language_code = '". database::input(language::$selected['code']) ."')
		where ". (!empty($_GET['parent_id']) ? "c.parent_id = ". (int)$_GET['parent_id'] : "c.parent_id is null") ."
		order by c.priority, ci.name;"
	)->fetch_all();

?>
<div id="modal-category-picker" class="modal fade" style="width: 640px;">

	<div class="modal-body">

		<button class="btn btn-default" name="select" type="button" data-id="<?php echo !empty($_GET['parent_id']) ? (int)$_GET['parent_id'] : '0'; ?>" data-name="<?php echo !empty($_GET['parent_id']) ? reference::category($_GET['parent_id'])->name : language::translate('title_root', 'Root'); ?>" style="position: absolute; right: 1.5em; margin-inline-start: 1em;">
			<?php echo language::translate('title_select', 'Select'); ?>
		</button>

		<nav class="nav nav-pills" style="margin-bottom: 1em;">
			<a class="nav-link" href="<?php echo document::href_ilink(null, ['parent_id' => 0]); ?>" data-id="0">
				<?php echo language::translate('title_root', 'Root'); ?>
			</a>
			<?php foreach ($breadcrumbs as $category) { ?>
			<a class="nav-link" href="<?php echo document::href_ilink(null, ['parent_id' => $category['id']]); ?>" data-id="<?php echo $category['id']; ?>">
				<?php echo $category['name']; ?>
			</a>
			<?php } ?>
		</nav>

		<nav class="nav nav-pills nav-stacked">
			<?php if (!empty($_GET['parent_id'])) { ?>
			<a class="nav-link" href="<?php echo document::href_ilink(null, ['parent_id' => reference::category($_GET['parent_id'])->parent_id]); ?>">
				<?php echo functions::draw_fonticon('icon-arrow-left'); ?> <?php echo language::translate('title_back', 'Back'); ?>
			</a>
			<?php } ?>
			<?php foreach ($categories as $category) { ?>
			<a class="nav-link" href="<?php echo document::href_ilink(null, ['parent_id' => $category['id']]); ?>">
				<?php echo functions::draw_fonticon('icon-folder', 'style="color: #cccc66;"'); ?> <?php echo fallback($category['name'], '[untitled]'); ?>
			</a>
			<?php } ?>
		</nav>
	</div>

</div>

<script>
	$('#modal-category-picker').on('click', 'a', function(e){
		e.preventDefault();
		$('.modal-body').load($(this).attr('href')+' .modal-body');
	});

	$('#modal-category-picker').on('click', 'button[name="select"]', function() {
		let field = $.featherlight.current().$currentTarget.closest('.input-group'),
			id = $(this).data('id'), name = $(this).data('name');

		$(field).find(':input').val(id).trigger('change');
		$(field).find('.name').text(name);
		$(field).find('a').attr('href', $(field).find('a').attr('href').replace(/(parent_id)=\d*/, '$1='+id));
		$.featherlight.close();
	});
</script>