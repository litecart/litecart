<?php

  function form_draw_form_begin($name='', $method='post', $action=false, $multipart=false, $parameters='') {
    return  '<form'. (($name) ? ' name="'. htmlspecialchars($name) .'"' : false) .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'" accept-charset="'. language::$selected['charset'] .'"'. (($action) ? ' action="'. htmlspecialchars($action) .'"' : '') . (($parameters) ? ' ' . $parameters : false) .'>'. PHP_EOL
          . ((strtolower($method) == 'post') ? form_draw_hidden_field('token', form::session_post_token()) . PHP_EOL : '');
  }

  function form_draw_protected_form_begin($name='', $method='post', $action=false, $multipart=false, $parameters=false) {

    document::$snippets['javascript'][] = "  $('form[name=\"". $name ."\"]').on('change keyup keydown', ':input', function(){" . PHP_EOL
                                        . "    $(this).addClass('unsaved');" . PHP_EOL
                                        . "  });" . PHP_EOL
                                        . "  $('form').submit(function() {" . PHP_EOL
                                        . "    $(this).find('.changed-input').each(function(){" . PHP_EOL
                                        . "      $(this).removeClass('unsaved');" . PHP_EOL
                                        . "    });" . PHP_EOL
                                        . "  });" . PHP_EOL
                                        . "  $(window).on('beforeunload', function(){" . PHP_EOL
                                        . "    if ($('.unsaved').length) return '". htmlspecialchars(language::translate('warning_unsaved_changes', 'There are unsaved changes, do you wish to continue?')) ."';" . PHP_EOL
                                        . "  });";

    return form_draw_form_begin($name, $method, $action, $multipart, $parameters);
  }

  function form_draw_form_end() {
    return '</form>' . PHP_EOL;
  }

  function form_reinsert_value($name, $array_value=null) {
    if (empty($name)) return;

    foreach (array($_POST, $_GET) as $superglobal) {
      if (empty($superglobal)) continue;

      foreach (explode('&', http_build_query($superglobal)) as $pair) {

        @list($key, $value) = explode('=', $pair);
        $key = urldecode($key);
        $value = urldecode($value);

        if ($key == $name) return $value;

        if (preg_replace('/(.*)\[([^\]]+)?\]$/', "$1", $key) == preg_replace('/(.*)\[([^\]]+)?\]$/', "$1", $name)) {
          if (preg_match('/\[([0-9]+)?\]$/', $key)) {
            if ($value == $array_value) {
              return $value;
            }
          }
        }
      }
    }

    return '';
  }

  function form_draw_button($name, $value, $type='submit', $parameters='', $icon='') {

    if (!empty($icon)) {
      switch($icon) {
        case 'add':
          $icon = functions::draw_fonticon('fa-plus', 'style="color: #66cc66;"');
          break;
        case 'cancel':
          $icon = functions::draw_fonticon('fa-times');
          break;
        case 'remove':
        case 'delete':
          $icon = functions::draw_fonticon('fa-trash-o');
          break;
        case 'on':
          $icon = functions::draw_fonticon('fa-circle', 'style="font-size: 0.75em; color: #99cc66;"');
          break;
        case 'off':
          $icon = functions::draw_fonticon('fa-circle', 'style="font-size: 0.75em; color: #ff6666;"');
          break;
        case 'save':
          $icon = functions::draw_fonticon('fa-floppy-o');
          break;
        default:
          $icon = functions::draw_fonticon($icon);
      }
    }

    if (is_array($value)) {
      list($value, $title) = $value;
    } else {
      $title = $value;
    }

    return '<button '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. ((!empty($icon)) ? $icon . ' ' : '') . $title .'</button>';
  }

  function form_draw_captcha_field($name, $id, $parameters='') {

    $output = '<div class="input-group">' . PHP_EOL
            . '  <span class="input-group-addon">'. functions::captcha_generate(100, 40, 4, $id, 'numbers', 'align="absbottom"') .'</span>' . PHP_EOL
            . '  ' . functions::form_draw_text_field('captcha', '', $parameters . ' style="font-size: 24px; text-align: center;"') . PHP_EOL
            . '</div>';

    return $output;
  }

  function form_draw_checkbox($name, $value, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name, $value);

    return '<input type="checkbox" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" '. ($input === $value ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
  }

  function form_draw_color_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="color" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="color" '. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_currency_field($currency_code, $name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (empty($currency_code)) $currency_code = settings::get('store_currency_code');

    document::$snippets['javascript']['input-currency-replace-decimal'] = '  $(document).ready(function(){' . PHP_EOL
                                                                        . '    $("body").on("change", "input[data-type=\'currency\']", function(){' . PHP_EOL
                                                                        . '      $(this).val($(this).val().replace(",", "."));' . PHP_EOL
                                                                        . '    });' . PHP_EOL
                                                                        . '  });';

    return '<div class="input-group">' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="text" name="'. htmlspecialchars($name) .'" value="'. (!empty($value) ? round($value, currency::$currencies[$currency_code]['decimals']+2) : '') .'" data-type="currency"'. (($parameters) ? ' '. $parameters : false) .' />' . PHP_EOL
         . '  <strong class="input-group-addon" style="opacity: 0.75;">'. $currency_code .'</strong>' . PHP_EOL
         . '</div>';
  }

  function form_draw_date_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!in_array(substr($value, 0, 10), array('', '0000-00-00', '1970-00-00', '1970-01-01'))) {
      $value = date('Y-m-d', strtotime($value));
    } else {
      $value = '';
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="date" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="date" maxlength="10" pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_datetime_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!in_array(substr($value, 0, 10), array('', '0000-00-00', '1970-00-00', '1970-01-01'))) {
      $value = date('Y-m-d\TH:i', strtotime($value));
    } else {
      $value = '';
    }

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="datetime-local" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="datetime" maxlength="16" pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(.[0-9]{2}:[0-9]{2})?" placeholder="YYYY-MM-DD [hh:nn]"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_decimal_field($name, $value=true, $decimals=2, $min=null, $max=null, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    $value = number_format((float)$value, (int)$decimals, '.', '');

    document::$snippets['javascript']['input-decimal-replace-decimal'] = '  $(\'body\').on(\'change\', \'input[data-type="decimal"]\', function(){' . PHP_EOL
                                                                       . '    $(this).val($(this).val().replace(\',\', \'.\'));' . PHP_EOL
                                                                       . '  });';

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="number" name="'. htmlspecialchars($name) .'" value="'. $value .'" data-type="decimal" step="any" '. (($min !== null) ? 'min="'. (float)$min .'"' : false) . (($max !== null) ? ' max="'. (float)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_email_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon">'. functions::draw_fonticon('fa-envelope-o fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="email" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="email"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_file_field($name, $parameters='') {

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="file" name="'. htmlspecialchars($name) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_fonticon_field($name, $value=true, $type, $icon, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon">'. functions::draw_fonticon($icon) .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />' . PHP_EOL
         . '</div>';
  }

  function form_draw_hidden_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input type="hidden" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_image($name, $src, $parameters=false) {
    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="image" name="'. htmlspecialchars($name) .'" src="'. htmlspecialchars($src) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_input($name, $value=true, $type='text', $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_link_button($url, $title, $parameters='', $icon='') {
    if (!empty($icon)) {
      switch($icon) {
        case 'add':
          $icon = functions::draw_fonticon('fa-plus', 'style="color: #66cc66;"');
          break;
        case 'cancel':
          $icon = functions::draw_fonticon('fa-times');
          break;
        case 'delete':
          $icon = functions::draw_fonticon('fa-trash-o');
          break;
        case 'on':
          $icon = functions::draw_fonticon('fa-circle', 'style="font-size: 0.75em; color: #99cc66;"');
          break;
        case 'off':
          $icon = functions::draw_fonticon('fa-circle', 'style="font-size: 0.75em; color: #ff6666;"');
          break;
        case 'save':
          $icon = functions::draw_fonticon('fa-floppy-o');
          break;
        default:
          $icon = functions::draw_fonticon($icon);
      }
    }

    return '<a '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default"' : '') .' href="'. htmlspecialchars($url) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. ((!empty($icon)) ? $icon . ' ' : false) . $title .'</a>';
  }

  function form_draw_month_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    if (!in_array(substr($value, 0, 7), array('', '0000-00', '1970-00', '1970-01'))) {
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
         . '  <span class="input-group-addon">'. functions::draw_fonticon('fa-key fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="password" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="password"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_phone_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon">'. functions::draw_fonticon('fa-phone fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="tel" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="phone" pattern="^\+?([0-9]|-| )+$"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_radio_button($name, $value, $input=true, $parameters='') {
    if ($input === true) $input = form_reinsert_value($name, $value);

    return '<input type="radio" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" '. ($input === $value ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
  }

  function form_draw_range_slider($name, $value=true, $min='', $max='', $step='', $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="range" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="range" min="'. (float)$min .'" max="'. (float)$max .'" step="'. (float)$step .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_regional_input_field($language_code, $name, $value=true, $parameters='') {
    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon"><img src="'. WS_DIR_IMAGES .'languages/'. $language_code .'.png" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_text_field($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_textarea($language_code, $name, $value=true, $parameters='') {

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon" style="vertical-align: top;"><img src="'. WS_DIR_IMAGES .'languages/'. $language_code .'.png" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_textarea($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_regional_wysiwyg_field($language_code, $name, $value=true, $parameters='') {

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon" style="vertical-align: top;"><img src="'. WS_DIR_IMAGES .'languages/'. $language_code .'.png" width="16" alt="'. $language_code .'" style="vertical-align: middle;" /></span>' . PHP_EOL
         . '  ' . form_draw_wysiwyg_field($name, $value, $parameters) . PHP_EOL
         . '</div>';
  }

  function form_draw_search_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon">'. functions::draw_fonticon('fa-search fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="search" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="search"'. (($parameters) ? ' '.$parameters : false) .' />' . PHP_EOL
         . '</div>';
  }

  function form_draw_select_optgroup_field($name, $groups=array(), $input=true, $multiple=false, $parameters='') {
    if (!is_array($groups)) $groups = array($groups);

    $html = '<select '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' name="'. htmlspecialchars($name) .'"'. (($multiple) ? ' multiple="multiple"' : false) .''. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;

    foreach ($groups as $group) {
      $html .= '  <optgroup label="'. $group['label'] .'">' . PHP_EOL;
      foreach ($group['options'] as $option) {
        if ($input === true) {
          $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
        } else {
          $option_input = $input;
        }
        $html .= '    <option value="'. htmlspecialchars(isset($option[1]) ? $option[1] : $option[0]) .'"'. (isset($option[1]) ? (($option[1] == $option_input) ? ' selected="selected"' : false) : (($option[0] == $option_input) ? ' selected="selected"' : false)) . ((isset($option[2])) ? ' ' . $option[2] : false) . '>'. $option[0] .'</option>' . PHP_EOL;
      }
      $html .= '  </optgroup>' . PHP_EOL;
    }

    $html .= '</select>';

    return $html;
  }

  function form_draw_select_field($name, $options=array(), $input=true, $multiple=false, $parameters='') {

    if (!is_array($options)) $options = array($options);

    $html = '<select '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' name="'. htmlspecialchars($name) .'"'. (($multiple) ? ' multiple="multiple"' : false) .''. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;

    foreach ($options as $option) {
      if ($input === true) {
        $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
      } else {
        $option_input = $input;
      }
      $html .= '  <option value="'. htmlspecialchars(isset($option[1]) ? $option[1] : $option[0]) .'"'. (isset($option[1]) ? (($option[1] == $option_input) ? ' selected="selected"' : false) : (($option[0] == $option_input) ? ' selected="selected"' : false)) . ((isset($option[2])) ? ' ' . $option[2] : false) . '>'. $option[0] .'</option>' . PHP_EOL;
    }

    $html .= '</select>';

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

  function form_draw_toggle($name, $input=true, $type='e/d', $parameters='') {
    if ($input === true) $input = form_reinsert_value($name);

    $input = in_array(strtolower($input), array('1', 'active', 'enabled', 'on', 'true', 'yes')) ? '1' : '0';

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

    return '<div>' . PHP_EOL
         . '  <div class="btn-group btn-block btn-group-inline" data-toggle="buttons">'. PHP_EOL
         . '    <label '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default'. (($input == '1') ? ' active' : '') .'"' : '') .'><input type="radio" name="'. htmlspecialchars($name) .'" value="1" data-type="toggle" '. (($input == '1') ? 'checked="checked"' : '') .' /> '. $true_text .'</label>'. PHP_EOL
         . '    <label '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default'. (($input == '0') ? ' active' : '') .'"' : '') .'><input type="radio" name="'. htmlspecialchars($name) .'" value="0" data-type="toggle" '. (($input == '0') ? 'checked="checked"' : '') .' /> '. $false_text .'</label>' . PHP_EOL
         . '  </div>' . PHP_EOL
         . '</div>';
  }

  function form_draw_time_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="time" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="time"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_url_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="url" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="url"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_username_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);

    return '<div class="input-group">' . PHP_EOL
         . '  <span class="input-group-addon">'. functions::draw_fonticon('fa-user fa-fw') .'</span>' . PHP_EOL
         . '  <input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-control"' : '') .' type="text" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="text"'. (($parameters) ? ' '.$parameters : false) .' />'
         . '</div>';
  }

  function form_draw_wysiwyg_field($name, $value=true, $parameters='') {

    if ($value === true) $value = form_reinsert_value($name);

    document::$snippets['head_tags']['trumbowyg'] = '<link href="'. WS_DIR_EXT .'trumbowyg/ui/trumbowyg.min.css" rel="stylesheet" />' . PHP_EOL
                                                  . '<link href="'. WS_DIR_EXT .'trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css" rel="stylesheet" />';

    document::$snippets['foot_tags']['trumbowyg'] = '<script src="'. WS_DIR_EXT .'trumbowyg/trumbowyg.min.js"></script>' . PHP_EOL
                                                  . ((language::$selected['code'] != 'en') ? '<script src="'. WS_DIR_EXT .'trumbowyg/langs/'. language::$selected['code'] .'.min.js"></script>' . PHP_EOL : '')
                                                  . '<script src="'. WS_DIR_EXT .'trumbowyg/plugins/base64/trumbowyg.base64.min.js"></script>' . PHP_EOL
                                                  . '<script src="'. WS_DIR_EXT .'trumbowyg/plugins/colors/trumbowyg.colors.min.js"></script>';

    document::$snippets['javascript'][] = "  $('textarea[name=\"". $name ."\"]').trumbowyg({" . PHP_EOL
                                        . "    lang: '". language::$selected["code"] ."'," . PHP_EOL
                                        . "    btnsDef: {" . PHP_EOL
                                        . "      image: {" . PHP_EOL
                                        . "       dropdown: ['insertImage', 'base64']," . PHP_EOL
                                        . "       ico: 'insertImage'" . PHP_EOL
                                        . "      }" . PHP_EOL
                                        . "    }," . PHP_EOL
                                        . "    semantic: false," . PHP_EOL
                                        . "    removeformatPasted: true," . PHP_EOL
                                        . "    btns: [['viewHTML'], ['formatting'], 'btnGrp-design', ['link'], ['image'], ['justifyLeft', 'justifyCenter', 'justifyRight'], 'btnGrp-lists', ['foreColor', 'backColor'], ['preformatted'], ['horizontalRule'], ['fullscreen']]" . PHP_EOL
                                        . "  });";

    return '<textarea name="'. htmlspecialchars($name) .'" data-type="wysiwyg"'. (($parameters) ? ' '.$parameters : false) .'>'. htmlspecialchars($value) .'</textarea>';
  }

  ######################################################################

  function form_draw_function($function, $name, $input=true) {

    preg_match('/(\w*)(?:\()(.*?)(?:\))/i', $function, $matches);

    if (!isset($matches[1])) trigger_error('Invalid function name ('. $function .')', E_USER_ERROR);

    $options = array();
    if (isset($matches[2])) {
      $options = explode(',', $matches[2]);
      for ($i=0; $i<count($options); $i++) {
        $options[$i] = trim($options[$i], '\'" ');
      }
    }

    switch ($matches[1]) {
      case 'decimal':
      case 'float':
        return functions::form_draw_decimal_field($name, $input, 2);
      case 'number':
      case 'int':
        return functions::form_draw_number_field($name, $input);
      case 'color':
        return functions::form_draw_color_field($name, $input);
      case 'currency':
        return functions::form_draw_currency_field(!empty($options[0]) ? $options[0] : null, $name, $input);
      case 'smallinput':
        return functions::form_draw_input($name, $input, 'text');
      case 'input':
        return functions::form_draw_input($name, $input, 'text');
      case 'password':
        return functions::form_draw_input($name, $input, 'password');
      case 'smalltext':
        return functions::form_draw_textarea($name, $input, 'rows="2"');
      case 'mediumtext':
        return functions::form_draw_textarea($name, $input, 'rows="5"');
      case 'bigtext':
        return functions::form_draw_textarea($name, $input, 'rows="10"');
      case 'category':
      case 'categories':
        return functions::form_draw_categories_list($name, $input);
      case 'customer':
      case 'customers':
        return functions::form_draw_customers_list($name, $input);
      case 'country':
      case 'countries':
        return functions::form_draw_countries_list($name, $input);
      case 'currency':
      case 'currencies':
        return functions::form_draw_currencies_list($name, $input);
      case 'delivery_status':
      case 'delivery_statuses':
        return functions::form_draw_delivery_statuses_list($name, $input);
      case 'geo_zone':
      case 'geo_zones':
        return functions::form_draw_geo_zones_list($name, $input);
      case 'language':
      case 'languages':
        return functions::form_draw_languages_list($name, $input);
      case 'length_class':
      case 'length_classes':
        return functions::form_draw_length_classes_list($name, $input);
      case 'product':
      case 'products':
        return functions::form_draw_products_list($name, $input);
      case 'quantity_unit':
      case 'quantity_units':
        return functions::form_draw_quantity_units_list($name, $input);
      case 'order_status':
      case 'order_statuses':
        return functions::form_draw_order_status_list($name, $input);
      case 'page':
      case 'pages':
        return functions::form_draw_pages_list($name, $input);
      case 'radio':
        $output = '';
        for ($i=0; $i<count($options); $i++) {
          $output .= '<div class="radio"><label>'. form_draw_radio_button($name, $options[$i], $input) .' '. $options[$i] .'</label></div>';
        }
        return $output;
      case 'select':
        for ($i=0; $i<count($options); $i++) $options[$i] = array($options[$i]);
        return functions::form_draw_select_field($name, $options, $input, false);
      case 'timezone':
      case 'timezones':
        return functions::form_draw_timezones_list($name, $input);
      case 'template':
      case 'templates':
        return functions::form_draw_templates_list($name, $input);
      case 'toggle':
        return functions::form_draw_toggle($name, $input, !empty($options[0]) ? $options[0] : null);
      case 'sold_out_status':
      case 'sold_out_statuses':
        return functions::form_draw_sold_out_statuses_list($name, $input);
      case 'tax_class':
      case 'tax_classes':
        return functions::form_draw_tax_classes_list($name, $input);
      case 'weight_class':
      case 'weight_classes':
        return functions::form_draw_weight_classes_list($name, $input);
      case 'zone':
      case 'zones':
        $option = !empty($options) ? $options[0] : '';
        //if (empty($option)) $option = settings::get('store_country_code');
        return functions::form_draw_zones_list($option, $name, $input);
      default:
        trigger_error('Unknown function name ('. $function .')', E_USER_ERROR);
    }
  }

  function form_draw_categories_list($name, $input=true, $multiple=false, $parameters=false) {

    if (!function_exists('form_draw_categories_list_options_iterator')) {
      function form_draw_categories_list_options_iterator($parent_id = 0, $level = 1) {

        $options = array();

        if ($parent_id == 0) $options[] = array('['.language::translate('title_root', 'Root').']');

        $categories_query = database::query(
          "select c.id, ci.name
          from ". DB_TABLE_CATEGORIES ." c
          left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
          where parent_id = '". (int)$parent_id ."'
          order by c.priority asc, ci.name asc;"
        );

        while ($category = database::fetch($categories_query)) {

          $options[] = array(str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $category['name'], $category['id']);

          $sub_categories_query = database::query(
            "select id
            from ". DB_TABLE_CATEGORIES ." c
            where parent_id = '". (int)$category['id'] ."'
            limit 1;"
          );

          $sub_options = form_draw_categories_list_options_iterator($category['id'], $level+1);

          $options = array_merge($options, $sub_options);
        }

        return $options;
      }
    }

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    $options = array_merge($options, form_draw_categories_list_options_iterator());

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_countries_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);
    if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_country_code');

    $countries_query = database::query(
      "select * from ". DB_TABLE_COUNTRIES ."
      where status
      order by name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($country = database::fetch($countries_query)) {
      $options[] = array($country['name'], $country['iso_code_2'], 'data-tax-id-format="'. $country['tax_id_format'] .'" data-postcode-format="'. $country['postcode_format'] .'" data-phone-code="'. $country['phone_code'] .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_currencies_list($name, $input=true, $multiple=false, $parameters='') {

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    foreach (currency::$currencies as $currency) {
      $options[] = array($currency['name'], $currency['code'], 'data-value="'. (float)$currency['value'] .'" data-decimals="'. (int)$currency['decimals'] .'" data-prefix="'. htmlspecialchars($currency['prefix']) .'" data-suffix="'. htmlspecialchars($currency['suffix']) .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_customers_list($name, $input=true, $multiple=false, $parameters='') {

    if (empty(user::$data['id'])) trigger_error('Must be logged in to use form_draw_customers_list()', E_USER_ERROR);

    if ($input === true) $input = form_reinsert_value($name);

    $options = array();

    $customers_query = database::query(
      "select id, email, company, firstname, lastname from ". DB_TABLE_CUSTOMERS ."
      order by email;"
    );

    while($customer = database::fetch($customers_query)) {
      $options[] = array($customer['email'], $customer['id']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_delivery_statuses_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);
    if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_delivery_status_id');

    $query = database::query(
      "select ds.id, dsi.name , dsi.description from ". DB_TABLE_DELIVERY_STATUSES ." ds
      left join ". DB_TABLE_DELIVERY_STATUSES_INFO ." dsi on (dsi.delivery_status_id = ds.id and dsi.language_code = '". database::input(language::$selected['code']) ."')
      order by dsi.name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($row = database::fetch($query)) {
      $options[] = array($row['name'], $row['id'], 'title="'. htmlspecialchars($row['description']) .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_encodings_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    $encodings = array(
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
    );

    foreach ($encodings as $encoding) {
      $options[] = array($encoding);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_geo_zones_list($name, $input=true, $multiple=false, $parameters='') {

    $geo_zones_query = database::query(
      "select * from ". DB_TABLE_GEO_ZONES ."
      order by name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    if (database::num_rows($geo_zones_query) == 0) {
      return form_draw_select_field($name, $options, $input, false, false, $parameters . ' disabled="disabled"');
    }

    while ($geo_zone = database::fetch($geo_zones_query)) {
      $options[] = array($geo_zone['name'], $geo_zone['id']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_google_taxonomy_categories_list($name, $input=true, $multiple=false, $parameters='') {

    $cache_id = cache::cache_id('google_taxonomy_categories', array('language'));

    if (!$response = cache::get($cache_id, 'file', 2592000, true)) {
      $response = file_get_contents('http://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt');

      if (empty($response)) return functions::form_draw_number_field($name, $input);

      $response = preg_replace('/^(\#.*\R)$/', '', $response);
      cache::set($cache_id, 'file', $response);
    }

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    foreach (preg_split('#\R#', $response) as $row) {
      if (preg_match('#^([0-9]+) - (.*)$#', $row, $matches)) {
        $options[] = array($matches[2], $matches[1]);
      }
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_languages_list($name, $input=true, $multiple=false, $parameters='') {

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    foreach (language::$languages as $language) {
      $options[] = array($language['name'], $language['code']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_length_classes_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);
    if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('store_length_class');

    $options = array();

    foreach (length::$classes as $class) {
      $options[] = array($class['unit'], $class['unit'], 'data-value="'. (float)$class['value'] .'" data-decimals="'. (int)$class['decimals'] .'" title="'. htmlspecialchars($class['name']) .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_manufacturers_list($name, $input=true, $multiple=false, $parameters='') {

    $manufacturers_query = database::query(
      "select id, name from ". DB_TABLE_MANUFACTURERS ."
      order by name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($manufacturer = database::fetch($manufacturers_query)) {
      $options[] = array($manufacturer['name'], $manufacturer['id']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_mysql_collations_list($name, $input=true, $multiple=false, $parameters='') {

    $collations_query = database::query(
      "SHOW COLLATION;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($row = database::fetch($collations_query)) {
      $options[] = array($row['Collation'], $row['Collation']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_option_groups_list($name, $input=true, $multiple=false, $parameters='') {

    $option_groups_query = database::query(
      "select pcg.id, pcg.function, pcg.required, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
      left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". database::input(language::$selected['code']) ."')
      order by pcgi.name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($option_group = database::fetch($option_groups_query)) {
      $options[] = array($option_group['name'] .' ['. $option_group['function'] .']', $option_group['id']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_option_values_list($group_id, $name, $input=true, $multiple=false, $parameters='') {

    $option_values_query = database::query(
      "select pcv.id, pcv.value, pcvi.name from ". DB_TABLE_OPTION_VALUES ." pcv
      left join ". DB_TABLE_OPTION_VALUES_INFO ." pcvi on (pcvi.value_id = pcv.id and pcvi.language_code = '". database::input(language::$selected['code']) ."')
      where pcv.group_id = '". (int)$group_id ."'
      order by pcvi.name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($option_value = database::fetch($option_values_query)) {
      if (empty($option_value['name'])) $option_value['name'] = $option_value['value'];
      if (empty($option_value['name'])) $option_value['name'] = '('. language::translate('text_user_input', 'User input') .')';
      $options[] = array($option_value['name'], $option_value['id']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_order_status_list($name, $input=true, $multiple=false, $parameters='') {

    $query = database::query(
      "select os.id, osi.name from ". DB_TABLE_ORDER_STATUSES ." os
      left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = os.id and osi.language_code = '". database::input(language::$selected['code']) ."')
      order by priority, name;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($row = database::fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_pages_list($name, $input=true, $multiple=false, $parameters='') {

    $query = database::query(
      "select p.id, pi.name from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (pi.delivery_status_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where p.status
      order by p.priority, pi.name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($row = database::fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_payment_modules_list($name, $input=true, $multiple=true, $parameters='') {

    $payment = new mod_payment();

    $options = array();

    foreach ($payment->modules as $module) {
      $options[] = array($module->name, $module->id);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_products_list($name, $input=true, $multiple=false, $parameters='') {

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    $products_query = database::query(
      "select p.*, pi.name from ". DB_TABLE_PRODUCTS ." p
      left join ". DB_TABLE_PRODUCTS_INFO ." pi on (p.id = pi.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      order by pi.name"
    );

    while ($product = database::fetch($products_query)) {
      $options[] = array($product['name'] .' &mdash; '. $product['sku'] . ' ['. (float)$product['quantity'] .']', $product['id']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_quantity_units_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);
    if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_quantity_unit_id');

    $quantity_units_query = database::query(
      "select qu.*, qui.name, qui.description from ". DB_TABLE_QUANTITY_UNITS ." qu
      left join ". DB_TABLE_QUANTITY_UNITS_INFO ." qui on (qui.quantity_unit_id = qu.id and language_code = '". language::$selected['code'] ."')
      order by qu.priority, qui.name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($quantity_unit = database::fetch($quantity_units_query)) {
      $options[] = array($quantity_unit['name'], $quantity_unit['id'], 'data-separate="'. (!empty($quantity_unit['separate']) ? 'true' : 'false') .'" data-decimals="'. (int)$quantity_unit['decimals'] .'" title="'. htmlspecialchars($quantity_unit['description']) .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_shipping_modules_list($name, $input=true, $multiple=true, $parameters='') {

    $shipping = new mod_shipping();

    $options = array();

    foreach ($shipping->modules as $module) {
      $options[] = array($module->name, $module->id);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_sold_out_statuses_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);
    if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_sold_out_status_id');

    $query = database::query(
      "select sos.id, sosi.name, sosi.description from ". DB_TABLE_SOLD_OUT_STATUSES ." sos
      left join ". DB_TABLE_SOLD_OUT_STATUSES_INFO ." sosi on (sosi.sold_out_status_id = sos.id and sosi.language_code = '". database::input(language::$selected['code']) ."')
      order by sosi.name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($row = database::fetch($query)) {
      $options[] = array($row['name'], $row['id'], 'title="'. htmlspecialchars($row['description']) .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_suppliers_list($name, $input=true, $multiple=false, $parameters='') {

    $suppliers_query = database::query(
      "select id, name, description from ". DB_TABLE_SUPPLIERS ."
      order by name;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($supplier = database::fetch($suppliers_query)) {
      $options[] = array($supplier['name'], $supplier['id'], 'title="'. htmlspecialchars($supplier['description']) .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_tax_classes_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);
    if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('default_tax_class_id');

    $tax_classes_query = database::query(
      "select * from ". DB_TABLE_TAX_CLASSES ."
      order by name asc;"
    );

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    while ($tax_class = database::fetch($tax_classes_query)) {
      $options[] = array($tax_class['name'], $tax_class['id'], 'title="'. htmlspecialchars($tax_class['description']) .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_templates_list($type='catalog', $name, $input=true, $multiple=false, $parameters='') {

    $folders = glob(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'*.'. $type);

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    foreach($folders as $folder) {
      $options[] = array(basename($folder));
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_timezones_list($name, $input=true, $multiple=false, $parameters='') {

    $options = array();

    if (empty($multiple)) $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');

    $zones = timezone_identifiers_list();

    foreach ($zones as $zone) {
      $zone = explode('/', $zone); // 0 => Continent, 1 => City

      if (in_array($zone[0], array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'))) {
        if (!empty($zone[1])) {
          $options[] = array(implode('/', $zone));
        }
      }
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_weight_classes_list($name, $input=true, $multiple=false, $parameters='') {

    if ($input === true) $input = form_reinsert_value($name);
    if ($input == '' && file_get_contents('php://input') == '') $input = settings::get('store_weight_class');

    $options = array();

    foreach (weight::$classes as $class) {
      $options[] = array($class['unit'], $class['unit'], 'data-value="'. (float)$class['value'] .'" data-decimals="'. (int)$class['decimals'] .'" title="'. htmlspecialchars($class['name']) .'"');
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_zones_list($country_code, $name, $input=true, $multiple=false, $parameters='', $preamble='none') {

    if (empty($country_code)) $country_code = settings::get('default_country_code');

    if ($country_code == 'default_country_code') $country_code = settings::get('default_country_code');

    if ($country_code == 'store_country_code') $country_code = settings::get('store_country_code');

    $zones_query = database::query(
      "select * from ". DB_TABLE_ZONES ."
      where country_code = '". database::input($country_code) ."'
      order by name asc;"
    );

    $options = array();

    switch($preamble) {
      case 'all':
        $options[] = array('-- '. language::translate('title_all_zones', 'All Zones') . ' --', '');
        break;
      case 'select':
        $options[] = array('-- '. language::translate('title_select', 'Select') . ' --', '');
        break;
      case 'none':
        break;
    }

    if (database::num_rows($zones_query) == 0) {
      return form_draw_select_field($name, $options, $input, $multiple, $parameters . ' disabled="disabled"');
    }

    while ($zone = database::fetch($zones_query)) {
      $options[] = array($zone['name'], $zone['code']);
    }

    return functions::form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
