<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_stock_items', 'Stock Items');

  breadcrumbs::add(language::translate('title_stock_items', 'Stock Items'));

// Table Rows
  $stock_items = [];

	if (!empty($_GET['query'])) {
		$sql_where_query = [
			"si.id = '". database::input($_GET['query']) ."'",
			"sii.name like '%". database::input($_GET['query']) ."%'",
			"si.code regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
			"si.sku regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
			"si.mpn regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
			"si.gtin regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
		];
	}

	$stock_items_query = database::query(
		"select si.*, sii.name from ". DB_TABLE_PREFIX ."stock_items si
		left join ". DB_TABLE_PREFIX ."stock_items_info sii on (si.id = sii.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')
		where si.id
		". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
		order by si.sku, sii.name;"
	);

  if ($_GET['page'] > 1) database::seek($stock_items_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($stock_item = database::fetch($stock_items_query)) {
    $stock_items[] = $stock_item;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
	}

// Number of Rows
  $num_rows = database::num_rows($stock_items_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="card card-app">
  <div class="card-heading">
    <div class="card-title">
      <div class="card-title">
        <?php echo $app_icon; ?> <?php echo language::translate('title_stock_items', 'Stock Items'); ?>
      </div>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink('catalog/edit_stock_item'), language::translate('title_create_new_stock_item', 'Create New Stock Item'), '', 'add'); ?>
  </div>

  <div class="card-filter">
    <?php echo functions::form_draw_form_begin('search_form', 'get') . functions::form_draw_hidden_field('app', true) . functions::form_draw_hidden_field('doc', true); ?>
    <ul class="list-inline float-end">
      <li><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_items', 'Search items').'"'); ?></li>
    </ul>
    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <?php echo functions::form_draw_form_begin('stock_items_form', 'post'); ?>

    <table class="table table-striped data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_mpn', 'MPN'); ?></th>
          <th><?php echo language::translate('title_gtin', 'GTIN'); ?></th>
          <th><?php echo language::translate('title_ordered', 'Ordered'); ?></th>
          <th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($stock_items as $stock_item) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('stock_items['. $stock_item['id'] .']', $stock_item['id']); ?></td>
          <td><?php echo $stock_item['id']; ?></td>
          <td><?php echo $stock_item['sku']; ?></td>
          <td><a href="<?php echo document::link('catalog/edit_stock_item', ['stock_item_id' => $stock_item['id']]); ?>"><?php echo $stock_item['name']; ?></a></td>
          <td><?php echo $stock_item['mpn']; ?></td>
          <td><?php echo $stock_item['gtin']; ?></td>
          <td class="text-end"><?php echo (float)$stock_item['ordered']; ?></td>
          <td class="text-end"><?php echo (float)$stock_item['quantity']; ?></td>
          <td><a href="<?php echo document::href_ilink('catalog/edit_stock_item', ['stock_item_id' => $stock_item['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="10"><?php echo language::translate('title_stock_items', 'Stock Items'); ?>: <?php echo database::num_rows($stock_items_query); ?></td>
        </tr>
      </tfoot>
    </table>

  <?php echo functions::form_draw_form_end(); ?>
</div>