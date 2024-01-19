<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  document::$title[] = language::translate('title_brands', 'Brands');

  breadcrumbs::add(language::translate('title_brands', 'Brands'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {

      if (empty($_POST['brands'])) {
        throw new Exception(language::translate('error_must_select_brands', 'You must select brands'));
      }

      foreach ($_POST['brands'] as $brand_id) {
        $brand = new ent_brand($brand_id);
        $brand->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $brand->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows, Total Number of Rows, Total Number of Pages
  $brands = database::query(
    "select b.*, p.num_products
    from ". DB_TABLE_PREFIX ."brands b
    left join (
      select brand_id, count(id) as num_products
      from ". DB_TABLE_PREFIX ."products
      group by brand_id
    ) p on (p.brand_id = b.id)
    order by name asc;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_brands', 'Brands'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_button_link(document::ilink(__APP__.'/edit_brand'), language::translate('title_create_new_brand', 'Create New Brand'), '', 'add'); ?>
  </div>

  <?php echo functions::form_begin('brands_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_products', 'Products'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($brands as $brand) { ?>
        <tr class="<?php if (empty($brand['status'])) echo 'semi-transparent'; ?>">
          <td><?php echo functions::form_input_checkbox('brands[]', $brand['id']); ?></td>
          <td><?php echo functions::draw_fonticon($brand['status'] ? 'on' : 'off'); ?></td>
          <td><?php if ($brand['featured']) echo functions::draw_fonticon('fa-star', 'style="color: #ffd700;"'); ?></td>
          <td><?php echo functions::draw_thumbnail('storage://images/' . ($brand['image'] ? $brand['image'] : 'no_image.png'), 16, 16, 'fit', 'style="vertical-align: bottom;"'); ?></td>
          <td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_brand', ['brand_id' => $brand['id']]); ?>"><?php echo $brand['name']; ?></a></td>
          <td class="text-center"><?php echo (int)$brand['num_products']; ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_brand', ['brand_id' => $brand['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="7"><?php echo language::translate('title_brands', 'Brands'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions">
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?></legend>

        <div class="btn-group">
          <?php echo functions::form_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
          <?php echo functions::form_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
        </div>
      </fieldset>
    </div>

  <?php echo functions::form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>

<script>
  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>