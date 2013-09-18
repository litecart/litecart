<?php
  
  function form_draw_form_begin($name='', $method='post', $action=false, $multipart=false, $parameters='') {
    return  '<form'. (($name) ? ' name="'. htmlspecialchars($name) .'"' : false) .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'"'. (($action) ? ' action="'. htmlspecialchars($action) .'"' : '') . (($parameters) ? ' ' . $parameters : false) .'>'. PHP_EOL
          . ((strtolower($method) == 'post') ? form_draw_hidden_field('token', $GLOBALS['system']->form->session_post_token()) . PHP_EOL : '');
  }
  
  function form_draw_form_end() {
    return '</form>' . PHP_EOL;
  }
  
  function form_reinsert_value($name, $array_value=null) {
    if (empty($name)) return;
    
    foreach (array($_POST, $_GET) as $superglobal) {
      if (empty($superglobal)) continue;
      
      foreach (explode('&', http_build_query($superglobal)) as $pair) {
        
        list($key, $value) = explode('=', $pair);
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
    return '<button type="'. (($type == 'submit') ? 'submit' : 'button') .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. ((!empty($icon)) ? '<img src="'. WS_DIR_IMAGES .'icons/16x16/'. $icon .'.png" /> ' : false) . $value .'</button>';
  }
  
  function form_draw_checkbox($name, $value, $input=true, $parameters='', $hint='') {
    if ($input === true) $input = form_reinsert_value($name, $value);
    
    return '<input type="checkbox" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" '. ($input === $value ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false) . (($hint) ? ' title="'. htmlspecialchars($hint) .'"' : false) .' />';
  }
  
  function form_draw_currency_field($currency_code, $name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="small"';
    
    if (empty($currency_code)) $currency_code = $GLOBALS['system']->settings->get('store_currency_code');
    
    return '<span class="input-wrapper">'. $GLOBALS['system']->currency->currencies[$currency_code]['prefix'] .'<input type="text" name="'. htmlspecialchars($name) .'" value="'. (!empty($value) ? number_format((float)$value, $GLOBALS['system']->currency->currencies[$currency_code]['decimals'], '.', '') : '') .'" data-type="currency" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '. $parameters : false) .' />'. $GLOBALS['system']->currency->currencies[$currency_code]['suffix'] .'</span>';
  }
  
  function form_draw_date_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (substr($value, 0, 10) == '0000-00-00') {
      $value = '';
    } else if (substr($value, 0, 10) == '1970-00-00') {
      $value = '';
    } else {
      $value = date('Y-m-d', strtotime($value));
      if (substr($value, 0, 10) == '1970-01-01') $value = '';
    }
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<img src="'. WS_DIR_IMAGES .'icons/16x16/calendar.png" width="16" height="16" /> <input type="date" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="date" maxlength="10" placeholder="YYYY-MM-DD"'. (($hint) ? ' title="'. htmlspecialchars($hint) .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_datetime_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (substr($value, 0, 16) == '0000-00-00 00:00') {
      $value = '';
    } else if (substr($value, 0, 16) == '1970-00-00 00:00') {
      $value = '';
    } else {
      $value = date('Y-m-d H:i', strtotime($value));
      if (substr($value, 0, 10) == '1970-01-01') $value = '';
    }
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="datetime" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="datetime" maxlength="16" placeholder="YYYY-MM-DD [hh:nn]" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_decimal_field($name, $value=true, $decimals=2, $min=0, $max=0, $parameters='', $hint='') {
    if ($value === true) $value = round((float)form_reinsert_value($name), $decimals);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="small"';
    
    return '<input type="number" name="'. htmlspecialchars($name) .'" value="'. (float)$value .'" data-type="decimal" title="'. htmlspecialchars($hint) .'" step="any" min="'. (float)$min .'"'. (!empty($max) ? ' max="'. (float)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_email_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="email" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="email" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }

  function form_draw_file_field($name, $parameters='', $hint='') {
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="large"';
    
    return '<input type="file" name="'. htmlspecialchars($name) .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_hidden_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    return form_draw_input($name, $value, 'hidden', $parameters);
  }
  
  function form_draw_image($name, $src, $parameters=false) {
    return '<input type="hidden" name="'. htmlspecialchars($name) .'" value="true" /><input type="image" src="'. htmlspecialchars($src) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_input($name, $value=true, $type='text', $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_link_button($url, $title, $parameters='', $icon='') {
    return '<a class="button" href="'. htmlspecialchars($url) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. ((!empty($icon)) ? '<img src="'. WS_DIR_IMAGES .'icons/16x16/'. $icon .'.png" /> ' : false) . $title .'</a>';
  }
  
  function form_draw_number_field($name, $value=true, $min='', $max='', $parameters='', $hint='') {
    if ($value === true) $value = (int)form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="tiny"';
    
    return '<input type="number" name="'. htmlspecialchars($name) .'" value="'. (int)$value .'" data-type="number" title="'. htmlspecialchars($hint) .'" step="1" min="'. (float)$min .'"'. (!empty($max) ? ' max="'. (float)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_password_field($name, $value=true, $parameters='', $hint='') {
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="password" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="password" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_radio_button($name, $value, $input=true, $parameters='', $hint='') {
    if ($input === true) $input = form_reinsert_value($name, $value);
    
    return '<input type="radio" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" '. ($input === $value ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false) . (($hint) ? ' title="'. htmlspecialchars($hint) .'"' : false) .' />';
  }
  
  function form_draw_range_slider($name, $value=true, $min='', $max='', $step='', $parameters='', $hint='') {
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="range" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="range" min="'. (float)$min .'" max="'. (float)$max .'" step="'. (float)$step .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_regional_input_field($language_code, $name, $value=true, $parameters='', $hint='') {
    return '<span class="input-wrapper"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" width="16" style="vertical-align: middle;" /> '. form_draw_text_field($name, $value, $parameters, $hint) .'</span>';
  }
  
  function form_draw_regional_textarea($language_code, $name, $value=true, $parameters='', $hint='') {
    return '<span class="input-wrapper"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" width="16" style="vertical-align: top;" /> '. form_draw_textarea($name, $value, $parameters, $hint) .'</span>';
  }
  
  function form_draw_regional_wysiwyg_field($language_code, $name, $value=true, $parameters='', $hint='') {
    return '<span class="input-wrapper"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" width="16" style="vertical-align: top;" /> '. form_draw_wysiwyg_field($name, $value, $parameters, $hint) .'</span>';
  }
  
  function form_draw_search_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="search" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="search" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_select_field($name, $options=array(), $input=true, $multiple=false, $parameters='', $hint='') {
    if (!is_array($options)) $options = array($options);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    $html = '<select name="'. htmlspecialchars($name) .'"'. (($multiple) ? ' multiple="multiple"' : false) .' title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;
    
    foreach ($options as $option) {
      if ($input === true) {
        $option_input = form_reinsert_value($name, isset($option[1]) ? $option[1] : $option[0]);
      } else {
        $option_input = $input;
      }
      $html .= '<option value="'. htmlspecialchars(isset($option[1]) ? $option[1] : $option[0]) .'"'. (isset($option[1]) ? (($option[1] == $option_input) ? ' selected="selected"' : false) : (($option[0] == $option_input) ? ' selected="selected"' : false)) . ((isset($option[2])) ? ' ' . $option[2] : false) . '>'. $option[0] .'</option>' . PHP_EOL;
    }
    
    $html .= '</select>';
    
    return $html;
  }
  
  function form_draw_static_field($name, $value=true, $parameters='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<div class="input-static"'. (($parameters) ? ' '.$parameters : false) .'>'. (($value) ? $value : '&nbsp;') .'</div>';
  }
  
  function form_draw_textarea($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="large"';
    
    return '<textarea name="'. htmlspecialchars($name) .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. htmlspecialchars($value) .'</textarea>';
  }
  
  function form_draw_text_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name, $value);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="text" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="text" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_time_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="time" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="time" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_toggle($name, $input=true, $type='e/d') {
    if ($input === true) $input = form_reinsert_value($name);
    
    $input = in_array(strtolower($input), array('1', 'active', 'enabled', 'on', 'true', 'yes')) ? '1' : '0';
    
    switch ($type) {
      case 'a/i':
        $true_text = $GLOBALS['system']->language->translate('title_active', 'Active');
        $false_text = $GLOBALS['system']->language->translate('title_inactive', 'Inactive');
        break;
      case 'e/d':
        $true_text = $GLOBALS['system']->language->translate('title_enabled', 'Enabled');
        $false_text = $GLOBALS['system']->language->translate('title_disabled', 'Disabled');
        break;
      case 'y/n':
        $true_text = $GLOBALS['system']->language->translate('title_yes', 'Yes');
        $false_text = $GLOBALS['system']->language->translate('title_no', 'No');
        break;
      case 'o/o':
        $true_text = $GLOBALS['system']->language->translate('title_on', 'On');
        $false_text = $GLOBALS['system']->language->translate('title_off', 'Off');
        break;
      case 't/f':
      default:
        $true_text = $GLOBALS['system']->language->translate('title_true', 'True');
        $false_text = $GLOBALS['system']->language->translate('title_false', 'False');
        break;
    }
    
    return '<label><input type="radio" name="'. htmlspecialchars($name) .'" value="1" data-type="toggle" '. (($input == '1') ? 'checked="checked"' : '') .' /> '. $true_text .'</label> <label><input type="radio" name="'. htmlspecialchars($name) .'" value="0" data-type="toggle" '. (($input == '0') ? 'checked="checked"' : '') .' /> '. $false_text .'</label>';
  }
  
  function form_draw_url_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="medium"';
    
    return '<input type="url" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" data-type="url" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_wysiwyg_field($name, $value=true, $parameters='', $hint='') {
    
    if ($value === true) $value = form_reinsert_value($name);
    
    $GLOBALS['system']->document->snippets['head_tags']['sceditor'] = '<script src="'. WS_DIR_EXT .'sceditor/jquery.sceditor.xhtml.min.js"></script>' . PHP_EOL
                                                         . '<script src="'. WS_DIR_EXT .'sceditor/plugins/format.js"></script>' . PHP_EOL
                                                         . '<script src="'. WS_DIR_EXT .'sceditor/languages/'. $GLOBALS['system']->language->selected['code'] .'.js"></script>' . PHP_EOL
                                                         . '<link href="'. WS_DIR_EXT .'sceditor/themes/square.min.css" rel="stylesheet" />' . PHP_EOL;
    
    return '<textarea name="'. htmlspecialchars($name) .'" data-type="wysiwyg" data-size="auto" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. htmlspecialchars($value) .'</textarea>'
         . '<script>' . PHP_EOL
         . '  $("textarea[name=\''. $name .'\']").sceditor({' . PHP_EOL
         . '    "plugins": "xhtml,format",' . PHP_EOL
         . '    "width": 1024,' . PHP_EOL
         . '    "resizeEnabled": true,' . PHP_EOL
         . '    "style": "'. WS_DIR_EXT .'sceditor/jquery.sceditor.default.min.css",' . PHP_EOL
         . '    "locale": "'. htmlspecialchars($GLOBALS['system']->language->selected['code']) .'",' . PHP_EOL
         . '    "emoticons": false,' . PHP_EOL
         . '    "toolbar": "format|font,size,bold,italic,underline,strike,subscript,superscript|left,center,right,justify|color,removeformat|bulletlist,orderedlist,table|code,quote|horizontalrule,image,email,link,unlink|youtube,date,time|ltr,rtl|print,maximize,source"' . PHP_EOL
         . '  });' . PHP_EOL
         . '</script>' . PHP_EOL;
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
        return form_draw_decimal_field($name, $input, 2, '', '');
      case 'int':
        return form_draw_number_field($name, $input);
      case 'currency':
        return form_draw_currency_field(!empty($options[0]) ? $options[0] : null, $name, $input);
      case 'smallinput':
        return form_draw_input($name, $input, 'text', 'data-size="small"');
      case 'input':
        return form_draw_input($name, $input, 'text', 'data-size="medium"');
      case 'password':
        return form_draw_input($name, $input, 'password', 'data-size="medium"');
      case 'smalltext':
        return form_draw_textarea($name, $input, 'rows="2" data-size="medium"');
      case 'mediumtext':
        return form_draw_textarea($name, $input, 'rows="5" data-size="large"');
      case 'bigtext':
        return form_draw_textarea($name, $input, 'rows="10" data-size="large"');
      case 'categories':
        return form_draw_categories_list($name, $input);
      case 'customers':
        return form_draw_customers_list($name, $input);
      case 'countries':
        return form_draw_countries_list($name, $input);
      case 'currencies':
        return form_draw_currencies_list($name, $input);
      case 'geo_zones':
        return form_draw_geo_zones_list($name, $input);
      case 'languages':
        return form_draw_languages_list($name, $input);
      case 'length_classes':
        return form_draw_length_classes_list($name, $input);
      case 'product':
        return form_draw_products_list($name, $input);
      case 'order_status':
        return form_draw_order_status_list($name, $input);
      case 'radio':
        $output = '';
        for ($i=0; $i<count($options); $i++) $output .= ' <label>'. form_draw_radio_button($name, $options[$i], $input) .' '. $options[$i] .'</label>';
        return $output;
      case 'select':
        for ($i=0; $i<count($options); $i++) $options[$i] = array($options[$i]);
        return form_draw_select_field($name, $options, $input, false);
      case 'timezones':
        return form_draw_timezones_list($name, $input);
      case 'templates':
        return form_draw_templates_list($name, $input);
      case 'toggle':
        return form_draw_toggle($name, $input, !empty($options[0]) ? $options[0] : null);
      case 'tax_classes':
        return form_draw_tax_classes_list($name, $input);
      case 'weight_classes':
        return form_draw_weight_classes_list($name, $input);
      case 'zones':
        $option = !empty($options) ? $options[0] : $GLOBALS['system']->settings->get('store_country_code');
        return form_draw_zones_list($option, $name, $input);
      default:
        trigger_error('Unknown function name ('. $function .')', E_USER_ERROR);
    }
  }
  
  function form_draw_categories_list($name, $input=true, $multiple=false, $parameters=false) {
    
    if (!function_exists('form_draw_categories_list_options_iterator')) {
      function form_draw_categories_list_options_iterator($parent_id = 0, $level = 1) {
        
        $options = array();
        
        if ($parent_id == '0') $options[] = array($GLOBALS['system']->language->translate('option_root', '[Root]'), '0', 'style="background: url('. WS_DIR_IMAGES .'/icons/16x16/folder_closed.png) no-repeat 0px 0px; padding-left: '. 18 .'px; margin: 5px;"');
        
        $categories_query = $GLOBALS['system']->database->query(
          "select c.id, ci.name
          from ". DB_TABLE_CATEGORIES ." c
          left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $GLOBALS['system']->language->selected['code'] ."')
          where parent_id = '". (int)$parent_id ."'
          order by c.priority asc, ci.name asc;"
        );
        
        while ($category = $GLOBALS['system']->database->fetch($categories_query)) {
        
          $options[] = array($category['name'], $category['id'], 'style="background: url('. WS_DIR_IMAGES .'/icons/16x16/folder_closed.png) no-repeat '. ($level*16) .'px 0px; padding-left: '. (($level*16)+18) .'px; margin: 5px;"');
        
          $sub_categories_query = $GLOBALS['system']->database->query(
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
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    $options = array_merge($options, form_draw_categories_list_options_iterator());
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_countries_list($name, $input=true, $multiple=false, $parameters='') {
    
    if ($input === true) $input = form_reinsert_value($name);
    
    if ($input == '') $input = $GLOBALS['system']->settings->get('default_country_code');
    
    $countries_query = $GLOBALS['system']->database->query(
      "select * from ". DB_TABLE_COUNTRIES ."
      where status
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($country = $GLOBALS['system']->database->fetch($countries_query)) {
      $options[] = array($country['name'], $country['iso_code_2']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_currencies_list($name, $input=true, $multiple=false, $parameters='') {
    
    $currencies_query = $GLOBALS['system']->database->query(
      "select * from ". DB_TABLE_CURRENCIES ."
      where status
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($currency = $GLOBALS['system']->database->fetch($currencies_query)) {
      $options[] = array($currency['name'], $currency['code']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_customers_list($name, $input=true, $multiple=false, $parameters='') {
    
    $customers_query = $GLOBALS['system']->database->query(
      "select id, firstname, lastname from ". DB_TABLE_CUSTOMERS ."
      order by lastname, firstname;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($customer = $GLOBALS['system']->database->fetch($customers_query)) {
      $options[] = array($customer['lastname'] .', '. $customer['firstname'] .' ['. $customer['id'] .']', $customer['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_delivery_status_list($name, $input=true, $multiple=false, $parameters='') {
    
    $query = $GLOBALS['system']->database->query(
      "select ds.id, dsi.name from ". DB_TABLE_DELIVERY_STATUSES ." ds
      left join ". DB_TABLE_DELIVERY_STATUSES_INFO ." dsi on (dsi.delivery_status_id = ds.id and dsi.language_code = '". $GLOBALS['system']->database->input($GLOBALS['system']->language->selected['code']) ."')
      order by dsi.name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($row = $GLOBALS['system']->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_geo_zones_list($name, $input=true, $multiple=false, $parameters='') {
    
    $geo_zones_query = $GLOBALS['system']->database->query(
      "select * from ". DB_TABLE_GEO_ZONES ."
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    if ($GLOBALS['system']->database->num_rows($geo_zones_query) == 0) {
      return form_draw_hidden_field($name, '0') . form_draw_select_field($name, $options, $input, false, false, $parameters . ' disabled="disabled"');
    }
    
    while ($geo_zone = $GLOBALS['system']->database->fetch($geo_zones_query)) {
      $options[] = array($geo_zone['name'], $geo_zone['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  } 
  
  function form_draw_languages_list($name, $input=true, $multiple=false, $parameters='') {
    
    $currencies_query = $GLOBALS['system']->database->query(
      "select * from ". DB_TABLE_LANGUAGES ."
      where status
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($language = $GLOBALS['system']->database->fetch($currencies_query)) {
      $options[] = array($language['name'], $language['code']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_length_classes_list($name, $input=true, $multiple=false, $parameters='') {
    
    if ($input === true) $input = form_reinsert_value($name);
    
    if ($input == '') $input = $GLOBALS['system']->settings->get('store_length_class');
    
    $options = array();
    
    foreach ($GLOBALS['system']->length->classes as $class) {
      $options[] = array($class['unit']);
    }
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="auto"';
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_manufacturers_list($name, $input=true, $multiple=false, $parameters='') {
    
    $manufacturers_query = $GLOBALS['system']->database->query(
      "select id, name from ". DB_TABLE_MANUFACTURERS ."
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($manufacturer = $GLOBALS['system']->database->fetch($manufacturers_query)) {
      $options[] = array($manufacturer['name'], $manufacturer['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_option_groups_list($name, $input=true, $multiple=false, $parameters='') {
    
    $option_groups_query = $GLOBALS['system']->database->query(
      "select pcg.id, pcg.function, pcg.required, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
      left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". $GLOBALS['system']->database->input($GLOBALS['system']->language->selected['code']) ."')
      order by pcgi.name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($option_group = $GLOBALS['system']->database->fetch($option_groups_query)) {
      $options[] = array($option_group['name'] .' ['. $option_group['function'] .']', $option_group['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_option_values_list($group_id, $name, $input=true, $multiple=false, $parameters='') {
    
    $option_values_query = $GLOBALS['system']->database->query(
      "select pcv.id, pcv.value, pcvi.name from ". DB_TABLE_OPTION_VALUES ." pcv
      left join ". DB_TABLE_OPTION_VALUES_INFO ." pcvi on (pcvi.value_id = pcv.id and pcvi.language_code = '". $GLOBALS['system']->database->input($GLOBALS['system']->language->selected['code']) ."')
      where pcv.group_id = '". (int)$group_id ."'
      order by pcvi.name asc;"
    );
      
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');

    while ($option_value = $GLOBALS['system']->database->fetch($option_values_query)) {
      if (empty($option_value['name'])) $option_value['name'] = $option_value['value'];
      if (empty($option_value['name'])) $option_value['name'] = '('. $GLOBALS['system']->language->translate('text_user_input', 'User input') .')';
      $options[] = array($option_value['name'], $option_value['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_order_status_list($name, $input=true, $multiple=false, $parameters='') {
    
    $query = $GLOBALS['system']->database->query(
      "select os.id, osi.name from ". DB_TABLE_ORDER_STATUSES ." os
      left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = os.id and osi.language_code = '". $GLOBALS['system']->database->input($GLOBALS['system']->language->selected['code']) ."')
      order by priority, name;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($row = $GLOBALS['system']->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_products_list($name, $input=true, $multiple=false, $parameters) {
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    $products_query = $GLOBALS['system']->functions->catalog_products_query(array('sort' => 'name'));
    while ($product = $GLOBALS['system']->database->fetch($products_query)) {
      $options[] = array($product['name'] .' ['. $product['quantity'] .'] '. $GLOBALS['system']->currency->format($product['final_price']), $product['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_product_stock_options_list($product_id, $name, $input=true, $multiple=false, $parameters) {
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    if (!empty($product_id)) {
      $product = new ref_product($product_id);
      if (count($product->options_stock) > 0) {
        foreach (array_keys($product->options_stock) as $key) {
          $options[] = array($product->options_stock[$key]['name'][$GLOBALS['system']->language->selected['code']] .' ['. $product->options_stock[$key]['quantity'] .'] ', $product->id);
        }
      }
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_sold_out_status_list($name, $input=true, $multiple=false, $parameters='') {
    
    $query = $GLOBALS['system']->database->query(
      "select sos.id, sosi.name from ". DB_TABLE_SOLD_OUT_STATUSES ." sos
      left join ". DB_TABLE_SOLD_OUT_STATUSES_INFO ." sosi on (sosi.sold_out_status_id = sos.id and sosi.language_code = '". $GLOBALS['system']->database->input($GLOBALS['system']->language->selected['code']) ."')
      order by sosi.name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($row = $GLOBALS['system']->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_suppliers_list($name, $input=true, $multiple=false, $parameters='') {
    
    $suppliers_query = $GLOBALS['system']->database->query(
      "select id, name from ". DB_TABLE_SUPPLIERS ."
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($supplier = $GLOBALS['system']->database->fetch($suppliers_query)) {
      $options[] = array($supplier['name'], $supplier['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_templates_list($name, $input=true, $multiple=false, $parameters='') {
    
    $folders = glob(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'*');
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    foreach($folders as $folder) {
      $options[] = array(basename($folder));
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }

  function form_draw_timezones_list($name, $input=true, $multiple=false, $parameters='') {
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    $zones = timezone_identifiers_list();
    
    foreach ($zones as $zone) {
      $zone = explode('/', $zone); // 0 => Continent, 1 => City
      
      if (in_array($zone[0], array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'))) {
        if (!empty($zone[1])) {
          $options[] = array($zone[0]. '/' . $zone[1]);
        }
      }
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_tax_classes_list($name, $input=true, $multiple=false, $parameters='') {
    
    if ($input === true) $input = form_reinsert_value($name);
    
    if ($input == '') $input = $GLOBALS['system']->settings->get('default_tax_class_id');
    
    $tax_classes_query = $GLOBALS['system']->database->query(
      "select * from ". DB_TABLE_TAX_CLASSES ."
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $GLOBALS['system']->language->translate('title_select', 'Select') . ' --', '');
    
    while ($tax_class = $GLOBALS['system']->database->fetch($tax_classes_query)) {
      $options[] = array($tax_class['name'], $tax_class['id']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_weight_classes_list($name, $input=true, $multiple=false, $parameters='') {
    
    if ($input === true) $input = form_reinsert_value($name);
    
    if ($input == '') $input = $GLOBALS['system']->settings->get('store_weight_class');
    
    $options = array();
    
    foreach ($GLOBALS['system']->weight->classes as $class) {
      $options[] = array($class['unit']);
    }
    
    if (!preg_match('/data-size="[^"]*"/', $parameters)) $parameters .= (!empty($parameters) ? ' ' : null) . 'data-size="auto"';
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
  function form_draw_zones_list($country_code, $name, $input=true, $multiple=false, $parameters='') {

    if ($country_code == '') $country_code = $GLOBALS['system']->settings->get('default_country_code');
  
    if ($input === true) $input = form_reinsert_value($name);
    
    if ($input == '') $input = $GLOBALS['system']->settings->get('default_zone_code');
    
    $zones_query = $GLOBALS['system']->database->query(
      "select * from ". DB_TABLE_ZONES ."
      where country_code = '". $GLOBALS['system']->database->input($country_code) ."'
      order by name asc;"
    );
    
    $options = array();
    
    if ($GLOBALS['system']->database->num_rows($zones_query) == 0) {
      return form_draw_hidden_field($name, '') . form_draw_select_field($name, $options, $input, $multiple, $parameters . ' disabled="disabled"');
    }
    
    while ($zone = $GLOBALS['system']->database->fetch($zones_query)) {
      $options[] = array($zone['name'], $zone['code']);
    }
    
    return form_draw_select_field($name, $options, $input, $multiple, $parameters);
  }
  
?>