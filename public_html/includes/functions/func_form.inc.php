<?php

  function form_draw_form_begin($name='', $method='post', $action=false, $multipart=false, $parameters='') {
    return  '<form'. (($name) ? ' name="'. htmlspecialchars($name) .'"' : false) .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'" accept-charset="'. language::$selected['charset'] .'"'. (($action) ? ' action="'. htmlspecialchars($action) .'"' : '') . (($parameters) ? ' ' . $parameters : false) .'>';
  }

  function form_draw_form_end() {
    return '</form>' . PHP_EOL;
  }

  function form_reinsert_value($name, $array_value=null) {
    if (empty($name)) return;

    foreach ([$_POST, $_GET] as $superglobal) {
      if (empty($superglobal)) continue;

      foreach (explode('&', http_build_query($superglobal, '', '&')) as $pair) {

        @list($key, $value) = explode('=', $pair);
        $key = urldecode($key);
        $value = urldecode($value);

        if ($key == $name) return $value;

        if (preg_replace('#^(.*)\[[^\]]*\]$#', '$1', $key) == preg_replace('#^(.*)\[[^\]]*\]$#', '$1', $name)) {
          if (preg_match('#\[[0-9]*\]$#', $key)) {
            if ($value != $array_value) continue;
            return $value;
          }
        }
      }
    }
  }

  function form_draw_button($name, $value, $type='submit', $parameters='', $fonticon='') {

    if (is_array($value)) {
      list($value, $title) = $value;
    } else {
      $title = $value;
    }

    return '<button '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. ((!empty($fonticon)) ? functions::draw_fonticon($fonticon) . ' ' : '') . $title .'</button>';
  }

  function form_draw_captcha_field($name, $id, $parameters='') {

   return '<div class="input-group">' . PHP_EOL
        . '  <span class="input-group-addon" style="padding: 0;">'. functions::captcha_generate(100, 40, 4, $id, 'numbers', 'align="absbottom"') .'</span>' . PHP_EOL
        . '  ' . form_draw_text_field('captcha', '', $parameters . ' style="font-size: 24px; padding: 0; text-align: center;"') . PHP_EOL
        . '</div>';
  }

  function form_draw_category_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    $account_name = language::translate('title_root', 'Root');

    if (!empty($value)) {
      $category_query = database::query(
        "select c.id, c.code, ci.name, c.date_created from ". DB_PREFIX ."categories c
        left join ". DB_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code)
        where c.id = ". (int)$value ."
        limit 1;"
      );

      if ($category = database::fetch($category_query)) {
        $account_name = $category['name'];
      }
    }

    functions::draw_lightbox();

    return '<div class="input-group"'. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL
         . '  <div class="form-control">' . PHP_EOL
         . '    ' . form_draw_hidden_field($name, true) . PHP_EOL
         . '    '. functions::draw_fonticon('folder') .' <span class="name" style="display: inline-block;">'. $account_name .'</span>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '  <div style="align-self: center;">' . PHP_EOL
         . '    <a href="'. document::href_link(WS_DIR_ADMIN, ['app' => 'catalog', 'doc' => 'category_picker', 'parent_id' => $value]) .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. language::translate('title_change', 'Change') .'</a>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '</div>';
  }

  function form_draw_checkbox($name, $value, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name, $value);

    return '<input type="checkbox" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" '. ($input === $value ? ' checked' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
  }

  function form_draw_color_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="color" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="color" '. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_csv_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    if (!$csv = functions::csv_decode($value)) {
      return form_draw_textarea($name, $value, $parameters);
    }

    $columns = array_keys($csv[0]);

    $html = '<table class="table table-striped table-hover data-table" data-toggle="csv">' . PHP_EOL
          . '  <thead>' . PHP_EOL
          . '    <tr>' . PHP_EOL;

    foreach ($columns as $column) {
      $html .= '      <th>'. $column .'</th>' . PHP_EOL;
    }

    $html .= '      <th><a class="add-column" href="#">'. functions::draw_fonticon('fa-plus', 'style="color: #66cc66;"') .'</a></th>' . PHP_EOL
           . '    </tr>' . PHP_EOL
           . '  </thead>' . PHP_EOL
           . '  <tbody>' . PHP_EOL;

    foreach ($csv as $line => $row) {
      $html .= '    <tr>' . PHP_EOL;
      foreach ($columns as $column) {
        $html .= '      <td contenteditable>'. $row[$column] .'</td>' . PHP_EOL;
      }
      $html .= '      <td><a class="remove" href="#">'. functions::draw_fonticon('fa-times-circle', 'style="color: #d33"') .'</a></td>' . PHP_EOL
             . '    </tr>' . PHP_EOL;
    }

    $html .= '  </tbody>' . PHP_EOL
           . '  <tfoot>' . PHP_EOL
           . '    <tr>' . PHP_EOL
           . '      <td colspan="'. (count($columns)+1) .'"><a class="add-row" href="#">'. functions::draw_fonticon('fa-plus', 'style="color: #66cc66;"') .'</a></td>' . PHP_EOL
           . '    </tr>' . PHP_EOL
           . '  </tfoot>' . PHP_EOL
           . '</table>' . PHP_EOL
           . PHP_EOL
           . form_draw_textarea($name, $value, 'style="display: none;"');

    document::$snippets['javascript']['table2csv'] =
<<<END
$('table[data-toggle="csv"]').on('click', '.remove', function(e) {
  e.preventDefault();
  var parent = $(this).closest('tbody');
  $(this).closest('tr').remove();
  $(parent).trigger('keyup');
});

$('table[data-toggle="csv"] .add-row').click(function(e) {
  e.preventDefault();
  var n = $(this).closest('table').find('thead th:not(:last-child)').length;
  $(this).closest('table').find('tbody').append(
    '<tr>' + ('<td contenteditable></td>'.repeat(n)) + '<td><a class="remove" href="#"><i class="fa fa-times-circle" style="color: #d33;"></i></a></td>' +'</tr>'
  ).trigger('keyup');
});

