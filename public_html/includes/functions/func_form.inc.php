<?php

  function form_draw_form_begin($name='', $method='post', $action=false, $multipart=false, $parameters='') {
    return  '<form'. (($name) ? ' name="'. htmlspecialchars($name) .'"' : '') .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'" accept-charset="'. language::$selected['charset'] .'"'. (($action) ? ' action="'. htmlspecialchars($action) .'"' : '') . (($parameters) ? ' ' . $parameters : '') .'>';
  }

  function form_draw_form_end() {
    return '</form>' . PHP_EOL;
  }

  function form_reinsert_value($name, $array_value=null) {
    if (empty($name)) return;

    foreach ([$_POST, $_GET] as $superglobal) {
      if (empty($superglobal)) continue;

    // Extract name parts
      $parts = preg_split('#[\]\[]+#', preg_replace('#\[\]$#', '', $name), -1, PREG_SPLIT_NO_EMPTY);

    // Get array node
      $node = $superglobal;
      foreach ($parts as $part) {
        if (!isset($node[$part])) continue 2;
        $node = $node[$part];
      }

      if ($array_value !== null) {

      // Attempt reinserting a numerical indexed array value
        if (preg_match('#\[\]$#', $name)) {
          if (!is_array($node) || !in_array($array_value, $node)) continue;
          return $array_value;

      // Reinsert a defined key array value
        } else {
          if ($array_value != $node) continue;
          return $array_value;
        }

    // Reinsert node value
      } else {
        return $node;
      }
    }
  }

  function form_draw_button($name, $value, $type='submit', $parameters='', $fonticon='') {

    if (!is_array($value)) {
      $value = [$value, $value];
    }

    return '<button'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="btn btn-default"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value[0]) .'"'. (($parameters) ? ' '.$parameters : '') .'>'. ((!empty($fonticon)) ? functions::draw_fonticon($fonticon) . ' ' : '') . (isset($value[1]) ? $value[1] : $value[0]) .'</button>';
  }

  function form_draw_captcha_field($name, $id, $parameters='') {

   return '<div class="input-group">' . PHP_EOL
        . '  <span class="input-group-text" style="padding: 0;">'. functions::captcha_generate(100, 40, 4, $id, 'numbers', 'align="absbottom"') .'</span>' . PHP_EOL
        . '  ' . form_draw_text_field('captcha', '', $parameters . ' autocomplete="off" style="font-size: 24px; padding: 0; text-align: center;"') . PHP_EOL
        . '</div>';
  }

  function form_draw_category_field($name, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    $category_name = language::translate('title_root', 'Root');

    if (!empty($input)) {
      $category_name = reference::category($input)->name;
    }

    functions::draw_lightbox();

    return '<div class="input-group"'. (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL
         . '  <div class="form-input">' . PHP_EOL
         . '    ' . form_draw_hidden_field($name, true) . PHP_EOL
         . '    '. functions::draw_fonticon('folder') .' <span class="name" style="display: inline-block;">'. $category_name .'</span>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '  <div style="align-self: center;">' . PHP_EOL
         . '    <a href="'. document::href_ilink('b:catalog/category_picker', ['parent_id' => $input]) .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. language::translate('title_change', 'Change') .'</a>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '</div>';
  }

  function form_draw_checkbox($name, $value, $input=true, $parameters='') {

    if (!is_array($value)) {
      if ($input === true) $input = form_reinsert_value($name, $value);
      return '<span class="form-check"><input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-check"' : '') .' type="checkbox" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (!strcmp($input, $value) ? ' checked' : '') . (($parameters) ? ' ' . $parameters : '') .' /></span>';
    }

    if ($input === true) $input = form_reinsert_value($name, $value[0]);

    return '<label'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-check"' : '') .'>' . PHP_EOL
         . '  <input type="checkbox" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value[0]) .'" '. (!strcmp($input, $value[0]) ? ' checked' : '') . (($parameters) ? ' ' . $parameters : '') .' />' . PHP_EOL
         . '  ' . (isset($value[1]) ? $value[1] : $value[0]) . PHP_EOL
         . '</label>';
  }

  function form_draw_color_field($name, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="color" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_csv_field($name, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    if (!$csv = functions::csv_decode($input)) {
      return form_draw_textarea($name, $input, $parameters);
    }

    $columns = array_keys($csv[0]);

    $html = '<table class="table table-striped table-hover data-table" data-toggle="csv">' . PHP_EOL
          . '  <thead>' . PHP_EOL
          . '    <tr>' . PHP_EOL;

    foreach ($columns as $column) {
      $html .= '      <th>'. $column .'</th>' . PHP_EOL;
    }

    $html .= '      <th><a class="add-column" href="#">'. functions::draw_fonticon('fa-plus', 'style="color: #6c6;"') .'</a></th>' . PHP_EOL
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
           . '      <td colspan="'. (count($columns)+1) .'"><a class="add-row" href="#">'. functions::draw_fonticon('fa-plus', 'style="color: #6c6;"') .'</a></td>' . PHP_EOL
           . '    </tr>' . PHP_EOL
           . '  </tfoot>' . PHP_EOL
           . '</table>' . PHP_EOL
           . PHP_EOL
           . form_draw_textarea($name, $input, 'style="display: none;"');

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

  function form_draw_currency_field($name, $currency_code=null, $input=true, $parameters='') {

    if (preg_match('#^[A-Z]{3}$#', $name)) {
      trigger_error('Passing currency code as 1st parameter in form_draw_currency_field() is deprecated. Instead, use form_draw_currency_field($name, $currency_code, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $currency_code) = [$currency_code, $name];
    }

    if ($currency_code == '') $currency_code = settings::get('site_currency_code');
    if ($input === true) $input = form_reinsert_value($name);

  // Format and show an additional two decimals precision if needed
    if ($input != '') {
      $input = number_format((float)$input, currency::$currencies[$currency_code]['decimals'] + 2, '.', '');
      $input = preg_replace('#(\.'. str_repeat('\d', 2) .')0{1,2}$#', '$1', $input);
      $input = rtrim($input, '.');
    }

    if (empty($currency_code)) $currency_code = settings::get('site_currency_code');

    return '<div class="input-group">' . PHP_EOL
         . '  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="number" step="any" name="'. htmlspecialchars($name) .'" value="'. (($input != 0) ? $input : '') .'"'. (($parameters) ? ' '. $parameters : '') .' />' . PHP_EOL
         . '  <strong class="input-group-text" style="opacity: 0.75; font-family: monospace;">'. $currency_code .'</strong>' . PHP_EOL
         . '</div>';
  }

  function form_draw_customer_field($name, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    $account_name = language::translate('title_guest', 'Guest');

    if (!empty($input)) {
      $customer_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."customers
        where id = ". (int)$input ."
        limit 1;"
      );

      if ($customer = database::fetch($customer_query)) {
        $account_name = $customer['company'] ? $customer['company'] : $customer['firstname'] .' '. $customer['lastname'];
      } else {
        $account_name = '<em>'. language::translate('title_unknown', 'Unknown') .'</em>';
      }
    }

    return '<div class="form-input"'. (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL
         . '  ' . form_draw_hidden_field($name, true) . PHP_EOL
         . '  '. language::translate('title_id', 'ID') .': <span class="id">'. (int)$input .'</span> &ndash; <span class="name">'. $account_name .'</span> <a href="'. document::href_ilink('b:customers/customer_picker') .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;">'. language::translate('title_change', 'Change') .'</a>' . PHP_EOL
         . '</div>';
  }

  function form_draw_date_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    if (!in_array(substr($input, 0, 10), ['', '0000-00-00', '1970-01-01'])) {
      $input = date('Y-m-d', strtotime($input));
    } else {
      $input = '';
    }

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="date" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'" placeholder="YYYY-MM-DD"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_datetime_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    if (!in_array(substr($input, 0, 10), ['', '0000-00-00', '1970-01-01'])) {
      $input = date('Y-m-d\TH:i', strtotime($input));
    } else {
      $input = '';
    }

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="datetime-local" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'" placeholder="YYYY-MM-DD [hh:nn]"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_decimal_field($name, $input=true, $decimals=2, $parameters='') {

    if (count($args = func_get_args()) > 4) {
      trigger_error('Passing min and max separate parameters in form_draw_decimal_field() is deprecated. Instead define min="0" max="999" in $parameters', E_USER_DEPRECATED);
      if (isset($args[5])) $parameters = $args[5];
      if (isset($args[3])) $parameters .= ($parameters ? ' ' : '') . 'min="'. (int)$args[3] .'"';
      if (isset($args[4])) $parameters .= ($parameters ? ' ' : '') . 'min="'. (int)$args[4] .'"';
    }

    if ($input === true) $input = form_reinsert_value($name);

    if ($input != '') {
      $input = round($input, (int)$decimals);
    }

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="number" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'" '. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_dropdown_field($name, $options=[], $input=true, $parameters='') {

    $html = '<div class="dropdown"'. (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL
          . '  <div class="form-select" data-toggle="dropdown">-- '. language::translate('title_select', 'Select') .' --</div>' . PHP_EOL
          . '  <ul class="dropdown-menu">' . PHP_EOL;

    $is_numerical_index = (array_keys($options) === range(0, count($options) - 1));

    foreach ($options as $key => $option) {

      if (!is_array($option)) {
        if ($is_numerical_index) {
          $option = [$option, $option];
        } else {
          $option = [$key, $option];
        }
      }

      if (preg_match('#\[\]$#', $name)) {
        $html .= '<li class="option">' . functions::form_draw_checkbox($name, $option, $input, isset($option[2]) ? $option[2] : '') .'</li>' . PHP_EOL;
      } else {
        $html .= '<li class="option">' . functions::form_draw_radio_button($name, $option, $input, isset($option[2]) ? $option[2] : '') .'</li>' . PHP_EOL;
      }
    }

    $html .= '  </ul>' . PHP_EOL
           . '</div>';

    return $html;
  }

  function form_draw_email_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-envelope-o fa-fw') .'</span>' . PHP_EOL
         . '  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="email" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />'
         . '</div>';
  }

  function form_draw_file_field($name, $parameters='') {

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="file" name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_fonticon_field($name, $input=true, $type='text', $icon='', $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon($icon) .'</span>' . PHP_EOL
         . '  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />' . PHP_EOL
         . '</div>';
  }

  function form_draw_hidden_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<input type="hidden" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_image($name, $src, $parameters='') {
    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="image" name="'. htmlspecialchars($name) .'" src="'. htmlspecialchars($src) .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_input($name, $input=true, $type='text', $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_link_button($url, $title, $parameters='', $fonticon='') {
    return '<a '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default"' : '') .' href="'. htmlspecialchars($url) .'"'. (($parameters) ? ' '.$parameters : '') .'>'. (!empty($fonticon) ? functions::draw_fonticon($fonticon) . ' ' : '') . $title .'</a>';
  }

  function form_draw_month_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    if (!in_array(substr($input, 0, 7), ['', '0000-00', '1970-00', '1970-01'])) {
      $input = date('Y-m', strtotime($input));
    } else {
      $input = '';
    }

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="month" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'" maxlength="7" pattern="[0-9]{4}-[0-9]{2}" placeholder="YYYY-MM"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_number_field($name, $input=true, $parameters='') {
    if ($input === true) $input = (int)form_reinsert_value($name);

    if ($input != '') {
      $input = round($input);
    }

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="number" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'" step="1"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_password_field($name, $input='', $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-key fa-fw') .'</span>' . PHP_EOL
         . '  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="password" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />'
         . '</div>';
  }

  function form_draw_phone_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-phone fa-fw') .'</span>' . PHP_EOL
         . '  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="tel" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'" pattern="^\+?([0-9]|-| )+$"'. (($parameters) ? ' '.$parameters : '') .' />'
         . '</div>';
  }

  function form_draw_product_field($name, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    $product_name = '('. language::translate('title_no_product', 'No Product') .')';

    if (!empty($input)) {
      $product_query = database::query(
        "select p.id, pi.name from ". DB_TABLE_PREFIX ."products p
        left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        where p.id = ". (int)$input ."
        limit 1;"
      );

      if ($product = database::fetch($product_query)) {
        $product_name = $product['name'];
      }
    }

    functions::draw_lightbox();

    return '<div class="input-group"'. (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL
         . '  <div class="form-input">' . PHP_EOL
         . '    ' . form_draw_hidden_field($name, true) . PHP_EOL
         . '    <span class="name" style="display: inline-block;">'. $product_name .'</span>' . PHP_EOL
         . '    [<span class="id" style="display: inline-block;">'. (int)$input .'</span>]' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '  <div style="align-self: center;">' . PHP_EOL
         . '    <a href="'. document::href_ilink('b:catalog/product_picker') .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. language::translate('title_change', 'Change') .'</a>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '</div>';
  }

  function form_draw_radio_button($name, $value, $input=true, $parameters='') {

    if (!is_array($value)) {
	    if ($input === true) $input = form_reinsert_value($name, $value);
      return '<span class="form-check"><input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-radio"' : '') .' type="radio" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (!strcmp($input, $value) ? ' checked' : '') . (($parameters) ? ' ' . $parameters : '') .' /></span>';
    }

    if ($input === true) $input = form_reinsert_value($name, $value[0]);

    return '<label'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-check"' : '') .'>' . PHP_EOL
         . '  <input type="radio" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value[0]) .'" '. (!strcmp($input, $value[0]) ? ' checked' : '') . (($parameters) ? ' ' . $parameters : '') .' />' . PHP_EOL
         . '  ' . (isset($value[1]) ? $value[1] : $value[0]) . PHP_EOL
         . '</label>';
  }

  function form_draw_range_slider($name, $input=true, $min='', $max='', $step='', $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-range"' : '') .' type="range" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'" min="'. (float)$min .'" max="'. (float)$max .'" step="'. (float)$step .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_regional_input_field($name, $language_code='', $input=true, $type='text', $parameters='') {

    if (preg_match('#^[a-z]{2}$#', $name)) {
      trigger_error('Passing $language code as 1st parameter in form_draw_regional_input_field() is deprecated. Instead, use form_draw_regional_input_field($name, $language_code, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $language_code) = [$language_code, $name];
    }

    if (empty($language_code)) $language_code = settings::get('site_language_code');

    if ($input === true) $input = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text"><img src="'. document::href_link(WS_DIR_APP . 'assets/languages/'. $language_code .'.png') .'" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  <input class="form-input" name="'. htmlspecialchars($name) .'" type="'. htmlspecialchars($type) .'" value="'. htmlspecialchars($input) .'" />' . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_text_field($name, $language_code='', $input=true, $parameters='') {

    if (empty($language_code)) $language_code = settings::get('site_language_code');

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text"><img src="'. document::href_link(WS_DIR_APP . 'assets/languages/'. $language_code .'.png') .'" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_text_field($name, $input, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_textarea($name, $language_code='', $input=true, $parameters='') {

    if (preg_match('#^[a-z]{2}$#', $name)) {
      trigger_error('Passing language code as 1st parameter in form_draw_regional_textarea() is deprecated. Instead, use form_draw_regional_textarea($name, $language_code, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $language_code) = [$language_code, $name];
    }

    if (empty($language_code)) $language_code = settings::get('site_language_code');

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text" style="vertical-align: top;"><img src="'. document::href_link(WS_DIR_APP . 'assets/languages/'. $language_code .'.png') .'" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_textarea($name, $input, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_wysiwyg_field($name, $language_code='', $input=true, $parameters='') {

    if (preg_match('#^[a-z]{2}$#', $name)) {
      trigger_error('Passing language code as 1st parameter in form_draw_regional_wysiwyg_field() is deprecated. Instead, use form_draw_regional_wysiwyg_field($name, $language_code, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $language_code) = [$language_code, $name];
    }

    if (empty($language_code)) $language_code = settings::get('site_language_code');

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text"><img src="'. document::href_link(WS_DIR_APP . 'assets/languages/'. $language_code .'.png') .'" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_wysiwyg_field($name, $input, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_search_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-search fa-fw') .'</span>' . PHP_EOL
         . '  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="search" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />' . PHP_EOL
         . '</div>';
  }

  function form_draw_select_field($name, $options=[], $input=true, $parameters='') {

    if (preg_match('#\[\]$#', $name)) return form_draw_select_multiple_field($name, $options, $input, $parameters);

    $html = '<select '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-select"' : '') .' name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL;

    $is_numerical_index = (array_keys($options) === range(0, count($options) - 1));

    foreach ($options as $key => $option) {

      if (!is_array($option)) {
        if ($is_numerical_index) {
          $option = [$option];
        } else {
          $option = [$key, $option];
        }
      }

      if ($input === true) {
        $option_input = form_reinsert_value($name, $option[0]);
      } else {
        $option_input = $input;
      }

      $html .= '  <option value="'. htmlspecialchars($option[0]) .'"'. (!strcmp($option[0], $option_input) ? ' selected="selected"' : '') . ((isset($option[2])) ? ' ' . $option[2] : '') . '>'. (isset($option[1]) ? $option[1] : $option[0]) .'</option>' . PHP_EOL;
    }

    $html .= '</select>';

    return $html;
  }

  function form_draw_select_multiple_field($name, $options=[], $input=true, $parameters='') {

    $html = '<div class="form-input"' . (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL;

    $is_numerical_index = (array_keys($options) === range(0, count($options) - 1));

    foreach ($options as $key => $option) {

      if (!is_array($option)) {
        if ($is_numerical_index) {
          $option = [$option, $option];
        } else {
          $option = [$key, $option];
        }
      }

      $html .= form_draw_checkbox($name, $option, $input, isset($option[2]) ? $option[2] : '');
    }

    $html .= '</div>';

    return $html;
  }

  function form_draw_select_optgroup_field($name, $groups=[], $input=true, $parameters='') {

    if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
      trigger_error('Passing $multiple as 4th parameter in form_draw_select_optgroup_field() is deprecated as determined by input name instead.', E_USER_DEPRECATED);
      if (isset($args[4])) $parameters = $args[3];
    }

    if (!is_array($groups)) $groups = [$groups];

    $html = '<select class="form-select" name="'. htmlspecialchars($name) .'"'. (preg_match('#\[\]$#', $name) ? ' multiple' : '') . (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL;

    foreach ($groups as $group) {
      $html .= '    <optgroup label="'. $group['label'] .'">' . PHP_EOL;

      $is_numerical_index = (array_keys($group['options']) === range(0, count($group['options']) - 1));

      foreach ($group['options'] as $key => $option) {

        if (!is_array($option)) {
          if ($is_numerical_index) {
            $option = [$option, $option];
          } else {
            $option = [$key, $option];
          }
        }

        if ($input === true) {
          $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
        } else {
          $option_input = $input;
        }

        $html .= '      <option value="'. htmlspecialchars(isset($option[1]) ? $option[1] : $option[0]) .'"'. (isset($option[1]) ? (($option[1] == $option_input) ? ' selected="selected"' : '') : (($option[0] == $option_input) ? ' selected="selected"' : '')) . ((isset($option[2])) ? ' ' . $option[2] : '') . '>'. $option[0] .'</option>' . PHP_EOL;
      }

      $html .= '    </optgroup>' . PHP_EOL;
    }

    $html .= '  </select>';

    return $html;
  }

  function form_draw_switch($name, $value, $label, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<label><input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-switch"' : '') .' name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' '.$parameters : '') .'>'. htmlspecialchars($label) .'</label>';
  }

  function form_draw_textarea($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<textarea'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' '.$parameters : '') .'>'. htmlspecialchars($input) .'</textarea>';
  }

  function form_draw_text_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="text" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_time_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="time" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_toggle($name, $type='t/f', $input=true, $parameters='') {

    if (strpos($input, '/') === true) {
      trigger_error('Passing type as 3rd parameter in form_draw_toggle() is deprecated. Use instead form_draw_toggle($name, $type, $input, $parameters)', E_USER_DEPRECATED);
      list($type, $input) = [$input, $type];
    }

    if ($input === true) $input = form_reinsert_value($name);
    $input = preg_match('#^(1|active|enabled|on|true|yes)$#i', $input) ? '1' : '0';

    switch ($type) {
      case 'a/i':
        $options = [
          '1' => language::translate('title_active', 'Active'),
          '0' => language::translate('title_inactive', 'Inactive'),
        ];
        break;

      case 'e/d':
        $options = [
          '1' => language::translate('title_enabled', 'Enabled'),
          '0' => language::translate('title_disabled', 'Disabled'),
        ];
        break;

      case 'y/n':
        $options = [
          '1' => language::translate('title_yes', 'Yes'),
          '0' => language::translate('title_no', 'No'),
        ];
        break;

      case 'o/o':
        $options = [
          '1' => language::translate('title_on', 'On'),
          '0' => language::translate('title_off', 'Off'),
        ];
        break;

      case 't/f':
      default:
        $options = [
          '1' => language::translate('title_true', 'True'),
          '0' => language::translate('title_false', 'False'),
        ];
        break;
    }

    return form_draw_toggle_buttons($name, $options, $input, $parameters);
  }

  function form_draw_toggle_buttons($name, $options, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    $html = '<div class="btn-group btn-block btn-group-inline" data-toggle="buttons">'. PHP_EOL;

    $is_numerical_index = (array_keys($options) === range(0, count($options) - 1));

    foreach ($options as $key => $option) {

      if (!is_array($option)) {
        if ($is_numerical_index) {
          $option = [$option, $option];
        } else {
          $option = [$key, $option];
        }
      }

      $html .= '  <label class="btn btn-default'. ($input == $option[0] ? ' active' : '') .'">' . PHP_EOL
             . '    <input type="radio" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($option[0]) .'"'. (!strcmp($input, $option[0]) ? ' checked' : '') . (!empty($option[2]) ? ' '. $option[2] : '') .' />'. $option[1]
             . '  </label>'. PHP_EOL;
    }

    $html .= '</div>';

    return $html;
  }

  function form_draw_url_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="url" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />';
  }

  function form_draw_username_field($name, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-user fa-fw') .'</span>' . PHP_EOL
         . '  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="text" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($input) .'"'. (($parameters) ? ' '.$parameters : '') .' />'
         . '</div>';
  }

  function form_draw_wysiwyg_field($name, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    document::$snippets['head_tags']['trumbowyg'] = '<link href="'. document::href_rlink(FS_DIR_APP .'assets/trumbowyg/ui/trumbowyg.min.css') .'" rel="stylesheet" />' . PHP_EOL
                                                  . '<link href="'. document::href_rlink(FS_DIR_APP .'assets/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css') .'" rel="stylesheet" />'
                                                  . '<link href="'. document::href_rlink(FS_DIR_APP .'assets/trumbowyg/plugins/table/ui/trumbowyg.table.min.css') .'" rel="stylesheet" />';

    document::$snippets['foot_tags']['trumbowyg'] = '<script src="'. document::href_rlink(FS_DIR_APP . 'assets/trumbowyg/trumbowyg.min.js') .'"></script>' . PHP_EOL
                                                  . ((language::$selected['code'] != 'en') ? '<script src="'. document::href_rlink(FS_DIR_APP . 'assets/trumbowyg/langs/'. language::$selected['code'] .'.min.js') .'"></script>' . PHP_EOL : '')
                                                  . '<script src="'. document::href_rlink(FS_DIR_APP . 'assets/trumbowyg/plugins/colors/trumbowyg.colors.min.js') .'"></script>' . PHP_EOL
                                                  . '<script src="'. document::href_rlink(FS_DIR_APP . 'assets/trumbowyg/plugins/upload/trumbowyg.upload.min.js') .'"></script>' . PHP_EOL
                                                  . '<script src="'. document::href_rlink(FS_DIR_APP . 'assets/trumbowyg/plugins/table/trumbowyg.table.min.js') .'"></script>';

    document::$snippets['javascript'][] = '  $(\'textarea[name="'. $name .'"]\').trumbowyg({' . PHP_EOL
                                        . '    btns: [["viewHTML"], ["formatting"], ["strong", "em", "underline", "del"], ["foreColor", "backColor"], ["link"], ["insertImage"], ["table"], ["justifyLeft", "justifyCenter", "justifyRight"], ["lists"], ["preformatted"], ["horizontalRule"], ["removeformat"], ["fullscreen"]],' . PHP_EOL
                                        . '    btnsDef: {' . PHP_EOL
                                        . '      lists: {' . PHP_EOL
                                        . '        dropdown: ["unorderedList", "orderedList"],' . PHP_EOL
                                        . '        title: "Lists",' . PHP_EOL
                                        . '        ico: "unorderedList",' . PHP_EOL
                                        . '      }' . PHP_EOL
                                        . '    },' . PHP_EOL
                                        . '    plugins: {' . PHP_EOL
                                        . '      upload: {' . PHP_EOL
                                        . '        serverPath: "'. document::href_rlink(FS_DIR_APP . 'assets/trumbowyg/plugins/upload/trumbowyg.upload.php') .'",' . PHP_EOL
                                        . '      }' . PHP_EOL
                                        . '    },' . PHP_EOL
                                        . '    lang: "'. language::$selected['code'] .'",' . PHP_EOL
                                        . '    autogrowOnEnter: true,' . PHP_EOL
                                        . '    imageWidthModalEdit: true,' . PHP_EOL
                                        . '    removeformatPasted: true,' . PHP_EOL
                                        . '    semantic: false' . PHP_EOL
                                        . '  });';

    return '<textarea name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' '.$parameters : '') .'>'. htmlspecialchars($input) .'</textarea>';
  }

  ######################################################################

  function form_draw_function($name, $function, $input=true, $parameters='') {

    if (preg_match('#\)$#', $name)) {
      trigger_error('Passing function as 1st parameter in form_draw_function() is deprecated. Instead, use form_draw_function($name, $function, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $function) = [$function, $name];
    }

    if (!preg_match('#(\w*)\((.*?)\)$#i', $function, $matches)) {
      trigger_error('Invalid function name ('. $function .')', E_USER_ERROR);
    }

    $options = [];
    if (!empty($matches[2])) {
      $options = preg_split('#\s*,\s*#', $matches[2], -1, PREG_SPLIT_NO_EMPTY);
      $options = array_map(function($s){ return trim($s, '\'" '); }, $options);
    }

    switch ($matches[1]) {

      case 'date':
        return form_draw_date_field($name, $input, $parameters);

      case 'datetime':
        return form_draw_datetime_field($name, $input, $parameters);

      case 'decimal':
      case 'float':
        return form_draw_decimal_field($name, $input, 2, $parameters);

      case 'number':
      case 'int':
        return form_draw_number_field($name, $input, $parameters);

      case 'checkbox':
        $html = '';
        foreach ($options as $option) {
          $html .= form_draw_checkbox($name, [$option, $option], $input, $parameters);
        }
        return $html;

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
      case 'categories':
        return form_draw_categories_list($name, $input, $parameters);

      case 'customer':
      case 'customers':
        return form_draw_customers_list($name, $input, $parameters);

      case 'country':
      case 'countries':
        return form_draw_countries_list($name, $input, $parameters);

      case 'currency':
      case 'currencies':
        return form_draw_currencies_list($name, $input, $parameters);

      case 'csv':
        return form_draw_textarea($name, $input, true, $parameters);

      case 'delivery_status':
      case 'delivery_statuses':
        return form_draw_delivery_statuses_list($name, $input, $parameters);

      case 'email':
        return form_draw_email_field($name, $input, $parameters);

      case 'file':
        return functions::form_draw_file_field($name);

      case 'geo_zone':
      case 'geo_zones':
        return form_draw_geo_zones_list($name, $input, $parameters);

      case 'language':
      case 'languages':
        return form_draw_languages_list($name, $input, $parameters);

      case 'length_class': // Deprecated
      case 'length_classes': // Deprecated
      case 'length_unit':
      case 'length_units':
        return form_draw_length_units_list($name, $input, $parameters);

      case 'product':
      case 'products':
        return form_draw_products_list($name, $input, $parameters);

      case 'quantity_unit':
      case 'quantity_units':
        return form_draw_quantity_units_list($name, $input, $parameters);

      case 'stock_option':
      case 'stock_options':
        return form_draw_stock_options_list($name, $input, $parameters);

      case 'order_status':
      case 'order_statuses':
        return form_draw_order_status_list($name, $input, $parameters);

      case 'page':
      case 'pages':
        return form_draw_pages_list($name, $input, $parameters);

      case 'password':
        return functions::form_draw_password_field($name, $input);

      case 'radio':
        $html = '';
        foreach ($options as $option) {
          $html .= form_draw_radio_button($name, [$option, $option], $input, $parameters);
        }
        return $html;

      case 'regional_input': //Deprecated
      case 'regional_text':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_draw_regional_text_field($name.'['. $language_code.']', $language_code, $input, $parameters);
        }
        return $html;

      case 'regional_textarea':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_draw_regional_textarea($name.'['. $language_code.']', $language_code, $input, $parameters);
        }
        return $html;

      case 'regional_wysiwyg':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_draw_regional_wysiwyg_field($name.'['. $language_code.']', $language_code, $input, $parameters);
        }
        return $html;

      case 'select':
      case 'select_multiple': // Deprecated
        for ($i=0; $i<count($options); $i++) $options[$i] = [$options[$i]];
        return form_draw_select_field($name, $options, $input, $parameters);

      case 'timezone':
      case 'timezones':
        return form_draw_timezones_list($name, $input, $parameters);

      case 'template':
      case 'templates':
        return form_draw_templates_list($name, $input, $parameters);

      case 'time':
        return form_draw_time_field($name, $input, $parameters);

      case 'toggle':
        return form_draw_toggle($name, !empty($options[0]) ? $options[0] : null, $input);

      case 'sold_out_status':
      case 'sold_out_statuses':
        return form_draw_sold_out_statuses_list($name, $input, $parameters);

      case 'tax_class':
      case 'tax_classes':
        return form_draw_tax_classes_list($name, $input, $parameters);

      case 'url':
        return form_draw_url_field($name, $input, $parameters);

      case 'user':
      case 'users':
        return form_draw_users_list($name, $input, $parameters);

      case 'weight_class': // Deprecated
      case 'weight_classes': // Deprecated
      case 'weight_unit':
      case 'weight_units':
        return form_draw_weight_units_list($name, $input, $parameters);

      case 'volume_unit':
      case 'volume_units':
        return form_draw_volume_units_list($name, $input, $parameters);

      case 'wysiwyg':
        return form_draw_regional_wysiwyg_field($input, $name, $parameters);

      case 'zone':
      case 'zones':
        $option = !empty($options) ? $options[0] : '';
        //if (empty($option)) $option = settings::get('site_country_code');
        return form_draw_zones_list($name, $option, $input, $parameters);

      default:
        trigger_error('Unknown function name ('. $function .')', E_USER_WARNING);
        return form_draw_hidden_field($name, $input, $parameters);
        break;
    }
  }

  function form_draw_attribute_groups_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_attribute_groups_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $query = database::query(
      "select ag.id, agi.name from ". DB_TABLE_PREFIX ."attribute_groups ag
      left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = ag.id and agi.language_code = '". database::input(language::$selected['code']) ."')
      order by name;"
    );

    $options = [];
    while ($row = database::fetch($query)) {
      $options[] = [$row['id'], $row['name']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_attribute_values_list($name, $group_id, $input=true, $parameters='') {

    if (is_numeric($name)) {
      trigger_error('form_draw_attribute_values_list() no longer takes group ID as 1st parameter. Instead, use form_draw_attribute_values_list($name, $group_id, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $group_id) = [$group_id, $name];
    }

    if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
      trigger_error('Passing $multiple as 4th parameter in form_draw_attribute_values_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[4])) $parameters = $args[3];
    }

    $query = database::query(
      "select av.id, avi.name from ". DB_TABLE_PREFIX ."attribute_values av
      left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = av.id and avi.language_code = '". database::input(language::$selected['code']) ."')
      where group_id = ". (int)$group_id ."
      order by name;"
    );

    $options = [];
    while ($row = database::fetch($query)) {
      $options[] = [$row['id'], $row['name']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_categories_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_categories_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if (!preg_match('#\[\]$#', $name)) {
      return form_draw_category_field($name, $options, $input, $parameters);
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    $html = '<div data-toggle="category-picker"' . (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL
          . '  <div class="form-input" style="overflow-y: auto; min-height: 100px; max-height: 480px;">' . PHP_EOL
          . '    <ul class="categories list-unstyled">' . PHP_EOL;

    if (empty($parent_id)) $options[] = array(functions::draw_fonticon('fa-folder fa-lg', 'style="color: #cccc66;"') . ' ['.language::translate('title_root', 'Root').']', '0');

    $categories_query = database::query(
      "select c.id, ci.name from ". DB_TABLE_PREFIX ."categories c
      left join ". DB_TABLE_PREFIX ."categories_info ci on (c.id = ci.category_id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.id in ('". implode("', '", database::input($input)) ."');"
    );

    while ($category = database::fetch($categories_query)) {

      $path = [];
      if (!empty(reference::category($category['id'])->path)) {
        foreach (reference::category($category['id'])->path as $ancestor) {
          $path[] = $ancestor->name;
        }
      }

      $html .= '<li class="list-item" style="display: flex;">'. PHP_EOL
             . '  ' . form_draw_hidden_field($name, $category['id'], 'data-name="'. htmlspecialchars($category['name']) .'"') . PHP_EOL
             . '  <div style="flex-grow: 1;">' . functions::draw_fonticon('folder') .' '. implode(' &gt; ', $path) .'</div>'. PHP_EOL
             . '  <button class="remove btn btn-default btn-sm" type="button">'. language::translate('title_remove', 'Remove') .'</button>' . PHP_EOL
             .'</li>';
    }

    $html .= '    </ul>' . PHP_EOL
           . '  </div>' . PHP_EOL
           . '  <div class="dropdown">' . PHP_EOL
           . '  '. form_draw_search_field('', '', 'autocomplete="off" placeholder="'. htmlspecialchars(language::translate('text_search_categories', 'Search categories')) .'&hellip;"') . PHP_EOL
           . '    <ul class="dropdown-menu" style="padding: .5em; right: 0;"></ul>' . PHP_EOL
           . '  </div>' . PHP_EOL
           . '</div>';

    document::$snippets['javascript']['category-picker'] = '$(\'[data-toggle="category-picker"]\').categoryPicker({' . PHP_EOL
                                                         . '  inputName: "'. $name .'",' . PHP_EOL
                                                         . '  link: "'. document::ilink('catalog/categories.json') .'",' . PHP_EOL
                                                         . '  icons: {' . PHP_EOL
                                                         . '    folder: \''. functions::draw_fonticon('folder') .'\',' . PHP_EOL
                                                         . '    back: \''. functions::draw_fonticon('fa-arrow-left') .'\'' . PHP_EOL
                                                         . '  },' . PHP_EOL
                                                         . '  translations: {' . PHP_EOL
                                                         . '    search_results: "'. language::translate('title_search_results', 'Search Results') .'",' . PHP_EOL
                                                         . '    root: "'. language::translate('title_root', 'Root') .'",' . PHP_EOL
                                                         . '    add: "'. language::translate('title_add', 'Add') .'",' . PHP_EOL
                                                         . '    remove: "'. language::translate('title_remove', 'Remove') .'",' . PHP_EOL
                                                         . '    root: "'. language::translate('title_root', 'Root') .'"' . PHP_EOL
                                                         . '  }' . PHP_EOL
                                                         . '})';

    return $html;
  }

  function form_draw_brands_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_brands_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $brands_query = database::query(
      "select id, name from ". DB_TABLE_PREFIX ."brands
      order by name asc;"
    );

    $options = [];
    while ($brand = database::fetch($brands_query)) {
      $options[] = [$brand['id'], $brand['name']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_countries_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_countries_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_country_code');
    }

    switch ($input) {
      case 'customer_country_code':
        $input = customer::$data['country_code'];
        break;
      case 'default_country_code':
        $input = settings::get('default_country_code');
        break;
      case 'store_country_code':
        $input = settings::get('store_country_code');
        break;
    }

    $countries_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."countries
      where status
      order by name asc;"
    );

    $options = [];
    while ($country = database::fetch($countries_query)) {
      $options[] = [$country['iso_code_2'], $country['name'], 'data-tax-id-format="'. $country['tax_id_format'] .'" data-postcode-format="'. $country['postcode_format'] .'" data-phone-code="'. $country['phone_code'] .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_currencies_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_currencies_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = [];
    foreach (currency::$currencies as $currency) {
      $options[] = [$currency['code'], $currency['name'], 'data-value="'. (float)$currency['value'] .'" data-decimals="'. (int)$currency['decimals'] .'" data-prefix="'. htmlspecialchars($currency['prefix']) .'" data-suffix="'. htmlspecialchars($currency['suffix']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_customers_list($name, $input=true, $parameters='') {

    if (empty(user::$data['id'])) trigger_error('Must be logged in to use form_draw_customers_list()', E_USER_ERROR);

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_customers_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = [];
    $customers_query = database::query(
      "select id, email, company, firstname, lastname from ". DB_TABLE_PREFIX ."customers
      order by email;"
    );

    while ($customer = database::fetch($customers_query)) {
      $options[] = [$customer['id'], $customer['email']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_delivery_statuses_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_delivery_statuses_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_delivery_status_id');
    }

    $query = database::query(
      "select ds.id, dsi.name , dsi.description from ". DB_TABLE_PREFIX ."delivery_statuses ds
      left join ". DB_TABLE_PREFIX ."delivery_statuses_info dsi on (dsi.delivery_status_id = ds.id and dsi.language_code = '". database::input(language::$selected['code']) ."')
      order by dsi.name asc;"
    );

    $options = [];
    while ($row = database::fetch($query)) {
      $options[] = [$row['id'], $row['name'], 'title="'. htmlspecialchars($row['description']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_encodings_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_encodings_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

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

    $options = [];
    foreach ($encodings as $encoding) {
      $options[] = $encoding;
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_geo_zones_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_geo_zones_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $geo_zones_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."geo_zones
      order by name asc;"
    );

    if (!database::num_rows($geo_zones_query)) {
      return form_draw_select_field($name, $options, $input, false, false, $parameters . ' disabled');
    }

    $options = [];
    while ($geo_zone = database::fetch($geo_zones_query)) {
      $options[] = [$geo_zone['id'], $geo_zone['name']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_languages_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_languages_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = [];

    foreach (language::$languages as $language) {
      $options[] = [$language['code'], $language['name'], 'data-decimal-point="'. $language['decimal_point'] .'" data-thousands-sep="'. $language['thousands_sep'] .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_length_classes_list($name, $input=true, $multiple=false, $parameters='') {
    trigger_error('form_draw_length_classes_list() is deprecated. Instead, use form_draw_length_units_list()', E_USER_DEPRECATED);
    return form_draw_length_units_list($name, $input, $parameters);
  }

  function form_draw_length_units_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_length_units_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('site_length_unit');
    }

    $options = [];
    foreach (length::$units as $unit) {
      $options[] = [$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. htmlspecialchars($unit['name']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['--', '']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_mysql_collations_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_mysql_collations_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $collations_query = database::query(
      "select * from information_schema.COLLATIONS
      where CHARACTER_SET_NAME = 'utf8mb4'
      order by COLLATION_NAME;"
    );

    $options = [];
    while ($row = database::fetch($collations_query)) {
      $options[] = $row['COLLATION_NAME'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_mysql_engines_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_mysql_engines_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $collations_query = database::query(
      "SHOW ENGINES;"
    );

    $options = [];
    while ($row = database::fetch($collations_query)) {
      if (!in_array(strtoupper($row['Support']), ['YES', 'DEFAULT'])) continue;
      if (!in_array($row['Engine'], ['CSV', 'InnoDB', 'MyISAM', 'Aria'])) continue;
      $options[] = [$row['Engine'], $row['Engine'] . ' -- '. $row['Comment']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_order_status_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_order_status_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $query = database::query(
      "select os.id, osi.name from ". DB_TABLE_PREFIX ."order_statuses os
      left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = os.id and osi.language_code = '". database::input(language::$selected['code']) ."')
      order by priority, name;"
    );

    $options = [];
    while ($row = database::fetch($query)) {
      $options[] = [$row['id'], $row['name']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_pages_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_pages_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $iterator = function($parent_id, $level) use (&$iterator) {

      $options = [];

      if (empty($parent_id)) $options[] = ['0', '['.language::translate('title_root', 'Root').']'];

      $pages_query = database::query(
        "select p.id, pi.title from ". DB_TABLE_PREFIX ."pages p
        left join ". DB_TABLE_PREFIX ."pages_info pi on (pi.page_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        where p.parent_id = '". (int)$parent_id ."'
        order by p.priority asc, pi.title asc;"
      );

      while ($page = database::fetch($pages_query)) {

        $options[] = [$page['id'], str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $page['title']];

        $sub_pages_query = database::query(
          "select id from ". DB_TABLE_PREFIX ."pages
          where parent_id = '". (int)$page['id'] ."'
          limit 1;"
        );

        $sub_options = $iterator($page['id'], $level+1);

        $options = array_merge($options, $sub_options);
      }

      return $options;
    };

    $options = $iterator(0, 1);

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_payment_modules_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_payment_modules_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $modules_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."modules
      where type = 'payment'
      and status;"
    );

    $options = [];
    while ($module = database::fetch($modules_query)) {
      $module = new $module();
      $options[] = [$module->id, $module->name];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_products_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_products_list() is deprecated.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $products_query = database::query(
      "select p.*, pi.name from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."products_info pi on (p.id = pi.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      order by pi.name"
    );

    $options = [];
    while ($product = database::fetch($products_query)) {
      $options[] = [$product['id'], $product['name'] .' &mdash; '. $product['sku'] . ' ['. (float)$product['quantity'] .']'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_quantity_units_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_quantity_units_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_quantity_unit_id');
    }

    $quantity_units_query = database::query(
      "select qu.*, qui.name, qui.description from ". DB_TABLE_PREFIX ."quantity_units qu
      left join ". DB_TABLE_PREFIX ."quantity_units_info qui on (qui.quantity_unit_id = qu.id and language_code = '". database::input(language::$selected['code']) ."')
      order by qu.priority, qui.name asc;"
    );

    $options = [];
    while ($quantity_unit = database::fetch($quantity_units_query)) {
      $options[] = [$quantity_unit['id'], $quantity_unit['name'], 'data-separate="'. (!empty($quantity_unit['separate']) ? 'true' : 'false') .'" data-decimals="'. (int)$quantity_unit['decimals'] .'" title="'. htmlspecialchars($quantity_unit['description']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_shipping_modules_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_shipping_modules_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $modules_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."modules
      where type = 'shipping'
      and status;"
    );

    $options = [];
    while ($module = database::fetch($modules_query)) {
      $module = new $module();
      $options[] = [$module->id, $module->name];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_sold_out_statuses_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_sold_out_statuses_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_sold_out_status_id');
    }

    $query = database::query(
      "select sos.id, sosi.name, sosi.description from ". DB_TABLE_PREFIX ."sold_out_statuses sos
      left join ". DB_TABLE_PREFIX ."sold_out_statuses_info sosi on (sosi.sold_out_status_id = sos.id and sosi.language_code = '". database::input(language::$selected['code']) ."')
      order by sosi.name asc;"
    );

    $options = [];
    while ($row = database::fetch($query)) {
      $options[] = [$row['id'], $row['name'], 'title="'. htmlspecialchars($row['description']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_stock_items_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_stock_items_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $stock_items_query = database::query(
      "select si.*, sii.name from ". DB_TABLE_PREFIX ."stock_items si
      left join ". DB_TABLE_PREFIX ."stock_items_info sii on (si.id = sii.product_id and sii.language_code = '". database::input(language::$selected['code']) ."')
      order by sii.name"
    );

    $options = [];
    while ($stock_item = database::fetch($stock_items_query)) {
      $options[] = [$stock_item['id'], $stock_item['name'] .' &mdash; '. $stock_item['sku'] . ' ['. (float)$stock_item['quantity'] .']'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_suppliers_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_suppliers_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $suppliers_query = database::query(
      "select id, name, description from ". DB_TABLE_PREFIX ."suppliers
      order by name;"
    );

    $options = [];
    while ($supplier = database::fetch($suppliers_query)) {
      $options[] = [$supplier['id'], $supplier['name'], 'title="'. htmlspecialchars($supplier['description']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_tax_classes_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_tax_classes_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_tax_class_id');
    }

    $tax_classes_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."tax_classes
      order by name asc;"
    );

    $options = [];
    while ($tax_class = database::fetch($tax_classes_query)) {
      $options[] = [$tax_class['id'], $tax_class['name'], 'title="'. htmlspecialchars($tax_class['description']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_templates_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_templates_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $folders = glob(FS_DIR_APP . 'frontend/templates/*', GLOB_ONLYDIR);

    $options = [];
    foreach ($folders as $folder) {
      $options[] = basename($folder);
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_timezones_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_timezones_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = [];
    foreach (timezone_identifiers_list() as $timezone) {
      $timezone = explode('/', $timezone); // 0 => Continent, 1 => City

      if (in_array($timezone[0], ['Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'])) {
        if (!empty($timezone[1])) {
          $options[] = implode('/', $timezone);
        }
      }
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_users_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_users_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $users_query = database::query(
      "select id, username from ". DB_TABLE_PREFIX ."users
      order by username;"
    );

    $options = [];
    while ($user = database::fetch($users_query)) {
      $options[] = [$user['id'], $user['username']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_weight_classes_list($name, $input=true, $multiple=false, $parameters='') {
    trigger_error('form_draw_weight_classes_list() is deprecated. Instead, use form_draw_weight_units_list()', E_USER_DEPRECATED);
    return form_draw_weight_units_list($name, $input, $parameters);
  }

  function form_draw_weight_units_list($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_draw_weight_units_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
      if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('site_weight_unit');
    }

    $options = [];
    foreach (weight::$units as $unit) {
      $options[] = [$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. htmlspecialchars($unit['name']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['--', '']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_volume_units_list($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    $options = [];
    foreach (volume::$units as $unit) {
      $options[] = [$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. htmlspecialchars($unit['name']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['--', '']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_zones_list($name, $country_code='', $input=true, $parameters='', $preamble='none') {

    if (preg_match('#^([A-Z]{2}|default_country_code|store_country_code)$#', $name)) {
      trigger_error('form_draw_zones_list() no longer takes country code as 1st parameter. Instead, use form_draw_zones_list($name, $country_code, $input)', E_USER_DEPRECATED);
      list($name, $country_code) = [$country_code, $name];
    }

    if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
      trigger_error('Passing $multiple as 4th parameter in form_draw_zones_list() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[4])) $parameters = $args[3];
    }

    if ($country_code == '') $country_code = settings::get('site_country_code');
    if ($country_code == 'default_country_code') $country_code = settings::get('default_country_code');
    if ($country_code == 'site_country_code') $country_code = settings::get('site_country_code');

    $zones_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."zones
      where country_code = '". database::input($country_code) ."'
      order by name asc;"
    );

    $options = [];

    if (!database::num_rows($zones_query)) {
      $parameters .= ' disabled';
    }

    while ($zone = database::fetch($zones_query)) {
      $options[] = [$zone['code'], $zone['name']];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      switch($preamble) {
        case 'all':
          array_unshift($options, ['', '-- '. language::translate('title_all_zones', 'All Zones') . ' --']);
          break;
        case 'select':
          array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
          break;
      }
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }
