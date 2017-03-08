<?php
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {

    if (!empty($_POST['countries'])) {

      $countries = array();
      foreach ($_POST['countries'] as $country_code) {

        if (!empty($_POST['disable']) && $country_code == settings::get('default_country_code')) {
          notices::add('errors', language::translate('error_cannot_disable_default_country', 'You cannot disable the default country'));
          continue;
        }

        if (!empty($_POST['disable']) && $country_code == settings::get('store_country_code')) {
          notices::add('errors', language::translate('error_cannot_disable_store_country', 'You cannot disable the store country'));
          continue;
        }

        $country = new ctrl_country($country_code);
        $country->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $country->save();
      }

      header('Location: '. document::link());
      exit;
    }
  }
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_country'), true), language::translate('title_add_new_country', 'Add New Country'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_countries', 'Countries'); ?></h1>

<?php echo functions::form_draw_form_begin('countries_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th><?php echo language::translate('title_code', 'Code'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_zones', 'Zones'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $countries_query = database::query(
    "select * from ". DB_TABLE_COUNTRIES ."
    order by status desc, name asc;"
  );

  if (database::num_rows($countries_query) > 0) {

    while ($country = database::fetch($countries_query)) {
?>
    <tr class="<?php echo empty($country['status']) ? 'semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('countries['. $country['iso_code_2'] .']', $country['iso_code_2']); ?></td>
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($country['status']) ? '#99cc66' : '#ff6666') .'";'); ?></td>
      <td><?php echo $country['id']; ?></td>
      <td><?php echo $country['iso_code_2']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_country', 'country_code' => $country['iso_code_2']), true); ?>"><?php echo $country['name']; ?></a></td>
      <td class="text-center"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_ZONES ." where country_code = '". database::input($country['iso_code_2']) ."'")); ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_country', 'country_code' => $country['iso_code_2']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7"><?php echo language::translate('title_countries', 'Countries'); ?>: <?php echo database::num_rows($countries_query); ?></td>
      </tr>
    </tfoot>
  </table>

  <p class="btn-group">
    <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
    <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>