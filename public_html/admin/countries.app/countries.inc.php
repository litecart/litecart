<?php
  document::$snippets['title'][] = language::translate('title_countries', 'Countries');

  breadcrumbs::add(language::translate('title_countries', 'Countries'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['countries'])) throw new Exception(language::translate('error_must_select_countries', 'You must select countries'));

      foreach ($_POST['countries'] as $country_id) {
        $country = new ent_country($country_id);
        $country->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $country->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $countries = [];

  $countries_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."countries
    order by status desc, name asc;"
  );

  while ($country = database::fetch($countries_query)) {
    $countries[] = $country;
  }

// Number of Rows
  $num_rows = database::num_rows($countries_query);
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_countries', 'Countries'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['doc' => 'edit_country'], true), language::translate('title_add_new_country', 'Add New Country'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('countries_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th><?php echo language::translate('title_code', 'Code'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_zones', 'Zones'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($countries as $country) { ?>
        <tr class="<?php echo empty($country['status']) ? 'semi-transparent' : null; ?>">
          <td><?php echo functions::form_draw_checkbox('countries[]', $country['id']); ?></td>
          <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($country['status']) ? '#88cc44' : '#ff6644') .'";'); ?></td>
          <td><?php echo $country['id']; ?></td>
          <td><?php echo $country['iso_code_2']; ?></td>
          <td><a class="link" href="<?php echo document::href_link('', ['doc' => 'edit_country', 'country_code' => $country['iso_code_2']], true); ?>"><?php echo $country['name']; ?></a></td>
          <td class="text-center"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."zones where country_code = '". database::input($country['iso_code_2']) ."'")); ?></td>
          <td><a class="btn btn-default btn-sm" href="<?php echo document::href_link('', ['doc' => 'edit_country', 'country_code' => $country['iso_code_2']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="7"><?php echo language::translate('title_countries', 'Countries'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions" disabled>
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?></legend>

        <div class="btn-group">
          <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
          <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
        </div>
      </fieldset>
    </div>

  <?php echo functions::form_draw_form_end(); ?>
</div>

<script>
  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>