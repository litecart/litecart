<?php

  function form_draw_form_begin($name='', $method='post', $action=false, $multipart=false, $parameters='') {
    return  '<form'. (($name) ? ' name="'. functions::escape_html($name) .'"' : false) .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'" accept-charset="'. language::$selected['charset'] .'"'. (($action) ? ' action="'. functions::escape_html($action) .'"' : '') . (($parameters) ? ' ' . $parameters : false) .'>';
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

    // Reinsert node value
      if (is_array($node) && $array_value !== null) {

      // Attempt reinserting a numerical indexed array value
        if (preg_match('#\[\]$#', $name)) {
          if (!is_array($node) || !in_array($array_value, $node)) continue;
          return $array_value;

      // Reinsert a defined key array value
        } else {
          if ($array_value != $node) continue;
          return $array_value;
        }
      }

      if (!empty($node)) return $node;
    }

    return '';
  }

  function form_draw_button($name, $value, $type='submit', $parameters='', $icon='') {

    if (is_array($value)) {
      list($value, $title) = $value;
    } else {
      $title = $value;
    }

    return '<button '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default"' : '') .' type="'. functions::escape_html($type) .'" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. ((!empty($icon)) ? functions::draw_fonticon($icon) . ' ' : '') . $title .'</button>';
  }

  function form_draw_captcha_field($name, $id, $parameters='') {

    $output = '<div class="input-group">' . PHP_EOL
            . '  <span class="input-group-text" style="padding: 0;">'. functions::captcha_generate(100, 40, 4, $id, 'numbers', 'align="absbottom"') .'</span>' . PHP_EOL
            . '  ' . form_draw_text_field('captcha', '', $parameters . ' style="font-size: 24px; padding: 0; text-align: center;"') . PHP_EOL
            . '</div>';

    return $output;
  }

  function form_draw_category_field($name, $input=true, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    $account_name = language::translate('title_root', 'Root');

    if (!empty($input)) {
      $category_query = database::query(
        "select c.id, c.code, ci.name, c.date_created from ". DB_TABLE_PREFIX ."categories c
        left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
        where c.id = ". (int)$input ."
        limit 1;"
      );

      $category_name = database::fetch($category_query, 'name');
    } else {
      $category_name = '['. language::translate('title_root', 'Root') .']';
    }

    functions::draw_lightbox();

    return '<div class="input-group"'. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL
         . '  <div class="form-control">' . PHP_EOL
         . '    ' . form_draw_hidden_field($name, true) . PHP_EOL
         . '    '. functions::draw_fonticon('fa-folder', 'style="color: #cc6;"') .' <span class="name" style="display: inline-block;">'. $category_name .'</span>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '  <div style="align-self: center;">' . PHP_EOL
         . '    <a href="'. document::href_link(WS_DIR_ADMIN, ['app' => 'catalog', 'doc' => 'category_picker', 'parent_id' => $input]) .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. language::translate('title_change', 'Change') .'</a>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '</div>';
  }

  function form_draw_checkbox($name, $value, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name, $value);

    return '<input class="form-check" type="checkbox" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" '. ($input == $value ? ' checked' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
  }

  function form_draw_code_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<textarea '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-code"' : '') .' name="'. functions::escape_html($name) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. functions::escape_html($value) .'</textarea>';
  }

  function form_draw_color_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="color" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="color" '. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_currency_field($currency_code, $name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

  // Format and show an additional two decimals precision if needed
    if ($value != '') {
      $value = number_format((float)$value, currency::$currencies[$currency_code]['decimals'] + 2, '.', '');
      $value = preg_replace('#(\.'. str_repeat('\d', 2) .')0{1,2}$#', '$1', $value);
      $value = rtrim($value, '.');
    }

    if (empty($currency_code)) $currency_code = settings::get('store_currency_code');

    return '<div class="input-group">' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="number" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" step="any" data-type="currency"'. (($parameters) ? ' '. $parameters : false) .' />' . PHP_EOL
         . '  <strong class="input-group-text" style="opacity: 0.75;">'. functions::escape_html($currency_code) .'</strong>' . PHP_EOL
         . '</div>';
  }

  function form_draw_customer_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    $account_name = language::translate('title_guest', 'Guest');

    if (!empty($value)) {
      $customer_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."customers
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
         . '  '. language::translate('title_id', 'ID') .': <span class="id">'. (int)$value .'</span> &ndash; <span class="name">'. $account_name .'</span> <a href="'. document::href_link(WS_DIR_ADMIN, ['app' => 'customers', 'doc' => 'customer_picker']) .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;">'. language::translate('title_change', 'Change') .'</a>' . PHP_EOL
         . '</div>';
  }

  function form_draw_date_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!empty($value) && !in_array(substr($value, 0, 10), ['', '0000-00-00', '1970-00-00', '1970-01-01'])) {
      $value = date('Y-m-d', strtotime($value));
    } else {
      $value = '';
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="date" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="date" placeholder="YYYY-MM-DD"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_datetime_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!empty($value) && !in_array(substr($value, 0, 10), ['', '0000-00-00', '1970-00-00', '1970-01-01'])) {
      $value = date('Y-m-d\TH:i', strtotime($value));
    } else {
      $value = '';
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="datetime-local" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="datetime" placeholder="YYYY-MM-DD [hh:nn]"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_decimal_field($name, $value=true, $decimals=2, $min=null, $max=null, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if ($value != '') {
      $value = round((float)$value, $decimals);
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="number" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="decimal" '. (($min != '') ? 'min="'. (float)$min .'"' : false) . (($max != '') ? ' max="'. (float)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) . (!preg_match('#step="([^"]+)?"#', $parameters) ? ' step="any"' : '') .' />';
  }

  function form_draw_email_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text">'. functions::draw_fonticon('fa-envelope-o fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="email" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="email"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_file_field($name, $parameters='') {

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="file" name="'. functions::escape_html($name) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_fonticon_field($name, $value, $type, $icon, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text">'. functions::draw_fonticon($icon) .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="'. functions::escape_html($type) .'" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />' . PHP_EOL
         . '</div>';
  }

  function form_draw_hidden_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input type="hidden" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_image($name, $src, $parameters='') {
    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="image" name="'. functions::escape_html($name) .'" src="'. functions::escape_html($src) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_input($name, $value=true, $type='text', $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="'. functions::escape_html($type) .'" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_link_button($url, $title, $parameters='', $icon='') {

    if (empty($url)) {
      $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    return '<a '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default"' : '') .' href="'. functions::escape_html($url) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. (!empty($icon) ? functions::draw_fonticon($icon) . ' ' : false) . $title .'</a>';
  }

  function form_draw_month_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!in_array(substr($value, 0, 7), ['', '0000-00', '1970-00', '1970-01'])) {
      $value = date('Y-m', strtotime($value));
    } else {
      $value = '';
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="month" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="month" maxlength="7" pattern="[0-9]{4}-[0-9]{2}" placeholder="YYYY-MM"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_number_field($name, $value=true, $min=null, $max=null, $parameters='') {
    if ($value === true) $value = (int)form_reinsert_value($name);

    if ($value != '') {
      $value = floor($value);
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="number" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="number" step="1" '. (($min !== null) ? 'min="'. (int)$min .'"' : false) . (($max !== null) ? ' max="'. (int)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_password_field($name, $value='', $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text">'. functions::draw_fonticon('fa-key fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="password" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="password"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_phone_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text">'. functions::draw_fonticon('fa-phone fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="tel" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="phone" pattern="^\+?([0-9]|-| )+$"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_radio_button($name, $value, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name, $value);

    return '<input class="form-check" type="radio" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" '. ($input == $value ? ' checked' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
  }

  function form_draw_range_slider($name, $value=true, $min='', $max='', $step='', $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="range" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="range" min="'. (float)$min .'" max="'. (float)$max .'" step="'. (float)$step .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_regional_input_field($language_code, $name, $value=true, $parameters='') {
    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text" style="font-family: monospace;">'. $language_code .'</span>' . PHP_EOL
         . '  ' . form_draw_text_field($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_textarea($language_code, $name, $value=true, $parameters='') {

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text" style="font-family: monospace;">'. $language_code .'</span>' . PHP_EOL
         . '  ' . form_draw_textarea($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_wysiwyg_field($language_code, $name, $value=true, $parameters='') {

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text" style="font-family: monospace;">'. $language_code .'</span>' . PHP_EOL
         . '  ' . form_draw_wysiwyg_field($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_search_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-icon">'. functions::draw_fonticon('fa-search fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="search" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="search"'. (($parameters) ? ' '.$parameters : false) .' />' . PHP_EOL
         . '</div>';
  }

  function form_draw_select_field($name, $options=[], $input=true, $parameters='') {

    if (is_bool($parameters)) {
      $args = func_get_args();
      if ($parameters === true) {
        trigger_error('The 4th parameter $multiple in form_draw_select_field() has been deprecated. Use form_draw_select_multiple_field()', E_USER_DEPRECATED);
        return form_draw_select_multiple_field($args[0], $args[1], $args[2], isset($args[4]) ? $args[4] : '');
      } else {
        trigger_error('The 4th parameter $multiple in form_draw_select_field() has been deprecated', E_USER_DEPRECATED);
        return form_draw_select_field($args[0], $args[1], $args[2], isset($args[4]) ? $args[4] : '');
      }
    }

    $html = '<select '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' name="'. functions::escape_html($name) .'"'. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;

    foreach ($options as $option) {

      if (!is_array($option)) $option = [$option, $option];

      if ($input === true) {
        $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
      } else {
        $option_input = $input;
      }

      $html .= '  <option value="'. functions::escape_html(isset($option[1]) ? $option[1] : $option[0]) .'"'. (isset($option[1]) ? (($option[1] == $option_input) ? ' selected="selected"' : false) : (($option[0] == $option_input) ? ' selected="selected"' : false)) . ((isset($option[2])) ? ' ' . $option[2] : false) . '>'. $option[0] .'</option>' . PHP_EOL;
    }

    $html .= '</select>';

    return $html;
  }

  function form_draw_select_multiple_field($name, $options=[], $input=true, $parameters='') {

    $html = '<div class="form-control" style="overflow-y: auto; max-height: 200px;">' . PHP_EOL;

    foreach ($options as $option) {

      if (!is_array($option)) $option = [$option, $option];

      if ($input === true) {
        $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
      } else {
        $option_input = $input;
      }

      $html .= '  <div class="checkbox">'. PHP_EOL
             . '    <label>'. form_draw_checkbox($name, isset($option[1]) ? $option[1] : $option[0], $option_input, isset($option[2]) ? $option[2] : null) .' '.  $option[0] .'</label>' . PHP_EOL
             . '  </div>';
    }

    $html .= '</div>';

    return $html;
  }

  function form_draw_select_optgroup_field($name, $groups=[], $input=true, $multiple=false, $parameters='') {
    if (!is_array($groups)) $groups = [$groups];

    $html = '<select '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' name="'. functions::escape_html($name) .'"'. (($multiple) ? ' multiple="multiple"' : false) .''. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;

    foreach ($groups as $group) {
      $html .= '    <optgroup label="'. $group['label'] .'">' . PHP_EOL;
      foreach ($group['options'] as $option) {
        if ($input === true) {
          $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
        } else {
          $option_input = $input;
        }
        $html .= '    <option value="'. functions::escape_html(isset($option[1]) ? $option[1] : $option[0]) .'"'. (isset($option[1]) ? (($option[1] == $option_input) ? ' selected="selected"' : false) : (($option[0] == $option_input) ? ' selected="selected"' : false)) . ((isset($option[2])) ? ' ' . $option[2] : false) . '>'. $option[0] .'</option>' . PHP_EOL;
      }
      $html .= '  </optgroup>' . PHP_EOL;
    }

    $html .= '</select>';

    return $html;
  }

  function form_draw_textarea($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<textarea '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' name="'. functions::escape_html($name) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. functions::escape_html($value) .'</textarea>';
  }

  function form_draw_text_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="text" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="text"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_time_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="time" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="time"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_toggle($name, $input=true, $type='e/d', $parameters='') {
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
          $option = [$option, $key];
        }
      }

      $html .= '  <label class="btn btn-default'. ($input == $option[1] ? ' active' : '') .'">' . PHP_EOL
             . '    <input type="radio" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($option[1]) .'"'. (!strcmp($input, $option[1]) ? ' checked' : '') . (!empty($option[2]) ? ' '. $option[2] : '') .' />'. $option[0]
             . '  </label>'. PHP_EOL;
    }

    $html .= '</div>';

    return $html;
  }

  function form_draw_url_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="url" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="url"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_username_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-text">'. functions::draw_fonticon('fa-user fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="text" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" data-type="text"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_wysiwyg_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    document::$snippets['head_tags']['trumbowyg'] = '<link href="'. document::href_rlink(FS_DIR_APP .'ext/trumbowyg/ui/trumbowyg.min.css') .'" rel="stylesheet" />' . PHP_EOL
                                                  . '<link href="'. document::href_rlink(FS_DIR_APP .'ext/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css') .'" rel="stylesheet" />'
                                                  . '<link href="'. document::href_rlink(FS_DIR_APP .'ext/trumbowyg/plugins/table/ui/trumbowyg.table.min.css') .'" rel="stylesheet" />';

    document::$snippets['foot_tags']['trumbowyg'] = '<script src="'. document::href_rlink(FS_DIR_APP . 'ext/trumbowyg/trumbowyg.min.js') .'"></script>' . PHP_EOL
                                                  . ((language::$selected['code'] != 'en') ? '<script src="'. document::href_rlink(FS_DIR_APP . 'ext/trumbowyg/langs/'. language::$selected['code'] .'.min.js') .'"></script>' . PHP_EOL : '')
                                                  . '<script src="'. document::href_rlink(FS_DIR_APP . 'ext/trumbowyg/plugins/colors/trumbowyg.colors.min.js') .'"></script>' . PHP_EOL
                                                  . '<script src="'. document::href_rlink(FS_DIR_APP . 'ext/trumbowyg/plugins/table/trumbowyg.table.min.js') .'"></script>';

    document::$snippets['javascript'][] = '  $(\'textarea[name="'. $name .'"]\').trumbowyg({' . PHP_EOL
                                        . '    btns: [["viewHTML"], ["formatting"], ["strong", "em", "underline", "del"], ["foreColor", "backColor"], ["link"], ["insertImage"], ["table"], ["justifyLeft", "justifyCenter", "justifyRight"], ["lists"], ["preformatted"], ["horizontalRule"], ["removeformat"], ["fullscreen"]],' . PHP_EOL
                                        . '    btnsDef: {' . PHP_EOL
                                        . '      lists: {' . PHP_EOL
                                        . '        dropdown: ["unorderedList", "orderedList"],' . PHP_EOL
                                        . '        title: "Lists",' . PHP_EOL
                                        . '        ico: "unorderedList",' . PHP_EOL
                                        . '      }' . PHP_EOL
                                        . '    },' . PHP_EOL
                                        . '    lang: "'. language::$selected['code'] .'",' . PHP_EOL
                                        . '    autogrowOnEnter: true,' . PHP_EOL
                                        . '    imageWidthModalEdit: true,' . PHP_EOL
                                        . '    removeformatPasted: true,' . PHP_EOL
                                        . '    semantic: false' . PHP_EOL
                                        . '  });';

    return '<textarea name="'. functions::escape_html($name) .'" data-type="wysiwyg"'. (($parameters) ? ' '.$parameters : false) .'>'. functions::escape_html($value) .'</textarea>';
  }

  ######################################################################

  function form_draw_function($function, $name, $input=true, $parameters='') {

    preg_match('#^(\w+)(?:\((.*?)\))?$#', $function, $matches);

    if (!isset($matches[1])) {
      trigger_error('Invalid function name ('. $function .')', E_USER_WARNING);
      return form_draw_textarea($name, $input, $parameters . ' rows="1"');
    }

    $options = [];
    if (isset($matches[2])) {
      $options = explode(',', $matches[2]);
      for ($i=0; $i<count($options); $i++) {
        $options[$i] = trim($options[$i], '\'" ');
      }
    }

    switch ($matches[1]) {

      case 'date':
        return form_draw_date_field($name, $input, $parameters);

      case 'datetime':
        return form_draw_datetime_field($name, $input, $parameters);

      case 'decimal':
      case 'float':
        return form_draw_decimal_field($name, $input, 2, null, null, $parameters);

      case 'number':
      case 'int':
        return form_draw_number_field($name, $input, null, null, $parameters);

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
        return form_draw_email_field($name, $input, $parameters);

      case 'file':
      case 'files':
        return form_draw_files_list($name, $options[0], $input, $parameters);

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

      case 'order_status':
        return form_draw_order_status_list($name, $input, false, $parameters);

      case 'order_statuses':
        return form_draw_order_status_list($name, $input, true, $parameters);

      case 'regional_input': //Deprecated
      case 'regional_text':
        $output = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $output .= form_draw_regional_input_field($language_code, $name.'['. $language_code.']', $input, $parameters);
        }
        return $output;

      case 'regional_textarea':
        $output = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $output .= form_draw_regional_textarea($language_code, $name.'['. $language_code.']', $input, $parameters);
        }
        return $output;

      case 'regional_wysiwyg':
        $output = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $output .= form_draw_regional_wysiwyg_field($language_code, $name.'['. $language_code.']', $input, $parameters);
        }
        return $output;

      case 'page':
        return form_draw_pages_list($name, $input, false, $parameters);

      case 'pages':
        return form_draw_pages_list($name, $input, true, $parameters);

      case 'radio':
        $output = '';
        for ($i=0; $i<count($options); $i++) {
          $output .= '<div class="radio"><label>'. form_draw_radio_button($name, $options[$i], $input, $parameters) .' '. $options[$i] .'</label></div>';
        }
        return $output;

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

      case 'upload':
        return form_draw_file_field($name, $parameters);

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
        return form_draw_text_field($name, $input, $parameters);
        break;
    }
  }

  function form_draw_attribute_groups_list($name, $input=true, $multiple=false, $parameters='') {

    $query = database::query(
      "select ag.id, agi.name from ". DB_TABLE_PREFIX ."attribute_groups ag
      left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = ag.id and agi.language_code = '". database::input(language::$selected['code']) ."')
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
      "select av.id, avi.name from ". DB_TABLE_PREFIX ."attribute_values av
      left join ". DB_TABLE_PREFIX ."attribute_groups_info avi on (avi.value_id = av.id and avi.language_code = '". database::input(language::$selected['code']) ."')
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

    if (!$multiple || !preg_match('#\[\]$#', $name)) {
      return form_draw_category_field($name, $input, $parameters);
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    $html = '<div data-toggle="category-picker"' . (($parameters) ? ' ' . $parameters : '') .'>' . PHP_EOL
          . '  <div class="form-control" style="overflow-y: auto; min-height: 100px; max-height: 480px;">' . PHP_EOL
          . '    <ul class="categories list-unstyled">' . PHP_EOL;

    if (!empty($input)) {

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

        $html .= '<li class="list-item" style="display: flex; align-items: center;">'. PHP_EOL
               . '  ' . form_draw_hidden_field($name, $category['id'], 'data-name="'. functions::escape_html($category['name']) .'"') . PHP_EOL
               . '  <div style="flex-grow: 1;">' . functions::draw_fonticon('fa-folder', 'style="color: #cccc66;"') .' '. implode(' &gt; ', $path) .'</div>'. PHP_EOL
               . '  <div><button class="remove btn btn-default btn-sm" type="button">'. language::translate('title_remove', 'Remove') .'</button></div>' . PHP_EOL
               .'</li>';
      }
    }

    $html .= '    </ul>' . PHP_EOL
           . '  </div>' . PHP_EOL
           . '  <div class="dropdown">' . PHP_EOL
           . '  '. form_draw_search_field('', '', 'autocomplete="off" placeholder="'. functions::escape_html(language::translate('text_search_categories', 'Search categories')) .'&hellip;"') . PHP_EOL
           . '    <ul class="dropdown-menu" style="padding: 1em; right: 0; max-height: 480px; overflow-y: auto;"></ul>' . PHP_EOL
           . '  </div>' . PHP_EOL
           . '</div>';

    document::$snippets['javascript']['category-picker'] = '$(\'[data-toggle="category-picker"]\').categoryPicker({' . PHP_EOL
                                                         . '  inputName: "'. $name .'",' . PHP_EOL
                                                         . '  link: "'. document::link(WS_DIR_ADMIN, ['app' => 'catalog', 'doc' => 'categories.json']) .'",' . PHP_EOL
                                                         . '  icons: {' . PHP_EOL
                                                         . '    folder: \''. functions::draw_fonticon('fa-folder', 'style="color: #cccc66;"') .'\',' . PHP_EOL
                                                         . '    back: \''. functions::draw_fonticon('fa-arrow-left') .'\'' . PHP_EOL
                                                         . '  },' . PHP_EOL
                                                         . '  translations: {' . PHP_EOL
                                                         . '    search_results: "'. language::translate('title_search_results', 'Search Results') .'",' . PHP_EOL
                                                         . '    root: "'. language::translate('title_root', 'Root') .'",' . PHP_EOL
                                                         . '    add: "'. language::translate('title_add', 'Add') .'",' . PHP_EOL
                                                         . '    remove: "'. language::translate('title_remove', 'Remove') .'",' . PHP_EOL
                                                         . '    root: "'. language::translate('title_root', 'Root') .'"' . PHP_EOL
                                                         . '  }' . PHP_EOL
                                                         . '});';

    return $html;
  }

  function form_draw_countries_list($name, $input=true, $multiple=false, $parameters='') {

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
      $options[] = [$currency['name'], $currency['code'], 'data-value="'. (float)$currency['value'] .'" data-decimals="'. (int)$currency['decimals'] .'" data-prefix="'. functions::escape_html($currency['prefix']) .'" data-suffix="'. functions::escape_html($currency['suffix']) .'"'];
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
      "select id, email, company, firstname, lastname from ". DB_TABLE_PREFIX ."customers
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
      "select ds.id, dsi.name , dsi.description from ". DB_TABLE_PREFIX ."delivery_statuses ds
      left join ". DB_TABLE_PREFIX ."delivery_statuses_info dsi on (dsi.delivery_status_id = ds.id and dsi.language_code = '". database::input(language::$selected['code']) ."')
      order by dsi.name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($query)) {
      $options[] = [$row['name'], $row['id'], 'title="'. functions::escape_html($row['description']) .'"'];
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

  function form_draw_files_list($name, $glob, $input=true, $parameters='') {

    $options = [];

    foreach (glob(FS_DIR_APP . $glob) as $file) {
      $file = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file);
      if (is_dir(FS_DIR_APP . $file)) {
        $options[] = [basename($file).'/', $file.'/'];
      } else {
        $options[] = [basename($file), $file];
      }
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['-- '. language::translate('title_select', 'Select') . ' --', '']);
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_geo_zones_list($name, $input=true, $multiple=false, $parameters='') {

    $geo_zones_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."geo_zones
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
      $options[] = [$class['unit'], $class['unit'], 'data-value="'. (float)$class['value'] .'" data-decimals="'. (int)$class['decimals'] .'" title="'. functions::escape_html($class['name']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_manufacturers_list($name, $input=true, $multiple=false, $parameters='') {

    $manufacturers_query = database::query(
      "select id, name from ". DB_TABLE_PREFIX ."manufacturers
      order by name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($manufacturer = database::fetch($manufacturers_query)) {
      $options[] = [$manufacturer['name'], $manufacturer['id']];
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
      "select os.id, osi.name from ". DB_TABLE_PREFIX ."order_statuses os
      left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = os.id and osi.language_code = '". database::input(language::$selected['code']) ."')
      order by field(state, 'created', 'on_hold', 'ready', 'delayed', 'processing', 'completed', 'dispatched', 'in_transit', 'delivered', 'returning', 'returned', 'cancelled', ''), os.priority, osi.name asc;"
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

    $iterator = function($parent_id, $level) use (&$iterator) {

      $options = [];

      if (empty($parent_id)) $options[] = ['['.language::translate('title_root', 'Root').']', '0'];

      $pages_query = database::query(
        "select p.id, pi.title from ". DB_TABLE_PREFIX ."pages p
        left join ". DB_TABLE_PREFIX ."pages_info pi on (pi.page_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        where p.parent_id = '". (int)$parent_id ."'
        order by p.priority asc, pi.title asc;"
      );

      while ($page = database::fetch($pages_query)) {

        $options[] = [str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $page['title'], $page['id']];

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

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    $options = array_merge($options, $iterator(0, 1));

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_payment_modules_list($name, $input=true, $multiple=true, $parameters='') {

    $modules_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."modules
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

  function form_draw_product_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    $product_name = '('. language::translate('title_no_product', 'No Product') .')';

    if (!empty($value)) {
      $product_query = database::query(
        "select p.id, p.sku, pp.price, pi.name
        from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        left join (
          select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
          from ". DB_TABLE_PREFIX ."products_prices
        ) pp on (pp.product_id = p.id)
        where p.id = ". (int)$value ."
        limit 1;"
      );

      $product = database::fetch($product_query);
    }

    functions::draw_lightbox();

    return '<div class="input-group"'. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL
         . '  <div class="form-control">' . PHP_EOL
         . '    ' . form_draw_hidden_field($name, true, !empty($product) ? 'data-sku="'. $product['sku'] .'" data-price="'. $product['price'] .'"' : '') . PHP_EOL
         . '    <span class="name" style="display: inline-block;">'. (!empty($product) ? $product['name'] : '('. language::translate('title_empty', 'Empty') .')') .'</span>' . PHP_EOL
         . '    [<span class="id" style="display: inline-block;">'. (int)$value .'</span>]' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '  <div style="align-self: center;">' . PHP_EOL
         . '    <a href="'. document::href_link(WS_DIR_ADMIN, ['app' => 'catalog', 'doc' => 'product_picker']) .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. language::translate('title_change', 'Change') .'</a>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '</div>';
  }

  function form_draw_products_list($name, $input=true, $multiple=false, $parameters='') {

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    $products_query = database::query(
      "select p.*, pi.name from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."products_info pi on (p.id = pi.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
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
      "select qu.*, qui.name, qui.description from ". DB_TABLE_PREFIX ."quantity_units qu
      left join ". DB_TABLE_PREFIX ."quantity_units_info qui on (qui.quantity_unit_id = qu.id and language_code = '". database::input(language::$selected['code']) ."')
      order by qu.priority, qui.name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($quantity_unit = database::fetch($quantity_units_query)) {
      $options[] = [$quantity_unit['name'], $quantity_unit['id'], 'data-separate="'. (!empty($quantity_unit['separate']) ? 'true' : 'false') .'" data-decimals="'. (int)$quantity_unit['decimals'] .'" title="'. functions::escape_html($quantity_unit['description']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_shipping_modules_list($name, $input=true, $multiple=true, $parameters='') {

    $modules_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."modules
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
      "select sos.id, sosi.name, sosi.description from ". DB_TABLE_PREFIX ."sold_out_statuses sos
      left join ". DB_TABLE_PREFIX ."sold_out_statuses_info sosi on (sosi.sold_out_status_id = sos.id and sosi.language_code = '". database::input(language::$selected['code']) ."')
      order by sosi.name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($row = database::fetch($query)) {
      $options[] = [$row['name'], $row['id'], 'title="'. functions::escape_html($row['description']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_suppliers_list($name, $input=true, $multiple=false, $parameters='') {

    $suppliers_query = database::query(
      "select id, name, description from ". DB_TABLE_PREFIX ."suppliers
      order by name;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($supplier = database::fetch($suppliers_query)) {
      $options[] = [$supplier['name'], $supplier['id'], 'title="'. functions::escape_html($supplier['description']) .'"'];
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
      "select * from ". DB_TABLE_PREFIX ."tax_classes
      order by name asc;"
    );

    $options = [];

    if (empty($multiple)) $options[] = ['-- '. language::translate('title_select', 'Select') . ' --', ''];

    while ($tax_class = database::fetch($tax_classes_query)) {
      $options[] = [$tax_class['name'], $tax_class['id'], 'title="'. functions::escape_html($tax_class['description']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_templates_list($type, $name, $input=true, $multiple=false, $parameters='') {

    $folders = glob(FS_DIR_APP . 'includes/templates/*.'. $type);

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
      "select id, username from ". DB_TABLE_PREFIX ."users
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
      $options[] = [$class['unit'], $class['unit'], 'data-value="'. (float)$class['value'] .'" data-decimals="'. (int)$class['decimals'] .'" title="'. functions::escape_html($class['name']) .'"'];
    }

    if ($multiple) {
      return form_draw_select_multiple_field($name, $options, $input, $parameters);
    } else {
      return form_draw_select_field($name, $options, $input, $parameters);
    }
  }

  function form_draw_zones_list($country_code, $name, $input=true, $multiple=false, $parameters='', $preamble='none') {

    if (empty($country_code)) $country_code = 'default_country_code';

    switch ($country_code) {
      case 'customer_country_code':
        $country_code = customer::$data['country_code'];
        break;
      case 'default_country_code':
        $country_code = settings::get('default_country_code');
        break;
      case 'store_country_code':
        $country_code = settings::get('store_country_code');
        break;
    }

    $zones_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."zones
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
