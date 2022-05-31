<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_geo_zones', 'Geo Zones');

  breadcrumbs::add(language::translate('title_geo_zones', 'Geo Zones'));

  if (isset($_POST['clone'])) {

    try {
      if (empty($_POST['geo_zones'])) throw new Exception(language::translate('error_must_select_geo_zones', 'You must select geo zones'));

      foreach ($_POST['geo_zones'] as $geo_zone_id) {
        $original = new ent_geo_zone($geo_zone_id);
        $geo_zone = new ent_geo_zone();

        $geo_zone->data = $original->data;
        $geo_zone->data['id'] = null;
        $geo_zone->data['name'] .= ' (Copy)';

        foreach (array_keys($geo_zone->data['zones']) as $key) {
          $geo_zone->data['zones'][$key]['id'] = null;
        }

        $geo_zone->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $geo_zones = [];

  $geo_zones_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."geo_zones
    order by name asc;"
  );

  if ($_GET['page'] > 1) database::seek($geo_zones_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($geo_zone = database::fetch($geo_zones_query)) {
    $geo_zones[] = $geo_zone;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($geo_zones_query);

// Pagination
  $num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_geo_zones', 'Geo Zones'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_geo_zone'), language::translate('title_create_new_geo_zone', 'Create New Geo Zone'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('geo_zones_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_zones', 'Zones'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($geo_zones as $geo_zone) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('geo_zones[]', $geo_zone['id']); ?></td>
          <td><?php echo $geo_zone['id']; ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_geo_zone', ['geo_zone_id' => $geo_zone['id']]); ?>"><?php echo $geo_zone['name']; ?></a></td>
          <td class="text-center"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."zones_to_geo_zones where geo_zone_id = ". (int)$geo_zone['id'] ."")); ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_geo_zone', ['geo_zone_id' => $geo_zone['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="5"><?php echo language::translate('title_geo_zones', 'Geo Zones'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions">
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

        <?php echo functions::form_draw_button('clone', language::translate('title_clone', 'Clone'), 'submit', 'fa-file-copy'); ?>
      </fieldset>
    </div>

  <?php echo functions::form_draw_form_end(); ?>

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