$('table[data-toggle="csv"] .add-column').click(function(e) {
  e.preventDefault();
  var table = $(this).closest('table');
  var title = prompt("<?php echo language::translate('title_column_title', 'Column Title'); ?>");
  if (!title) return;
  $(table).find('thead tr th:last-child:last-child').before('<th>'+ title +'</th>');
  $(table).find('tbody tr td:last-child:last-child').before('<td contenteditable></td>');
  $(table).find('tfoot tr td').attr('colspan', $(this).closest('table').find('tfoot tr td').attr('colspan') + 1);
  $(this).trigger('keyup');
});

$('table[data-toggle="csv"]').keyup(function(e) {
   var csv = $(this).find('thead tr, tbody tr').map(function (i, row) {
      return $(row).find('th:not(:last-child),td:not(:last-child)').map(function (j, col) {
        var text = \$(col).text();
        if (/("|,)/.test(text)) {
          return '"'+ text.replace(/"/g, '""') +'"';
        } else {
          return text;
        }
      }).get().join(',');
    }).get().join("\\r\\n");
  $(this).next('textarea').val(csv);
});
END;

    return $html;
  }

  function form_draw_currency_field($currency_code, $name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (empty($currency_code)) $currency_code = settings::get('store_currency_code');

    return '<div class="input-group">' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="number" step="any" name="'. htmlspecialchars($name) .'" value="'. (!empty($value) ? round($value, currency::$currencies[$currency_code]['decimals']+2) : '') .'" data-type="currency"'. (($parameters) ? ' '. $parameters : false) .' />' . PHP_EOL
         . '  <strong class="input-group-addon" style="opacity: 0.75;">'. $currency_code .'</strong>' . PHP_EOL
         . '</div>';
  }

  function form_draw_customer_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    $account_name = language::translate('title_guest', 'Guest');

    if (!empty($value)) {
      $customer_query = database::query(
        "select * from ". DB_PREFIX ."customers
        where id = ". (int)$value ."
        limit 1;"
      );

      if ($customer = database::fetch($customer_query)) {
        $account_name = $customer['company'] ? $customer['company'] : $customer['firstname'] .' '. $customer['lastname'];
      } else {
        $account_name = '<em>'. language::translate('title_unknown', 'Unknown') .'</em>';
      }
    }

    return '<div class="form-control"'. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL
         . '  ' . form_draw_hidden_field($name, true) . PHP_EOL
         . '  '. language::translate('title_id', 'ID') .': <span class="id">'. (int)$value .'</span> &ndash; <span class="name">'. $account_name .'</span> <a href="'. document::href_link(WS_DIR_ADMIN, ['app' => 'customers', 'doc' => 'customer_picker']) .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-left: 5px;">'. language::translate('title_change', 'Change') .'</a>' . PHP_EOL
         . '</div>';
  }

  function form_draw_date_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!in_array(substr($value, 0, 10), ['', '0000-00-00', '1970-00-00', '1970-01-01'])) {
      $value = date('Y-m-d', strtotime($value));
    } else {
      $value = '';
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="date" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="date" placeholder="YYYY-MM-DD"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_datetime_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!in_array(substr($value, 0, 10), ['', '0000-00-00', '1970-00-00', '1970-01-01'])) {
      $value = date('Y-m-d\TH:i', strtotime($value));
    } else {
      $value = '';
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="datetime-local" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="datetime" placeholder="YYYY-MM-DD [hh:nn]"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_decimal_field($name, $value=true, $decimals=2, $min=null, $max=null, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    $value = number_format((float)$value, (int)$decimals, '.', '');

    document::$snippets['javascript']['input-decimal-replace-decimal'] = '  $(\'body\').on(\'change\', \'input[data-type="decimal"]\', function(){' . PHP_EOL
                                                                       . '    $(this).val($(this).val().replace(\',\', \'.\'));' . PHP_EOL
                                                                       . '  });';

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="number" name="'. htmlspecialchars($name) .'" value="'. (float)$value .'" data-type="decimal" step="any" '. (($min !== null) ? 'min="'. (float)$min .'"' : false) . (($max !== null) ? ' max="'. (float)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_email_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-envelope-o fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="email" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="email"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_file_field($name, $parameters='') {

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="file" name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_fonticon_field($name, $value=true, $type, $icon, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon($icon) .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />' . PHP_EOL
         . '</div>';
  }

  function form_draw_hidden_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input type="hidden" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_image($name, $src, $parameters='') {
    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="image" name="'. htmlspecialchars($name) .'" src="'. htmlspecialchars($src) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_input($name, $value=true, $type='text', $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_link_button($url, $title, $parameters='', $fonticon='') {

    if (empty($url)) {
      $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    return '<a '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default"' : '') .' href="'. htmlspecialchars($url) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. (!empty($fonticon) ? functions::draw_fonticon($fonticon) . ' ' : false) . $title .'</a>';
  }

  function form_draw_month_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!in_array(substr($value, 0, 7), ['', '0000-00', '1970-00', '1970-01'])) {
      $value = date('Y-m', strtotime($value));
    } else {
      $value = '';
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="month" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="month" maxlength="7" pattern="[0-9]{4}-[0-9]{2}" placeholder="YYYY-MM"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_number_field($name, $value=true, $min=null, $max=null, $parameters='') {
    if ($value === true) $value = (int)form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="number" name="'. htmlspecialchars($name) .'" value="'. (int)$value .'" data-type="number" step="1" '. (($min !== null) ? 'min="'. (float)$min .'"' : false) . (($max !== null) ? ' max="'. (float)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_password_field($name, $value='', $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-key fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="password" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="password"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_phone_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-phone fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="tel" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="phone" pattern="^\+?([0-9]|-| )+$"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_radio_button($name, $value, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name, $value);

    return '<input type="radio" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" '. ($input === $value ? ' checked' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
  }

  function form_draw_range_slider($name, $value=true, $min='', $max='', $step='', $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="range" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="range" min="'. (float)$min .'" max="'. (float)$max .'" step="'. (float)$step .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_regional_input_field($language_code, $name, $value=true, $parameters='') {
    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon"><img src="'. document::href_link(WS_DIR_STORAGE . 'images/languages/'. $language_code .'.png') .'" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_text_field($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_textarea($language_code, $name, $value=true, $parameters='') {

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon" style="vertical-align: top;"><img src="'. document::href_link(WS_DIR_STORAGE . 'images/languages/'. $language_code .'.png') .'" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_textarea($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_wysiwyg_field($language_code, $name, $value=true, $parameters='') {

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon" style="vertical-align: top;"><img src="'. document::href_link(WS_DIR_STORAGE . 'images/languages/'. $language_code .'.png') .'" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_wysiwyg_field($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_search_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-search fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="search" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="search"'. (($parameters) ? ' '.$parameters : false) .' />' . PHP_EOL
         . '</div>';
  }

  function form_draw_select_field($name, $options=[], $input=true, $parameters='') {

    $html = '<select class="form-control" name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;

    foreach ($options as $option) {

      if (!is_array($option)) $option = [$option, $option];

      if ($input === true) {
        $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
      } else {
        $option_input = $input;
      }

      if (!is_array($option)) $option = [$option, $option];

      $html .= '  <option value="'. htmlspecialchars(isset($option[1]) ? $option[1] : $option[0]) .'"'. (isset($option[1]) ? (($option[1] == $option_input) ? ' selected="selected"' : false) : (($option[0] == $option_input) ? ' selected="selected"' : false)) . ((isset($option[2])) ? ' ' . $option[2] : false) . '>'. $option[0] .'</option>' . PHP_EOL;
    }

    $html .= '</select>';

    return $html;
  }

  function form_draw_select_multiple_field($name, $options=[], $input=true, $parameters='') {

    $html = '<div class="form-control" style="overflow-y: auto; max-height: 200px;" ' . (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;

    foreach ($options as $option) {

      if (!is_array($option)) $option = [$option, $option];

      if ($input === true) {
        $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
      } else {
        $option_input = $input;
      }

      if (!is_array($option)) $option = [$option, $option];

      $html .= '  <div class="checkbox">'. PHP_EOL
             . '    <label>'. form_draw_checkbox($name, isset($option[1]) ? $option[1] : $option[0], $option_input, isset($option[2]) ? $option[2] : null) .' '.  $option[0] .'</label>' . PHP_EOL
             . '  </div>';
    }

    $html .= '</div>';

    return $html;
  }

  function form_draw_select_optgroup_field($name, $groups=[], $input=true, $multiple=false, $parameters='') {
    if (!is_array($groups)) $groups = [$groups];

    $html = '<div class="form-control">' . PHP_EOL
          . '  <select name="'. htmlspecialchars($name) .'"'. (($multiple) ? ' multiple' : false) .''. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;

    foreach ($groups as $group) {
      $html .= '    <optgroup label="'. $group['label'] .'">' . PHP_EOL;
      foreach ($group['options'] as $option) {
        if ($input === true) {
          $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
        } else {
          $option_input = $input;
        }
        $html .= '      <option value="'. htmlspecialchars(isset($option[1]) ? $option[1] : $option[0]) .'"'. (isset($option[1]) ? (($option[1] == $option_input) ? ' selected="selected"' : false) : (($option[0] == $option_input) ? ' selected="selected"' : false)) . ((isset($option[2])) ? ' ' . $option[2] : false) . '>'. $option[0] .'</option>' . PHP_EOL;
      }
      $html .= '    </optgroup>' . PHP_EOL;
    }

    $html .= '  </select>' . PHP_EOL
           . '</div>';

    return $html;
  }

  function form_draw_textarea($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<textarea '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. htmlspecialchars($value) .'</textarea>';
  }

  function form_draw_text_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name, $value);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="text" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="text"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_time_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="time" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="time"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_toggle($name, $input=true, $type='e/d', $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    $input = in_array(strtolower($input), ['1', 'active', 'enabled', 'on', 'true', 'yes']) ? '1' : '0';

    switch ($type) {
      case 'a/i':
        $true_text = language::translate('title_active', 'Active');
        $false_text = language::translate('title_inactive', 'Inactive');
        break;
      case 'e/d':
        $true_text = language::translate('title_enabled', 'Enabled');
        $false_text = language::translate('title_disabled', 'Disabled');
        break;
      case 'y/n':
        $true_text = language::translate('title_yes', 'Yes');
        $false_text = language::translate('title_no', 'No');
        break;
      case 'o/o':
        $true_text = language::translate('title_on', 'On');
        $false_text = language::translate('title_off', 'Off');
        break;
      case 't/f':
      default:
        $true_text = language::translate('title_true', 'True');
        $false_text = language::translate('title_false', 'False');
        break;
    }

    return '<div class="btn-group btn-block btn-group-inline" data-toggle="buttons">'. PHP_EOL
         . '  <label '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default'. (($input == '1') ? ' active' : '') .'"' : '') .'><input type="radio" name="'. htmlspecialchars($name) .'" value="1" '. (($input == '1') ? 'checked' : '') .' /> '. $true_text .'</label>'. PHP_EOL
         . '  <label '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default'. (($input == '0') ? ' active' : '') .'"' : '') .'><input type="radio" name="'. htmlspecialchars($name) .'" value="0" '. (($input == '0') ? 'checked' : '') .' /> '. $false_text .'</label>' . PHP_EOL
         . '</div>';
  }

  function form_draw_url_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="url" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="url"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_username_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-user fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="text" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="text"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_wysiwyg_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    document::$snippets['head_tags']['trumbowyg'] = '<link href="'. WS_DIR_APP .'vendor/trumbowyg/ui/trumbowyg.min.css" rel="stylesheet" />' . PHP_EOL
                                                  . '<link href="'. WS_DIR_APP .'vendor/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css" rel="stylesheet" />';

    document::$snippets['foot_tags']['trumbowyg'] = '<script src="'. WS_DIR_APP .'vendor/trumbowyg/trumbowyg.min.js"></script>' . PHP_EOL
                                                  . ((language::$selected['code'] != 'en') ? '<script src="'. WS_DIR_APP .'vendor/trumbowyg/langs/'. language::$selected['code'] .'.min.js"></script>' . PHP_EOL : '')
                                                  . '<script src="'. WS_DIR_APP .'vendor/trumbowyg/plugins/colors/trumbowyg.colors.min.js"></script>' . PHP_EOL
                                                  . '<script src="'. WS_DIR_APP .'vendor/trumbowyg/plugins/table/trumbowyg.upload.min.js"></script>' . PHP_EOL
                                                  . '<script src="'. WS_DIR_APP .'vendor/trumbowyg/plugins/table/trumbowyg.table.min.js"></script>';

    document::$snippets['javascript'][] = '  $(\'textarea[name="'. $name .'"]\').trumbowyg({' . PHP_EOL
                                        . '    btns: [["viewHTML"], ["formatting"], ["strong", "em", "underline", "del"], ["link"], ["insertImage"], ["table"], ["justifyLeft", "justifyCenter", "justifyRight"], ["lists"], ["foreColor", "backColor"], ["preformatted"], ["horizontalRule"], ["removeformat"], ["fullscreen"]],' . PHP_EOL
                                        . '    btnsDef: {' . PHP_EOL
                                        . '      lists: {' . PHP_EOL
                                        . '        dropdown: ["unorderedList", "orderedList"],' . PHP_EOL
                                        . '        title: "Lists",' . PHP_EOL
                                        . '        ico: "unorderedList",' . PHP_EOL
                                        . '      }' . PHP_EOL
                                        . '    },' . PHP_EOL
                                        . '    plugins: {' . PHP_EOL
                                        . '      upload: {' . PHP_EOL
                                        . '        serverPath: "'. WS_DIR_APP .'vendor/trumbowyg/plugins/upload/trumbowyg.upload.php",' . PHP_EOL
                                        . '      }' . PHP_EOL
                                        . '    },' . PHP_EOL
                                        . '    lang: "'. language::$selected['code'] .'",' . PHP_EOL
                                        . '    autogrowOnEnter: true,' . PHP_EOL
                                        . '    imageWidthModalEdit: true,' . PHP_EOL
                                        . '    removeformatPasted: true,' . PHP_EOL
                                        . '    semantic: false' . PHP_EOL
                                        . '  });';

    return '<textarea name="'. htmlspecialchars($name) .'" data-type="wysiwyg"'. (($parameters) ? ' '.$parameters : false) .'>'. htmlspecialchars($value) .'</textarea>';
  }

  ######################################################################

  function form_draw_function($function, $name, $input=true, $parameters='') {

    preg_match('#(\w*)(?:\()(.*?)(?:\))#i', $function, $matches);

    if (!isset($matches[1])) trigger_error('Invalid function name ('. $function .')', E_USER_ERROR);

    $options = [];
    if (isset($matches[2])) {
      $options = explode(',', $matches[2]);
      for ($i=0; $i<count($options); $i++) {
        $options[$i] = trim($options[$i], '\'" ');
      }
    }

    switch ($matches[1]) {

      case 'decimal':
      case 'float':
        return form_draw_decimal_field($name, $input, 2, $parameters);

      case 'number':
      case 'int':
        return form_draw_number_field($name, $input, $parameters);

      case 'color':
        return form_draw_color_field($name, $input, $parameters);

      case 'smallinput': // Deprecated
      case 'smalltext': // Deprecated
      case 'input': // Deprecated
      case 'text':
        return form_draw_text_field($name, $input, $parameters);

      case 'password':
        return form_draw_password_field($name, $input, $parameters);

      case 'mediumtext':
      case 'textarea':
        return form_draw_textarea($name, $input, $parameters . ' rows="5"');

      case 'bigtext':
        return form_draw_textarea($name, $input, $parameters . ' rows="10"');

      case 'category':
        return form_draw_categories_list($name, $input, $parameters);

      case 'categories':
        return form_draw_categories_list($name, $input, true, $parameters);

      case 'customer':
        return form_draw_customers_list($name, $input, false, $parameters);

      case 'customers':
        return form_draw_customers_list($name, $input, true, $parameters);

      case 'country':
        return form_draw_countries_list($name, $input, false, $parameters);

      case 'countries':
        return form_draw_countries_list($name, $input, true, $parameters);

      case 'currency':
        return form_draw_currencies_list($name, $input, false, $parameters);

      case 'currencies':
        return form_draw_currencies_list($name, $input, true, $parameters);

      case 'csv':
        return form_draw_textarea($name, $input, true, $parameters . ' data-type="csv"');

      case 'delivery_status':
        return form_draw_delivery_statuses_list($name, $input, false, $parameters);

      case 'delivery_statuses':
        return form_draw_delivery_statuses_list($name, $input, true, $parameters);

      case 'email':
        return functions::form_draw_email_field($name, $input, $parameters);

      case 'file':
        return functions::form_draw_file_field($name);

      case 'geo_zone':
        return form_draw_geo_zones_list($name, $input, false, $parameters);

      case 'geo_zones':
        return form_draw_geo_zones_list($name, $input, true, $parameters);

      case 'language':
        return form_draw_languages_list($name, $input, false, $parameters);

      case 'languages':
        return form_draw_languages_list($name, $input, true, $parameters);

      case 'length_class':
        return form_draw_length_classes_list($name, $input, false, $parameters);

      case 'length_classes':
        return form_draw_length_classes_list($name, $input, true, $parameters);

      case 'product':
        return form_draw_products_list($name, $input, false, $parameters);

      case 'products':
        return form_draw_products_list($name, $input, true, $parameters);

      case 'quantity_unit':
        return form_draw_quantity_units_list($name, $input, false, $parameters);

      case 'quantity_units':
        return form_draw_quantity_units_list($name, $input, true, $parameters);

      case 'stock_option':
        return form_draw_stock_options_list($name, $input, false, $parameters);

      case 'stock_options':
        return form_draw_stock_options_list($name, $input, true, $parameters);

      case 'order_status':
        return form_draw_order_status_list($name, $input, false, $parameters);

      case 'order_statuses':
        return form_draw_order_status_list($name, $input, true, $parameters);

      case 'regional_input': //Deprecated
      case 'regional_text':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_draw_regional_input_field($language_code, $name.'['. $language_code.']', $input, $parameters);
        }
        return $html;

      case 'regional_textarea':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_draw_regional_textarea($language_code, $name.'['. $language_code.']', $input, $parameters);
        }
        return $html;

      case 'regional_wysiwyg':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_draw_regional_wysiwyg_field($language_code, $name.'['. $language_code.']', $input, $parameters);
        }
        return $html;

      case 'page':
        return form_draw_pages_list($name, $input, false, $parameters);

      case 'pages':
        return form_draw_pages_list($name, $input, true, $parameters);

      case 'radio':
        $html = '';
        for ($i=0; $i<count($options); $i++) {
          $html .= '<div class="radio"><label>'. form_draw_radio_button($name, $options[$i], $input, $parameters) .' '. $options[$i] .'</label></div>';
        }
        return $html;

      case 'select':
        for ($i=0; $i<count($options); $i++) $options[$i] = [$options[$i]];
        return form_draw_select_field($name, $options, $input, $parameters);

      case 'select_multiple':
        for ($i=0; $i<count($options); $i++) $options[$i] = [$options[$i]];
        return form_draw_select_multiple_field($name, $options, $input, $parameters);

      case 'timezone':
        return form_draw_timezones_list($name, $input, false, $parameters);

      case 'timezones':
        return form_draw_timezones_list($name, $input, true, $parameters);

      case 'template':
        return form_draw_templates_list($name, $input, false, $parameters);

      case 'templates':
        return form_draw_templates_list($name, $input, true, $parameters);

      case 'time':
        return form_draw_time_field($name, $input, $parameters);

      case 'toggle':
        return form_draw_toggle($name, $input, !empty($options[0]) ? $options[0] : null);

      case 'sold_out_status':
        return form_draw_sold_out_statuses_list($name, $input, false, $parameters);

      case 'sold_out_statuses':
        return form_draw_sold_out_statuses_list($name, $input, true, $parameters);

      case 'tax_class':
        return form_draw_tax_classes_list($name, $input, false, $parameters);

      case 'tax_classes':
        return form_draw_tax_classes_list($name, $input, true, $parameters);

      case 'user':
        return form_draw_users_list($name, $input, false, $parameters);

      case 'users':
        return form_draw_users_list($name, $input, true, $parameters);

      case 'weight_class':
        return form_draw_weight_classes_list($name, $input, false, $parameters);

      case 'weight_classes':
        return form_draw_weight_classes_list($name, $input, true, $parameters);

      case 'wysiwyg':
        return form_draw_regional_wysiwyg_field($name, $input, $parameters);

      case 'zone':
        $option = !empty($options) ? $options[0] : '';
        //if (empty($option)) $option = settings::get('store_country_code');
        return form_draw_zones_list($option, $name, $input, false, $parameters);

      case 'zones':
        $option = !empty($options) ? $options[0] : '';
        //if (empty($option)) $option = settings::get('store_country_code');
        return form_draw_zones_list($option, $name, $input, true, $parameters);

      default:
        trigger_error('Unknown function name ('. $function .')', E_USER_WARNING);
        return form_draw_hidden_field($name, $input, $parameters);
        break;
    }
  }

  function form_draw_attribute_groups_list($name, $input=true, $multiple=false, $parameters='') {

    $query = database::query(
      "select ag.id, agi.name from ". DB_PREFIX ."attribute_groups ag
      left join ". DB_PREFIX ."attribute_groups_info agi on (agi.group_id = ag.id and agi.language_code = '". database::input(language::$selected['code']) ."')
      order by name;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($query)) {
      $options[] = [$row['name'], $row['id']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_attribute_values_list($group_id, $name, $input=true, $multiple=false, $parameters='') {

    $query = database::query(
      "select av.id, avi.name from ". DB_PREFIX ."attribute_values av
      left join ". DB_PREFIX ."attribute_groups_info avi on (avi.value_id = av.id and avi.language_code = '". database::input(languave::$selected['code']) ."')
      where group_id = ". (int)$group_id ."
      order by name;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($query)) {
      $options[] = [$row['name'], $row['id']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_categories_list($name, $input=true, $multiple=false, $parameters='') {

    if (!$multiple) {
      return form_draw_category_field($name, $options, $input, $parameters);
    }

    $html = '<div class="input-group" style="flex-direction: column;"' . (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL
          . '  <div class="form-control" style="overflow-y: auto; min-height: 200px;">' . PHP_EOL
          . '    ' . PHP_EOL
          . '  </div>' . PHP_EOL
          . '  <div class="dropdown">' . PHP_EOL
          . '  '. functions::form_draw_text_field('category_query', '', 'autocomplete="off" placeholder="'. htmlspecialchars(language::translate('text_search_category', 'Search category')) .' &hellip;"') . PHP_EOL
          . '    <ul class="dropdown-menu" style="right: 0;">' . PHP_EOL
          . '    </ul>' . PHP_EOL
          . '  </div>' . PHP_EOL
          . '</div>';

    $javascript =
<<<END
  $('input[name="category_query"]').on('focus', function(e){
    $(this).closest('.dropdown').addClass('open');
  });

  $('input[name="category_query"]').closest('dropdown').click('focus', function(e){
    e.stopPropagation();
  });

  $('body').click(function(e){
    $(this).closest('.dropdown').removeClass('open');
  });

  var xhr_category_search = null;
  $('input[name="category_query"]').on('input', function(e){

    var dropdown = $(this).closest('.dropdown');

    $(dropdown).find('.dropdown-menu').html('');

    if (xhr_category_search) xhr_category_search.abort();

    if ($(this).val() == '') {

      $.getJSON('%link&parent_id=0', function(result) {

        $(dropdown).find('.dropdown-menu').html('');

        $.each(result, function(i, category) {
          $(dropdown).find('.dropdown-menu').append(
            '<li class="list-item" data-id="'+ category.id +'" data-name="'+ category.name +'">' +
            '  <a href="%link&parent_id='+ category.id +'">' +
            '    <button class="btn btn-default btn-sm pull-right" type="button">%add</button>' +
            '    %folder_icon '+ category.name +
            '  </a>' +
            '</li>'
          );
        });

        $(dropdown).find('.dropdown-menu a').on('click', function(e){
          alert('x');
          e.preventDefault();

          $.getJSON($(this).attr('href'), function(result) {

            $(dropdown).find('.dropdown-menu').html('');

            $.each(result, function(i, category) {
              $(dropdown).find('.dropdown-menu').append(
                '<li class="list-item" data-id="'+ category.id +'" data-name="'+ category.name +'">' +
                '  <a>' +
                '    <button class="btn btn-default btn-sm pull-right" type="button">%add</button>' +
                '    %folder_icon '+ category.name +
                '  </a>' +
                '</li>'
              );
            });
          });
        });
      });

      return;
    }

    xhr_category_search = $.ajax({
      type: 'get',
      async: true,
      cache: true,
      url: '%link&query=' + $(this).val(),
      dataType: 'json',

      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
      },

      error: function(jqXHR, textStatus, errorThrown) {
        alert(errorThrown);
      },

      success: function(json) {

        if (!json) {
          $(dropdown).find('dropdown-menu').html('<li class="text-center no-results"><em>:(</em></li>');
        }

        $.each(json, function(i, result) {
          $(dropdown).find('.dropdown-menu').append(
            '<li class="list-item" data-id="'+ result.id +'" data-name="'+ result.name +'"><a>' +
            '  <button class="btn btn-default btn-sm pull-right" type="button">%add</button>' +
            '  %folder_icon '+ result.name +
            '</a></li>'
          );
        });
      },
    });
  });
END;

    document::$snippets['javascript'][] = strtr($javascript, [
      '%link' => document::link(WS_DIR_ADMIN, ['app' => 'catalog', 'doc' => 'categories.json']),
      '%add' => language::translate('title_add', 'Add'),
      '%folder_icon' => functions::draw_fonticon('folder'),
    ]);

    return $html;
  }

  function form_draw_countries_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_country_code');
    }

    $countries_query = database::query(
      "select * from ". DB_PREFIX ."countries
      where status
      order by name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($country = database::fetch($countries_query)) {
      $options[] = [$country['name'], $country['iso_code_2'], 'data-tax-id-format="'. $country['tax_id_format'] .'" data-postcode-format="'. $country['postcode_format'] .'" data-phone-code="'. $country['phone_code'] .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_currencies_list($name, $input=true, $multiple=false, $parameters='') {

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    foreach (currency::$currencies as $currency) {
      $options[] = [$currency['name'], $currency['code'], 'data-value="'. (float)$currency['value'] .'" data-decimals="'. (int)$currency['decimals'] .'" data-prefix="'. htmlspecialchars($currency['prefix']) .'" data-suffix="'. htmlspecialchars($currency['suffix']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_customers_list($name, $input=true, $multiple=false, $parameters='') {

    if (empty(user::$data['id'])) trigger_error('Must be logged in to use form_draw_customers_list()', E_USER_ERROR);

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    $customers_query = database::query(
      "select id, email, company, firstname, lastname from ". DB_PREFIX ."customers
      order by email;"
    );

    while ($customer = database::fetch($customers_query)) {
      $options[] = [$customer['email'], $customer['id']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_delivery_statuses_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_delivery_status_id');
    }

    $query = database::query(
      "select ds.id, dsi.name , dsi.description from ". DB_PREFIX ."delivery_statuses ds
      left join ". DB_PREFIX ."delivery_statuses_info dsi on (dsi.delivery_status_id = ds.id and dsi.language_code = '". database::input(language::$selected['code']) ."')
      order by dsi.name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($query)) {
      $options[] = [$row['name'], $row['id'], 'title="'. htmlspecialchars($row['description']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_encodings_list($name, $input=true, $multiple=false, $parameters='') {

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    $encodings = [
      'BIG-5',
      'CP50220',
      'CP50221',
      'CP50222',
      'CP51932',
      'CP850',
      'CP932',
      'EUC-CN',
      'EUC-JP',
      'EUC-KR',
      'EUC-TW',
      'GB18030',
      'ISO-8859-1',
      'ISO-8859-2',
      'ISO-8859-3',
      'ISO-8859-4',
      'ISO-8859-5',
      'ISO-8859-6',
      'ISO-8859-7',
      'ISO-8859-8',
      'ISO-8859-9',
      'ISO-8859-10',
      'ISO-8859-13',
      'ISO-8859-14',
      'ISO-8859-15',
      'ISO-8859-16',
      'KOI8-R',
      'KOI8-U',
      'SJIS',
      'UTF-8',
      'UTF-16',
      'Windows-1251',
      'Windows-1252',
      'Windows-1254',
    ];

    foreach ($encodings as $encoding) {
      $options[] = [$encoding];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_geo_zones_list($name, $input=true, $multiple=false, $parameters='') {

    $geo_zones_query = database::query(
      "select * from ". DB_PREFIX ."geo_zones
      order by name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    if (!database::num_rows($geo_zones_query)) {
      return form_draw_select_field($name, $options, $input, false, false, $parameters . ' disabled');
    }

    while ($geo_zone = database::fetch($geo_zones_query)) {
      $options[] = [$geo_zone['name'], $geo_zone['id']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_languages_list($name, $input=true, $multiple=false, $parameters='') {

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    foreach (language::$languages as $language) {
      $options[] = [$language['name'], $language['code']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_length_classes_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('store_length_class');
    }

    $options = [];

    if (empty($multiple)) $options[] = ['--', ''];

    foreach (length::$classes as $class) {
      $options[] = [$class['unit'], $class['unit'], 'data-value="'. (float)$class['value'] .'" data-decimals="'. (int)$class['decimals'] .'" title="'. htmlspecialchars($class['name']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_brands_list($name, $input=true, $multiple=false, $parameters='') {

    $brands_query = database::query(
      "select id, name from ". DB_PREFIX ."brands
      order by name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($brand = database::fetch($brands_query)) {
      $options[] = [$brand['name'], $brand['id']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_mysql_collations_list($name, $input=true, $multiple=false, $parameters='') {

    $collations_query = database::query(
      "SELECT * FROM `information_schema`.`COLLATIONS`
      order by COLLATION_NAME;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($collations_query)) {
      $options[] = [$row['COLLATION_NAME'], $row['COLLATION_NAME']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_mysql_engines_list($name, $input=true, $multiple=false, $parameters='') {

    $collations_query = database::query(
      "SHOW ENGINES;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($collations_query)) {
      if (!in_array(strtoupper($row['Support']), ['YES', 'DEFAULT'])) continue;
      if (!in_array($row['Engine'], ['CSV', 'InnoDB', 'MyISAM', 'Aria'])) continue;
      $options[] = [$row['Engine'] . ' -- '. $row['Comment'], $row['Engine']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_order_status_list($name, $input=true, $multiple=false, $parameters='') {

    $query = database::query(
      "select os.id, osi.name from ". DB_PREFIX ."order_statuses os
      left join ". DB_PREFIX ."order_statuses_info osi on (osi.order_status_id = os.id and osi.language_code = '". database::input(language::$selected['code']) ."')
      order by priority, name;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($query)) {
      $options[] = [$row['name'], $row['id']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_pages_list($name, $input=true, $multiple=false, $parameters='') {

    $iterator = function($parent_id=0, $level=1, &$_this) {

      $options = [];

      if ($parent_id == 0) $options[] = ['['.language::translate('title_root', 'Root').']', '0'];

      $pages_query = database::query(
        "select p.id, pi.title from ". DB_PREFIX ."pages p
        left join ". DB_PREFIX ."pages_info pi on (pi.page_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        where p.parent_id = '". (int)$parent_id ."'
        order by p.priority asc, pi.title asc;"
      );

      while ($page = database::fetch($pages_query)) {

        $options[] = [str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $page['title'], $page['id']];

        $sub_pages_query = database::query(
          "select id from ". DB_PREFIX ."pages
          where parent_id = '". (int)$page['id'] ."'
          limit 1;"
        );

        $sub_options = $_this($page['id'], $level+1, $_this);

        $options = array_merge($options, $sub_options);
      }

      return $options;
    };

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    $options = array_merge($options, $iterator(0, 1, $iterator));

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_payment_modules_list($name, $input=true, $multiple=true, $parameters='') {

    $modules_query = database::query(
      "select * from ". DB_PREFIX ."modules
      where type = 'payment'
      and status;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($module = database::fetch($modules_query)) {
      $module = new $module();
      $options[] = [$module->name, $module->id];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_products_list($name, $input=true, $multiple=false, $parameters='') {

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    $products_query = database::query(
      "select p.*, pi.name from ". DB_PREFIX ."products p
      left join ". DB_PREFIX ."products_info pi on (p.id = pi.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      order by pi.name"
    );

    while ($product = database::fetch($products_query)) {
      $options[] = [$product['name'] .' &mdash; '. $product['sku'] . ' ['. (float)$product['quantity'] .']', $product['id']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_quantity_units_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_quantity_unit_id');
    }

    $quantity_units_query = database::query(
      "select qu.*, qui.name, qui.description from ". DB_PREFIX ."quantity_units qu
      left join ". DB_PREFIX ."quantity_units_info qui on (qui.quantity_unit_id = qu.id and language_code = '". language::$selected['code'] ."')
      order by qu.priority, qui.name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($quantity_unit = database::fetch($quantity_units_query)) {
      $options[] = [$quantity_unit['name'], $quantity_unit['id'], 'data-separate="'. (!empty($quantity_unit['separate']) ? 'true' : 'false') .'" data-decimals="'. (int)$quantity_unit['decimals'] .'" title="'. htmlspecialchars($quantity_unit['description']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_shipping_modules_list($name, $input=true, $multiple=true, $parameters='') {

    $modules_query = database::query(
      "select * from ". DB_PREFIX ."modules
      where type = 'shipping'
      and status;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($module = database::fetch($modules_query)) {
      $module = new $module();
      $options[] = [$module->name, $module->id];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_sold_out_statuses_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_sold_out_status_id');
    }

    $query = database::query(
      "select sos.id, sosi.name, sosi.description from ". DB_PREFIX ."sold_out_statuses sos
      left join ". DB_PREFIX ."sold_out_statuses_info sosi on (sosi.sold_out_status_id = sos.id and sosi.language_code = '". database::input(language::$selected['code']) ."')
      order by sosi.name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($query)) {
      $options[] = [$row['name'], $row['id'], 'title="'. htmlspecialchars($row['description']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_stock_options_list($name, $input=true, $multiple=false, $parameters='') {

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    $products_query = database::query(
      "select p.id, p.sku, pi.name from ". DB_TABLE_PRODUCTS ." p
      left join ". DB_TABLE_PRODUCTS_INFO ." pi on (p.id = pi.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      order by name;"
    );

    while ($product = database::fetch($products_query)) {

      $products_stock_option_query = database::query(
        "select pos.id, pos.product_id, pos.combination, pos.sku, pi.name from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ." pos
        left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pos.product_id = pi.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
        where pos.product_id = ". (int)$product['id'] ."
        and pos.combination != ''
        order by pi.name;"
      );

      if (database::num_rows($products_stock_option_query)) {
        while ($stock_option = database::fetch($products_stock_option_query)) {

          $option_names = '';
          if (!empty($stock_option['combination'])) {
            foreach(explode(',', $stock_option['combination']) as $combination) {
              list($group_id, $value_id) = explode('-', $combination);

              $option_groups_query = database::query(
                "select pcg.id, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
                left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". database::input(language::$selected['code']) ."')
                where pcg.id = ". (int)$group_id .";"
              );
              $option_group = database::fetch($option_groups_query);

              $option_values_query = database::query(
                "select pcv.id, pcvi.name from ". DB_TABLE_OPTION_VALUES ." pcv
                left join ". DB_TABLE_OPTION_VALUES_INFO ." pcvi on (pcvi.value_id = pcv.id and pcvi.language_code = '". database::input(language::$selected['code']) ."')
                where pcv.group_id = ". (int)$group_id ."
                and pcv.id = ". (int)$value_id .";"
              );
              $option_value = database::fetch($option_values_query);

              $option_names = $option_names . ($option_names  ? ' + ' : '') . $option_group['name'] .': '. $option_value['name'];
            }
          }

          $options[] = [$stock_option['name'] . (!empty($option_names) ? ': ' . $option_names : '') .' &mdash; '. ($stock_option['sku'] ? $stock_option['sku'] : 'n/a'), $stock_option['id'], 'data-product-id="'. $stock_option['product_id'] .'" data-name="'. htmlspecialchars($stock_option['name'] . (!empty($option_names) ? ': ' . $option_names : '')) .'" data-combination="'. $stock_option['combination'] .'" data-sku="'. htmlspecialchars($stock_option['sku']) .'"'];
        }
      } else {
        $options[] = [$product['name'] .' &mdash; '. ($product['sku'] ? $product['sku'] : 'n/a'), $product['id'], 'data-product-id="'. $product['id'] .'" data-name="'. htmlspecialchars($product['name']) .'" data-combination="" data-sku="'. htmlspecialchars($product['sku']) .'"'];
      }
    }

    if ($multiple) {
      return functions::form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return functions::form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_suppliers_list($name, $input=true, $multiple=false, $parameters='') {

    $suppliers_query = database::query(
      "select id, name, description from ". DB_PREFIX ."suppliers
      order by name;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($supplier = database::fetch($suppliers_query)) {
      $options[] = [$supplier['name'], $supplier['id'], 'title="'. htmlspecialchars($supplier['description']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_tax_classes_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_tax_class_id');
    }

    $tax_classes_query = database::query(
      "select * from ". DB_PREFIX ."tax_classes
      order by name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($tax_class = database::fetch($tax_classes_query)) {
      $options[] = [$tax_class['name'], $tax_class['id'], 'title="'. htmlspecialchars($tax_class['description']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_templates_list($name, $input=true, $multiple=false, $parameters='') {

    $folders = glob(FS_DIR_APP . 'frontend/templates/*', GLOB_ONLYDIR);

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    foreach ($folders as $folder) {
      $options[] = [basename($folder)];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_timezones_list($name, $input=true, $multiple=false, $parameters='') {

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    $zones = timezone_identifiers_list();

    foreach ($zones as $zone) {
      $zone = explode('/', $zone); // 0 => Continent, 1 => City

      if (in_array($zone[0], ['Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'])) {
        if (!empty($zone[1])) {
          $options[] = [implode('/', $zone)];
        }
      }
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_users_list($name, $input=true, $multiple=false, $parameters='') {

    $users_query = database::query(
      "select id, username from ". DB_PREFIX ."users
      order by username;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($user = database::fetch($users_query)) {
      $options[] = [$user['username'], $user['id']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_weight_classes_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('store_weight_class');
    }

    $options = [];

    if (empty($multiple)) $options[] = ['--', ''];

    foreach (weight::$classes as $class) {
      $options[] = [$class['unit'], $class['unit'], 'data-value="'. (float)$class['value'] .'" data-decimals="'. (int)$class['decimals'] .'" title="'. htmlspecialchars($class['name']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_zones_list($country_code, $name, $input=true, $multiple=false, $parameters='', $preamble='none') {

    if (empty($country_code)) $country_code = settings::get('default_country_code');

    if ($country_code == 'default_country_code') $country_code = settings::get('default_country_code');

    if ($country_code == 'store_country_code') $country_code = settings::get('store_country_code');

    $zones_query = database::query(
      "select * from ". DB_PREFIX ."zones
      where country_code = '". database::input($country_code) ."'
      order by name asc;"
    );

    $options = [];

    switch($preamble) {
      case 'all':
        $options[] = ['-- '. language::translate('title_all_zones', 'All Zones') . ' --', ''];
        break;
      case 'select':
        $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];
        break;
      case 'none':
        break;
    }

    if (!database::num_rows($zones_query)) {
      $parameters .= ' disabled';
    }

    while ($zone = database::fetch($zones_query)) {
      $options[] = [$zone['name'], $zone['code']];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }
