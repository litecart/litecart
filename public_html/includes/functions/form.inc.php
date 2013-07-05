<?php
  
  function form_draw_form_begin($name='', $method='post', $action=false, $multipart=false, $parameters='') {
    global $system;
    
    return  '<form'. (($name) ? ' name="'. htmlspecialchars($name) .'"' : false) .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'"'. (($action) ? ' action="'. htmlspecialchars($action) .'"' : '') . (($parameters) ? ' ' . $parameters : false) .'>'. PHP_EOL
          . ((strtolower($method) == 'post') ? form_draw_input('token', $system->form->session_post_token(), 'hidden') . PHP_EOL : '');
  }
  
  function form_draw_form_end() {
    return '</form>' . PHP_EOL;
  }
  
  function form_reinsert_value($name, $default_value=null) {
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
            if ($value == $default_value) {
              return $value;
            }
          }
        }
      }
    }
    
    return '';
  }
  
  function form_draw_image($name, $src, $parameters=false) {
    return '<input type="hidden" name="'. htmlspecialchars($name) .'" value="true" /><input type="image" src="'. htmlspecialchars($src) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_button($name, $value, $type='submit', $parameters='', $icon='') {
    return '<button type="'. (($type == 'submit') ? 'submit' : 'button') .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. ((!empty($icon)) ? '<img src="'. WS_DIR_IMAGES .'icons/16x16/'. $icon .'.png" /> ' : false) . $value .'</button>';
  }
  
  function form_draw_link_button($url, $title, $parameters='', $icon='') {
    return '<a class="button" href="'. htmlspecialchars($url) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. ((!empty($icon)) ? '<img src="'. WS_DIR_IMAGES .'icons/16x16/'. $icon .'.png" /> ' : false) . $title .'</a>';
  }
  
  function form_draw_checkbox($name, $value, $input=true, $parameters='', $hint='') {
    if ($input === true) $input = form_reinsert_value($name, $value);
    return form_draw_input($name, $value, 'checkbox', ($input === $value ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false), $hint);
  }
  
  function form_draw_currency_field($currency_code, $name, $value=true, $parameters='', $hint='') {
    global $system;
    if ($value === true) $value = form_reinsert_value($name);
    
    return '<span class="input-wrapper">'. $system->currency->currencies[$currency_code]['prefix'] . form_draw_input_field($name, $value, $parameters, $hint) . $system->currency->currencies[$currency_code]['suffix'] .'</span>';
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
    
    return '<img src="'. WS_DIR_IMAGES .'icons/16x16/calendar.png" width="16" height="16" /> <input type="date" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" maxlength="10" placeholder="YYYY-MM-DD"'. (($hint) ? ' title="'. htmlspecialchars($hint) .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
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
    
    return '<input type="datetime" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" maxlength="16" placeholder="YYYY-MM-DD [hh:nn]" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_decimal_field($name, $value=true, $decimals=2, $min=0, $max=0, $parameters='', $hint='') {
    if ($value === true) $value = round((float)form_reinsert_value($name), $decimals);
    return form_draw_input($name, $value, 'number', 'step="any" min="'. (float)$min .'"'. (!empty($max) ? ' max="'. (float)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) .' style="width: 75px; text-align: right;"', $hint);
  }
  
  function form_draw_email_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    return form_draw_input($name, $value, 'email', $parameters, $hint);
  }

  function form_draw_file_field($name, $parameters='', $hint='') {
    return form_draw_input($name, '', 'file', $parameters, $hint);
  }
  
  function form_draw_hidden_field($name, $value=true) {
    if ($value === true) $value = form_reinsert_value($name);
    return form_draw_input($name, $value, 'hidden');
  }
  
  function form_draw_input($name, $value=true, $type='text', $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    return '<input type="'. htmlspecialchars($type) .'" name="'. htmlspecialchars($name) .'" value="'. htmlspecialchars($value) .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
  }
  
  function form_draw_input_field($name, $value=true, $parameters='', $hint='') {
    return form_draw_input($name, $value, 'text', $parameters, $hint);
  }
  
  function form_draw_number_field($name, $value=true, $min='', $max='', $parameters='', $hint='') {
    if ($value === true) $value = (int)form_reinsert_value($name);
    return form_draw_input($name, $value, 'number', 'step="1" min="'. (($min) ? (int)$min : false) .'"'. (!empty($max) ? ' max="'. (float)$max .'"' : false) . (($parameters) ? ' '.$parameters : false) .' style="width: 75px; text-align: right;"', $hint);
  }
  
  function form_draw_password_field($name, $value=true, $parameters='', $hint='') {
    return form_draw_input($name, $value, 'password', $parameters, $hint);
  }
  
  function form_draw_radio_button($name, $value, $input=true, $parameters='', $hint='') {
    if ($input === true) $input = form_reinsert_value($name, $value);
    return form_draw_input($name, $value, 'radio', ($input === $value ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false), $hint);
  }
  
  function form_draw_range_slider($name, $value=true, $min='', $max='', $step='', $parameters='', $hint='') {
    return form_draw_input_field($name, $value, 'range', 'min="'. (float)$min .'" max="'. (float)$max .'" step="'. (float)$step .'"'. (($parameters) ? ' ' . $parameters : false));
  }
  
  function form_draw_regional_input_field($language_code, $name, $value=true, $parameters='', $hint='') {
    return '<span class="input-wrapper"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" width="16" style="vertical-align: middle;" /> '. form_draw_input($name, $value, 'text', $parameters, $hint) .'</span>';
  }
  
  function form_draw_regional_textarea($language_code, $name, $value=true, $parameters='', $hint='') {
    return '<span class="input-wrapper"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" width="16" style="vertical-align: top;" /> '. form_draw_textarea($name, $value, $parameters, $hint) .'</span>';
  }
  
  function form_draw_regional_wysiwyg_field($language_code, $name, $value=true, $parameters='', $hint='') {
    return '<span class="input-wrapper"><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" width="16" style="vertical-align: top;" /> '. form_draw_wysiwyg_field($name, $value, $parameters, $hint) .'</span>';
  }
  
  function form_draw_search_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    return form_draw_input($name, $value, 'search', $parameters, $hint);
  }
  
  function form_draw_select_field($name, $options=array(), $input=true, $multiple=false, $parameters='', $hint='') {
    
    if (!is_array($options)) $options = array($options);
    
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
    return '<div class="input-static"'. (($parameters) ? ' '.$parameters : false) .'>'. (($value) ? $value : '&nbsp;') .'</div>';
  }
  
  function form_draw_textarea($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    return '<textarea name="'. htmlspecialchars($name) .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. htmlspecialchars($value) .'</textarea>';
  }
  
  function form_draw_time_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    return form_draw_input($name, $value, 'time', 'maxlength="6"' . (($parameters) ? ' '.$parameters : false), $hint);
  }
  
  function form_draw_url_field($name, $value=true, $parameters='', $hint='') {
    if ($value === true) $value = form_reinsert_value($name);
    return form_draw_input($name, $value, 'url', $parameters, $hint);
  }
  
  function form_draw_wysiwyg_field($name, $value=true, $parameters='', $hint='') {
    global $system;
    
    $system->document->snippets['head_tags']['sceditor'] = '<script src="'. WS_DIR_EXT .'sceditor/jquery.sceditor.xhtml.min.js"></script>' . PHP_EOL
                                                         . '<script src="'. WS_DIR_EXT .'sceditor/plugins/format.js"></script>' . PHP_EOL
                                                         . '<script src="'. WS_DIR_EXT .'sceditor/languages/'. $system->language->selected['code'] .'.js"></script>' . PHP_EOL
                                                         . '<link href="'. WS_DIR_EXT .'sceditor/themes/square.min.css" rel="stylesheet" />' . PHP_EOL;
    
    return form_draw_textarea($name, $value, $parameters, $hint) . PHP_EOL
         . '<script>' . PHP_EOL
         . '  $("textarea[name=\''. $name .'\']").sceditor({' . PHP_EOL
         . '    "plugins": "xhtml,format",' . PHP_EOL
         . '    "width": "auto",' . PHP_EOL
         . '    "max-height": "auto",' . PHP_EOL
         . '    "style": "'. WS_DIR_EXT .'sceditor/jquery.sceditor.default.min.css",' . PHP_EOL
         . '    "locale": "'. htmlspecialchars($system->language->selected['code']) .'",' . PHP_EOL
         . '    "emoticons": false,' . PHP_EOL
         . '    "toolbar": "format|font,size,bold,italic,underline,strike,subscript,superscript|left,center,right,justify|color,removeformat|bulletlist,orderedlist,table|code,quote|horizontalrule,image,email,link,unlink|youtube,date,time|ltr,rtl|print,maximize,source"' . PHP_EOL
         . '  });' . PHP_EOL
         . '</script>' . PHP_EOL;
  }
  
  ######################################################################

  function form_draw_function($function, $name, $input=true) {
    global $system;
    
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
        return form_draw_decimal_field($name, $input, 2, '', '', 'style="width: 30px"');
      case 'int':
        return form_draw_input($name, (int)$input, 'text', 'style="width: 50px"');
      case 'smallinput':
        return form_draw_input($name, $input, 'text', 'style="width: 50px"');
      case 'input':
        return form_draw_input($name, $input, 'text', 'style="width: 200px"');
      case 'password':
        return form_draw_input($name, $input, 'password', 'style="width: 200px"');
      case 'smalltext':
        return form_draw_textarea($name, $input, 'rows="2" style="width: 200px"');
      case 'mediumtext':
        return form_draw_textarea($name, $input, 'rows="5" style="width: 200px"');
      case 'bigtext':
        return form_draw_textarea($name, $input, 'rows="10" style="width: 200px"');
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
        return form_draw_select_field($name, $options, $input, false, 'style="width: 200px"');
      case 'timezones':
        return form_draw_timezones_list($name, $input);
      case 'templates':
        return form_draw_templates_list($name, $input);
      case 'toggle':
        return form_draw_radio_button($name, 'true', $input) . ' true '. form_draw_radio_button('value', 'false', $input) . ' false';
      case 'tax_classes':
        return form_draw_tax_classes_list($name, $input);
      case 'weight_classes':
        return form_draw_weight_classes_list($name, $input);
      case 'zones':
        $option = empty($options) ? $options[0] : $system->settings->get('store_country_code');
        return form_draw_zones_list($option, $name, $input);
      default:
        trigger_error('Unknown function name ('. $function .')', E_USER_ERROR);
    }
  }
  
  function form_draw_categories_list($name, $insert=true, $multiple=false, $parameters=false) {
    global $system;
    
    if (!function_exists('form_draw_categories_list_options_iterator')) {
      function form_draw_categories_list_options_iterator($parent_id = 0, $level = 1) {
        global $system;
        
        $options = array();
        
        if ($parent_id == '0') $options[] = array($system->language->translate('option_root', '[Root]'), '0', 'style="background: url('. WS_DIR_IMAGES .'/icons/16x16/folder_closed.png) no-repeat 0px 0px; padding-left: '. 18 .'px; margin: 5px;"');
        
        $categories_query = $system->database->query(
          "select c.id, ci.name
          from ". DB_TABLE_CATEGORIES ." c
          left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
          where parent_id = '". (int)$parent_id ."'
          order by c.priority asc, ci.name asc;"
        );
        
        while ($category = $system->database->fetch($categories_query)) {
        
          $options[] = array($category['name'], $category['id'], 'style="background: url('. WS_DIR_IMAGES .'/icons/16x16/folder_closed.png) no-repeat '. ($level*16) .'px 0px; padding-left: '. (($level*16)+18) .'px; margin: 5px;"');
        
          $sub_categories_query = $system->database->query(
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
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    $options = array_merge($options, form_draw_categories_list_options_iterator());
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }

  function form_draw_countries_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $countries_query = $system->database->query(
      "select * from ". DB_TABLE_COUNTRIES ."
      where status
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($country = $system->database->fetch($countries_query)) {
      $options[] = array($country['name'], $country['iso_code_2']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_currencies_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $currencies_query = $system->database->query(
      "select * from ". DB_TABLE_CURRENCIES ."
      where status
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($currency = $system->database->fetch($currencies_query)) {
      $options[] = array($currency['name'], $currency['code']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_customers_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $customers_query = $system->database->query(
      "select id, firstname, lastname from ". DB_TABLE_CUSTOMERS ."
      order by lastname, firstname;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($customer = $system->database->fetch($customers_query)) {
      $options[] = array($customer['lastname'] .', '. $customer['firstname'] .' ['. $customer['id'] .']', $customer['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_delivery_status_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $query = $system->database->query(
      "select ds.id, dsi.name from ". DB_TABLE_DELIVERY_STATUS ." ds
      left join ". DB_TABLE_DELIVERY_STATUS_INFO ." dsi on (dsi.delivery_status_id = ds.id and dsi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      order by dsi.name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($row = $system->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_geo_zones_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $geo_zones_query = $system->database->query(
      "select * from ". DB_TABLE_GEO_ZONES ."
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    if ($system->database->num_rows($geo_zones_query) == 0) {
      return form_draw_hidden_field($name, '0') . form_draw_select_field($name, $options, $insert, false, false, $parameters . ' disabled="disabled"');
    }
    
    while ($geo_zone = $system->database->fetch($geo_zones_query)) {
      $options[] = array($geo_zone['name'], $geo_zone['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  } 
  
  function form_draw_languages_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $currencies_query = $system->database->query(
      "select * from ". DB_TABLE_LANGUAGES ."
      where status
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($language = $system->database->fetch($currencies_query)) {
      $options[] = array($language['name'], $language['code']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_length_classes_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $options = array();
    
    foreach ($system->length->classes as $class) {
      $options[] = array($class['unit']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_manufacturers_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $manufacturers_query = $system->database->query(
      "select id, name from ". DB_TABLE_MANUFACTURERS ."
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($manufacturer = $system->database->fetch($manufacturers_query)) {
      $options[] = array($manufacturer['name'], $manufacturer['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_option_groups_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $option_groups_query = $system->database->query(
      "select pcg.id, pcg.function, pcg.required, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
      left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      order by pcgi.name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($option_group = $system->database->fetch($option_groups_query)) {
      $options[] = array($option_group['name'] .' ['. $option_group['function'] .']', $option_group['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_option_values_list($group_id, $name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $option_values_query = $system->database->query(
      "select pcv.id, pcv.value, pcvi.name from ". DB_TABLE_OPTION_VALUES ." pcv
      left join ". DB_TABLE_OPTION_VALUES_INFO ." pcvi on (pcvi.value_id = pcv.id and pcvi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      where pcv.group_id = '". (int)$group_id ."'
      order by pcvi.name asc;"
    );
      
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');

    while ($option_value = $system->database->fetch($option_values_query)) {
      if (empty($option_value['name'])) $option_value['name'] = $option_value['value'];
      if (empty($option_value['name'])) $option_value['name'] = '('. $system->language->translate('text_user_input', 'User input') .')';
      $options[] = array($option_value['name'], $option_value['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_order_status_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $query = $system->database->query(
      "select os.id, osi.name from ". DB_TABLE_ORDERS_STATUS ." os
      left join ". DB_TABLE_ORDERS_STATUS_INFO ." osi on (osi.order_status_id = os.id and osi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($row = $system->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_products_list($name, $insert=true, $multiple=false, $parameters) {
    global $system;
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
    while ($product = $system->database->fetch($products_query)) {
      $options[] = array($product['name'] .' ['. $product['quantity'] .'] '. $system->currency->format($product['final_price']), $product['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_product_stock_options_list($product_id, $name, $insert=true, $multiple=false, $parameters) {
    global $system;
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    if (!empty($product_id)) {
      $product = new ref_product($product_id);
      if (count($product->options_stock) > 0) {
        foreach (array_keys($product->options_stock) as $key) {
          $options[] = array($product->options_stock[$key]['name'][$system->language->selected['code']] .' ['. $product->options_stock[$key]['quantity'] .'] ', $product->id);
        }
      }
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_sold_out_status_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $query = $system->database->query(
      "select sos.id, sosi.name from ". DB_TABLE_SOLD_OUT_STATUS ." sos
      left join ". DB_TABLE_SOLD_OUT_STATUS_INFO ." sosi on (sosi.sold_out_status_id = sos.id and sosi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      order by sosi.name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($row = $system->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_suppliers_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $suppliers_query = $system->database->query(
      "select id, name from ". DB_TABLE_SUPPLIERS ."
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($supplier = $system->database->fetch($suppliers_query)) {
      $options[] = array($supplier['name'], $supplier['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_templates_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $folders = glob(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'*');
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    foreach($folders as $folder) {
      $options[] = array(basename($folder));
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }

  function form_draw_timezones_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    $zones = timezone_identifiers_list();
    
    foreach ($zones as $zone) {
      $zone = explode('/', $zone); // 0 => Continent, 1 => City
      
      if (in_array($zone[0], array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'))) {
        if (!empty($zone[1])) {
          $options[] = array($zone[0]. '/' . $zone[1]);
        }
      }
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_tax_classes_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    if (empty($insert)) $insert = $system->settings->get('default_tax_class_id');
    
    $tax_classes_query = $system->database->query(
      "select * from ". DB_TABLE_TAX_CLASSES ."
      order by name asc;"
    );
    
    $options = array();
    
    if (empty($multiple)) $options[] = array('-- '. $system->language->translate('title_select', 'Select') . ' --', '');
    
    while ($tax_class = $system->database->fetch($tax_classes_query)) {
      $options[] = array($tax_class['name'], $tax_class['id']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_weight_classes_list($name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    if (empty($insert)) $insert = $system->settings->get('store_weight_class');
    
    $options = array();
    
    foreach ($system->weight->classes as $class) {
      $options[] = array($class['unit']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
  function form_draw_zones_list($country_code, $name, $insert=true, $multiple=false, $parameters='') {
    global $system;
    
    $zones_query = $system->database->query(
      "select * from ". DB_TABLE_ZONES ."
      where country_code = '". $system->database->input($country_code) ."'
      order by name asc;"
    );
    
    $options = array();
    
    if ($system->database->num_rows($zones_query) == 0) {
      return form_draw_hidden_field($name, '') . form_draw_select_field($name, $options, $insert, false, false, $parameters . ' disabled="disabled"');
    }
    
    while ($zone = $system->database->fetch($zones_query)) {
      $options[] = array($zone['name'], $zone['code']);
    }
    
    return form_draw_select_field($name, $options, $insert, $multiple, $parameters);
  }
  
?>