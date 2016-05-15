<?php
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {

    if (!empty($_POST['countries'])) {
      foreach ($_POST['countries'] as $key => $value) $_POST['countries'][$key] = database::input($value);
      database::query(
        "update ". DB_TABLE_COUNTRIES ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['countries']) ."');"
      );
    }

    header('Location: '. document::link());
    exit;
  }
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_country'), true), language::translate('title_add_new_country', 'Add New Country'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_countries', 'Countries'); ?></h1>

<?php echo functions::form_draw_form_begin('countries_form', 'post'); ?>

  <table width="100%" align="center" class="dataTable">
    <tr class="header">
      <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
      <th></th>
      <th><?php echo language::translate('title_id', 'ID'); ?></th>
      <th><?php echo language::translate('title_code', 'Code'); ?></th>
      <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
      <th><?php echo language::translate('title_zones', 'Zones'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  $countries_query = database::query(
    "select * from ". DB_TABLE_COUNTRIES ."
    order by status desc, name asc;"
  );

  if (database::num_rows($countries_query) > 0) {

    while ($country = database::fetch($countries_query)) {
?>
    <tr class="row<?php echo !$country['status'] ? ' semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('countries['. $country['id'] .']', $country['id']); ?></td>
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($country['status']) ? '#99cc66' : '#ff6666') .'";'); ?></td>
      <td><?php echo $country['id']; ?></td>
      <td><?php echo $country['iso_code_2']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_country', 'country_code' => $country['iso_code_2']), true); ?>"><?php echo $country['name']; ?></a></td>
      <td><?php echo database::num_rows(database::query("select id from ". DB_TABLE_ZONES ." where country_code = '". database::input($country['iso_code_2']) ."'")); ?></td>
      <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_country', 'country_code' => $country['iso_code_2']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
    }
  }
?>
    <tr class="footer">
      <td colspan="7"><?php echo language::translate('title_countries', 'Countries'); ?>: <?php echo database::num_rows($countries_query); ?></td>
    </tr>
  </table>

  <script>
    $(".dataTable .checkbox-toggle").click(function() {
      $(this).closest("form").find(":checkbox").each(function() {
        $(this).attr('checked', !$(this).attr('checked'));
      });
      $(".dataTable .checkbox-toggle").attr("checked", true);
    });

    $('.dataTable tr').click(function(event) {
      if ($(event.target).is('input:checkbox')) return;
      if ($(event.target).is('a, a *')) return;
      if ($(event.target).is('th')) return;
      $(this).find('input:checkbox').trigger('click');
    });
  </script>

  <p><span class="button-set"><?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></span></p>

<?php
  echo functions::form_draw_form_end();
?>