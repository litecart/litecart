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

  $query = database::query(
    "select c.id, ci.name from ". DB_TABLE_PREFIX ."categories c
    left join ". DB_TABLE_PREFIX ."categories_info ci on (c.id = ci.category_id and ci.language_code = '". database::input(language::$selected['code']) ."')
    where c.parent_id = ". (!empty($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0) ."
    order by c.priority, ci.name;"
  );

  $categories = [];
  while ($category = database::fetch($query)) {
    $categories[] = $category;
  }

?>
<div id="modal-category-picker" class="modal fade" style="width: 640px;">

	<div class="modal-body">

    <button class="btn btn-primary" name="select" type="button" data-id="<?php echo !empty($_GET['parent_id']) ? (int)$_GET['parent_id'] : '0'; ?>" data-name="<?php echo functions::escape_html(!empty($_GET['parent_id']) ? reference::category($_GET['parent_id'])->name : language::translate('title_root', 'Root')); ?>" style="position: absolute; right: 1.5em; margin-inline-start: 1em;">
      <?php echo language::translate('title_select', 'Select'); ?>
    </button>

    <ul class="nav nav-pills" style="margin-bottom: 1em;">
      <li>
        <a href="<?php echo document::link(null, ['parent_id' => 0], true); ?>" data-id="0">
          <?php echo language::translate('title_root', 'Root'); ?>
        </a>
      </li>
      <?php foreach ($breadcrumbs as $category) { ?>
      <li>
        <a href="<?php echo document::link(null, ['parent_id' => $category['id']], true); ?>" data-id="<?php echo (int)$category['id']; ?>">
          <?php echo $category['name']; ?>
        </a>
      </li>
      <?php } ?>
    </ul>

		<ul class="nav nav-pills nav-stacked">
      <?php if (!empty($_GET['parent_id'])) { ?>
      <li>
        <a href="<?php echo document::link(null, ['parent_id' => reference::category($_GET['parent_id'])->parent_id], true); ?>">
          <?php echo functions::draw_fonticon('fa-arrow-left'); ?> <?php echo language::translate('title_back', 'Back'); ?>
        </a>
      <li>
      <?php } ?>
      <?php foreach ($categories as $category) { ?>
      <li>
        <a href="<?php echo document::link(null, ['parent_id' => $category['id']], true); ?>">
          <?php echo functions::draw_fonticon('fa-folder fa-lg', 'style="color: #cccc66;"'); ?> <?php echo !empty($category['name']) ? $category['name'] : '[untitled]'; ?>
        </a>
      <li>
      <?php } ?>
		</ul>
	</div>

</div>

<script>
	$('#modal-category-picker').on('click', 'a', function(e){
    e.preventDefault();
    $('.modal-body').load($(this).attr('href')+' .modal-body');
  });

	$('#modal-category-picker').on('click', 'button[name="select"]', function() {
    var field = $.featherlight.current().$currentTarget.closest('.input-group');
    var id = $(this).data('id'), name = $(this).data('name');

    $(field).find(':input').val(id).trigger('change');
    $(field).find('.name').text(name);
    $(field).find('a').attr('href', $(field).find('a').attr('href').replace(/(parent_id)=\d*/, '$1='+id));
    $.featherlight.close();
	});
</script>