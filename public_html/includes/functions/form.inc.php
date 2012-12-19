<?php

  function form_draw_form_begin($name='', $method='post', $action=false, $multipart=false, $parameters=false) {
    global $system;
    
    $html = '<form'. (($name) ? ' name="'. $name .'"' : false) . (($action) ? ' action="' . $action .'"' : false) .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'"' . (($parameters) ? ' ' . $parameters : false) .'>'. PHP_EOL;
    if (strtolower($method) == 'post') $html .= '<input type="hidden" name="token" value="'. $system->form->session_post_token() .'" />';
    return $html;
  }
  
  function form_draw_form_end() {
    $html = '</form>';
    return $html;
  }
  
  function form_draw_radio_button($name, $value, $input=false, $parameters=false, $hint=false) {
    $html = '<input type="radio" name="'. $name .'" value="'. $value .'" title="'. htmlspecialchars($hint) .'"'. ((isset($input) && $input === $value) ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
    return $html;
  }
  
  function form_draw_range_slider($name, $value, $min, $max, $step='', $parameters=false, $hint=false) {
    $html = '<input type="range" name="'. $name .'" value="'. $value .'" min="'. $min .'" max="'. $max .'" step="'. $step .'" title="'. htmlspecialchars($hint) .'"'. ((isset($input) && $input === $value) ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
    return $html;
  }
  
  function form_draw_checkbox($name, $value, $input=false, $parameters=false, $hint=false) {
    $html = '<input type="checkbox" name="'. $name .'" value="'. $value .'" title="'. htmlspecialchars($hint) .'"'. ((isset($input) && $input === $value) ? ' checked="checked"' : false) . (($parameters) ? ' ' . $parameters : false) .' />';
    return $html;
  }
  
  function form_draw_button($name, $value, $type='submit', $parameters=false, $icon='') {
    $html = '<button type="'. (($type == 'submit') ? 'submit' : 'button') .'" name="'. $name .'" value="'. $value .'" class="'. $type .'"'. (($parameters) ? ' '.$parameters : false) .'>' . $value .'</button>';
    return $html;
  }
  
  function form_draw_currency_field($currency_code, $name, $value='', $parameters=false, $hint=false) {
    global $system;
    //$html = '<div class="regional-input-wrapper"><input type="text" name="'. $name .'" value="'. htmlspecialchars($value) .'" class="currency-field" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' /><span style="position: absolute; left: 5px; top: 6px;"></span></div>';
    $html = '<input type="text" name="'. $name .'" value="'. number_format((float)$value, $system->currency->currencies[$currency_code]['decimals'], '.', '') .'" class="currency-field" title="'. htmlspecialchars($hint) .'" style="width: 75px; text-align: right;"'. (($parameters) ? ' '.$parameters : false) .' />';
    return $html;
  }
  
  function form_draw_number_field($currency_code, $name, $value='', $parameters=false, $hint=false) {
    global $system;
    $html = '<input type="number" name="'. $name .'" value="'. (int)$value .'" class="input-field" title="'. htmlspecialchars($hint) .'" style="width: 75px; text-align: right;"'. (($parameters) ? ' '.$parameters : false) .' />';
    return $html;
  }
  
  function form_draw_date_field($name, $value='', $parameters=false, $hint=false) {
    global $system;
    
    $strf = '%Y-%m-%d';
    
    $system->document->snippets['head_tags']['dynDateTime'] = '<script type="text/javascript" src="'. WS_DIR_EXT .'dynDateTime/jquery.dynDateTime.js"></script>' . PHP_EOL
                                                            . '<script type="text/javascript" src="'. WS_DIR_EXT .'dynDateTime/lang/calendar-en.js"></script>' . PHP_EOL
                                                            . '<script type="text/javascript" src="'. WS_DIR_EXT .'dynDateTime/lang/calendar-'. $system->language->selected['code'] .'.js"></script>' . PHP_EOL
                                                            . '<link rel="stylesheet" type="text/css" media="screen" href="'. WS_DIR_EXT .'dynDateTime/css/calendar-system.css" />' . PHP_EOL;
                                                                 
    $system->document->snippets['javascript']['date_picker'] = '  $(document).ready(function(){' . PHP_EOL
                                                                   . '    $("input.date").live("mouseover", function() {' . PHP_EOL
                                                                   . '      $(this).dynDateTime({' . PHP_EOL
                                                                   . '        showsTime: false,' . PHP_EOL
                                                                   . '        ifFormat: "'. $strf .'",' . PHP_EOL
                                                                   . '        button: ".next()"' . PHP_EOL
                                                                   . '      });' . PHP_EOL
                                                                   . '    });' . PHP_EOL
                                                                   . '  });' . PHP_EOL;
  
    if (empty($hint)) $hint = $system->language->selected['raw_date'];
    
    if (substr($value, 0, 10) == '0000-00-00') {
      $value = '';
    } else if (substr($value, 0, 10) == '1970-00-00') {
      $value = '';
    } else {
      $value = strftime($strf, strtotime($value));
      if (substr($value, 0, 10) == '1970-01-01') $value = '';
    }
    
    $html = '<div style="display: inline; position: relative;"><input type="text" name="'. $name .'" value="'. htmlspecialchars($value) .'" maxlength="10" class="date" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' /><img src="'. WS_DIR_IMAGES .'icons/16x16/calendar.png" width="16" height="16" style="position: absolute; top: 0px; left: 5px;" /></div>';
    return $html;
  }
  
  function form_draw_datetime_field($name, $value='', $parameters=false, $hint=false) {
    global $system;
    
    $strf = '%Y-%m-%d %H:%M';
  
    $system->document->snippets['head_tags']['dynDateTime'] = '<script type="text/javascript" src="'. WS_DIR_EXT .'dynDateTime/jquery.dynDateTime.js"></script>' . PHP_EOL
                                                            . '<script type="text/javascript" src="'. WS_DIR_EXT .'dynDateTime/lang/calendar-en.js"></script>' . PHP_EOL
                                                            . '<script type="text/javascript" src="'. WS_DIR_EXT .'dynDateTime/lang/calendar-'. $system->language->selected['code'] .'.js"></script>' . PHP_EOL
                                                            . '<link rel="stylesheet" type="text/css" media="screen" href="'. WS_DIR_EXT .'dynDateTime/css/calendar-system.css" />' . PHP_EOL;
    
    $system->document->snippets['javascript']['datetime_picker'] = '  $(document).ready(function(){' . PHP_EOL
                                                                 . '    $("input.datetime").live("mouseover", function() {' . PHP_EOL
                                                                 . '      $(this).dynDateTime({' . PHP_EOL
                                                                 . '        showsTime: true,' . PHP_EOL
                                                                 . '        ifFormat: "'. $strf .'",' . PHP_EOL
                                                                 . '        button: ".next()"' . PHP_EOL
                                                                 . '      });' . PHP_EOL
                                                                 . '    });' . PHP_EOL
                                                                 . '  });' . PHP_EOL;
    
    if (empty($hint)) $hint = $system->language->selected['raw_datetime'];
    
    if (substr($value, 0, 16) == '0000-00-00 00:00') {
      $value = '';
    } else if (substr($value, 0, 16) == '1970-00-00 00:00') {
      $value = '';
    } else {
      $value = strftime($strf, strtotime($value));
      if (substr($value, 0, 10) == '1970-01-01') $value = '';
    }
    
    $html = '<div style="display: inline; position: relative;"><input type="text" name="'. $name .'" value="'. htmlspecialchars($value) .'" maxlength="16" class="datetime" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' /><img src="'. WS_DIR_IMAGES .'icons/16x16/calendar.png" width="16" height="16" style="position: absolute; top: 0px; left: 5px;" /></div>';

    return $html;
  }
  
  function form_draw_time_field($name, $value='', $parameters=false, $hint=false) {
    $html = '<input type="text" name="'. $name .'" value="'. htmlspecialchars($value) .'" maxlength="6" class="time" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
    return $html;
  }
  
  function form_draw_file_field($name, $parameters=false, $hint=false) {
    $html = '<input type="file" name="'. $name .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
    return $html;
  }
  
  function form_draw_hidden_field($name, $value='') {
    $html = '<input type="hidden" name="'. $name .'" value="'. htmlspecialchars($value) .'" />';
    return $html;
  }
  
  function form_draw_image_field($name, $src, $title=false, $parameters=false) {
    $html = '<input type="image" src="'. $src .'" name="'. $name .'" value="'. $value .'"'. (($title != '') ? ' alt="'. $title .'" title="'. $title .'"' : false) . (($parameters) ? ' '.$parameters : false) .' />';
    return $html;
  }
  
  function form_draw_input_field($name, $value='', $type='text', $parameters=false, $hint=false) {
    $html = '<input type="'. (($type != 'password') ? 'text' : 'password') .'" name="'. $name .'" value="'. htmlspecialchars($value) .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' />';
    return $html;
  }
  
  function form_draw_regional_input_field($language_code, $name, $value='', $type='text', $parameters=false, $hint=false) {
    $html = '<div class="regional-input-wrapper"><input type="'. (($type != 'password') ? 'text' : 'password') .'" name="'. $name .'" value="'. htmlspecialchars($value) .'" class="regional-input-field" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .' /><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" style="position: absolute; left: 5px; top: 6px;" width="16" height="11" /></div>';
    return $html;
  }
  
  function form_draw_select_field($name, $options=array(), $input=false, $size=false, $multiple=false, $parameters=false, $hint=false) {
    
    if (!is_array($options)) $options = array($options);
    if (!is_array($input)) $input = array($input);
    
    $html = '<select name="'. $name .'"'. (($size) ? ' size="' . $size .'"' : false) . (($multiple && $size > 1) ? ' multiple="multiple"' : false) .' title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' ' . $parameters : false) .'>' . PHP_EOL;
    
    foreach ($options as $option) {
      $html .= '<option value="'. htmlspecialchars(isset($option[1]) ? $option[1] : $option[0]) .'"'. (in_array(isset($option[1]) ? $option[1] : $option[0], $input) ? ' selected="selected"' : false) . ((isset($option[2])) ? ' ' . $option[2] : false) . '>'. $option[0] .'</option>' . PHP_EOL;
    }
    
    $html .= '</select>';
    
    return $html;
  }
  
  function form_draw_static_field($name, $value='', $parameters=false) {
    $html = '<div class="input-static"'. (($parameters) ? ' '.$parameters : false) .'>'. (($value) ? $value : '&nbsp;') .'</div>';
    return $html;
  }
  
  function form_draw_textarea($name, $value='', $parameters=false, $hint=false) {
    $html = '<textarea name="'. $name .'" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. htmlspecialchars($value) .'</textarea>';
    return $html;
  }
  
  function form_draw_regional_textarea($language_code, $name, $value='', $parameters=false, $hint=false) {
    $html = '<div class="regional-input-wrapper"><textarea name="'. $name .'" class="regional-input-field" title="'. htmlspecialchars($hint) .'"'. (($parameters) ? ' '.$parameters : false) .'>'. htmlspecialchars($value) .'</textarea><img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" style="position: absolute; left: 5px; top: 6px;" width="16" height="11" /></div>';
    return $html;
  }
    
  ######################################################################

  function form_draw_function($function, $name, $input='') {
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
        return $system->functions->form_draw_input_field($name, number_format($input, 2), 'text', 'style="width: 50px"');
      case 'int':
        return $system->functions->form_draw_input_field($name, (int)$input, 'text', 'style="width: 50px"');
      case 'smallinput':
        return $system->functions->form_draw_input_field($name, $input, 'text', 'style="width: 50px"');
      case 'input':
        return $system->functions->form_draw_input_field($name, $input, 'text', 'style="width: 200px"');
      case 'smalltext':
        return $system->functions->form_draw_textarea($name, $input, 'rows="2" style="width: 200px"');
      case 'mediumtext':
        return $system->functions->form_draw_textarea($name, $input, 'rows="5" style="width: 200px"');
      case 'bigtext':
        return $system->functions->form_draw_textarea($name, $input, 'rows="10" style="width: 200px"');
      case 'customers':
        return form_draw_customers_list($name, $input, '');
      case 'countries':
        return form_draw_countries_list($name, $input, '', 'code');
      case 'countries_id':
        return form_draw_countries_list($name, $input, '', 'id');
      case 'currencies':
        return form_draw_currencies_list($name, $input, '', 'code');
      case 'currencies_id':
        return form_draw_currencies_list($name, $input, '', 'id');
      case 'geo_zones':
        return form_draw_geo_zones_list($name, $input);
      case 'languages':
        return form_draw_languages_list($name, $input, '', 'code');
      case 'languages_id':
        return form_draw_languages_list($name, $input, '', 'id');
      case 'length_classes':
        return form_draw_length_classes_list($name, $input);
      case 'product':
        return form_draw_products_list($name, $input);
      case 'order_status':
        return form_draw_order_status_list($name, $input);
      case 'radio':
        $output = '';
        for ($i=0; $i<count($options); $i++) $output .= ' '. $system->functions->form_draw_radio_button($name, $options[$i], $input) .' '. $options[$i];
        return $output;
      case 'select':
        for ($i=0; $i<count($options); $i++) $options[$i] = array($options[$i]);
        return $system->functions->form_draw_select_field($name, $options, $input, false, false, 'style="width: 200px"');
      case 'timezones':
        return form_draw_timezones_list($name, $input);
      case 'templates':
        return form_draw_templates_list($name, $input);
      case 'toggle':
        return $system->functions->form_draw_radio_button($name, 'true', $input) . ' true '. $system->functions->form_draw_radio_button('value', 'false', $input) . ' false';
      case 'tax_classes':
        return form_draw_tax_classes_list($name, $input);
      case 'weight_classes':
        return form_draw_weight_classes_list($name, $input);
      case 'zones':
        $option = empty($options) ? $options[0] : $system->settings->get('store_country_code');
        return form_draw_zones_list($option, $name, $input, '', 'code');
      case 'zones_id':
        $option = empty($options[0]) ? $options[0] : $system->settings->get('store_country_code');
        return form_draw_zones_list($option, $name, $input, '', 'id');
      default:
        trigger_error('Unknown function name ('. $function .')', E_USER_ERROR);
    }
  }
  
  function form_draw_categories_list($name, $insert, $parameters=false) {
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
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    $options = array_merge($options, form_draw_categories_list_options_iterator());
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }

  function form_draw_countries_list($name, $insert='', $parameters='', $return_type='code') {
    global $system;
    
    $countries_query = $system->database->query(
      "select * from ". DB_TABLE_COUNTRIES ."
      where status
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    while ($country = $system->database->fetch($countries_query)) {
      switch($return_type) {
        case 'id':
          $options[] = array($country['name'], $country['id']);
          break;
        case 'code':
        case 'iso_code_2':
          $options[] = array($country['name'], $country['iso_code_2']);
          break;
        case 'iso_code_2':
          $options[] = array($country['name'], $country['iso_code_3']);
          break;
        default:
          trigger_error('Unknown return type for list of countries', E_USER_ERROR);
      }
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_currencies_list($name, $insert='', $parameters='', $return_type='code') {
    global $system;
    
    $currencies_query = $system->database->query(
      "select * from ". DB_TABLE_CURRENCIES ."
      where status
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($currency = $system->database->fetch($currencies_query)) {
      switch($return_type) {
        case 'id':
          $options[] = array($currency['name'], $currency['id']);
          break;
        case 'code':
          $options[] = array($currency['name'], $currency['code']);
          break;
        default:
          trigger_error('Unknown return type for list of currencies', E_USER_ERROR);
      }
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_customers_list($name, $insert='', $parameters='') {
    global $system;
    
    $customers_query = $system->database->query(
      "select id, firstname, lastname from ". DB_TABLE_CUSTOMERS ."
      order by lastname, firstname;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($customer = $system->database->fetch($customers_query)) {
      $options[] = array($customer['lastname'] .', '. $customer['firstname'] .' ['. $customer['id'] .']', $customer['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_delivery_status_list($name, $insert='', $parameters='') {
    global $system;
    
    $query = $system->database->query(
      "select ds.id, dsi.name from ". DB_TABLE_DELIVERY_STATUS ." ds
      left join ". DB_TABLE_DELIVERY_STATUS_INFO ." dsi on (dsi.delivery_status_id = ds.id and dsi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      order by dsi.name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($row = $system->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_designers_list($name, $insert='', $parameters='') {
    global $system;
    
    $designers_query = $system->database->query(
      "select id, name from ". DB_TABLE_DESIGNERS ."
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($designer = $system->database->fetch($designers_query)) {
      $options[] = array($designer['name'], $designer['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_geo_zones_list($name, $insert='', $parameters='') {
    global $system;
    
    $geo_zones_query = $system->database->query(
      "select * from ". DB_TABLE_GEO_ZONES ."
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    if ($system->database->num_rows($geo_zones_query) == 0) {
      return $system->functions->form_draw_hidden_field($name, '0') . $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters . ' disabled="disabled"');
    }
    
    while ($geo_zone = $system->database->fetch($geo_zones_query)) {
      $options[] = array($geo_zone['name'], $geo_zone['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  } 
  
  function form_draw_languages_list($name, $insert='', $parameters='', $return_type='code') {
    global $system;
    
    $currencies_query = $system->database->query(
      "select * from ". DB_TABLE_LANGUAGES ."
      where status
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($language = $system->database->fetch($currencies_query)) {
      switch($return_type) {
        case 'id':
          $options[] = array($language['name'], $language['id']);
          break;
        case 'code':
          $options[] = array($language['name'], $language['code']);
          break;
        default:
          trigger_error('Unknown return type for list of languages', E_USER_ERROR);
      }
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_length_classes_list($name, $insert='', $parameters='') {
    global $system;
    
    $options = array();
    
    foreach ($system->length->classes as $class) {
      $options[] = array($class['unit']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_manufacturers_list($name, $insert='', $parameters='') {
    global $system;
    
    $manufacturers_query = $system->database->query(
      "select id, name from ". DB_TABLE_MANUFACTURERS ."
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($manufacturer = $system->database->fetch($manufacturers_query)) {
      $options[] = array($manufacturer['name'], $manufacturer['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_option_groups_list($name, $insert='', $parameters='') {
    global $system;
    
    $option_groups_query = $system->database->query(
      "select pcg.id, pcg.function, pcg.required, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
      left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      order by pcgi.name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($option_group = $system->database->fetch($option_groups_query)) {
      $options[] = array($option_group['name'] .' ['. $option_group['function'] .']', $option_group['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_option_values_list($group_id, $name, $insert='', $parameters='') {
    global $system;
    
    $option_values_query = $system->database->query(
      "select pcv.id, pcv.value, pcvi.name from ". DB_TABLE_OPTION_VALUES ." pcv
      left join ". DB_TABLE_OPTION_VALUES_INFO ." pcvi on (pcvi.value_id = pcv.id and pcvi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      where pcv.group_id = '". (int)$group_id ."'
      order by pcvi.name asc;"
    );
      
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );

    while ($option_value = $system->database->fetch($option_values_query)) {
      if (empty($option_value['name'])) $option_value['name'] = $option_value['value'];
      if (empty($option_value['name'])) $option_value['name'] = '('. $system->language->translate('text_user_input', 'User input') .')';
      $options[] = array($option_value['name'], $option_value['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_order_status_list($name, $insert='', $parameters='') {
    global $system;
    
    $query = $system->database->query(
      "select os.id, osi.name from ". DB_TABLE_ORDERS_STATUS ." os
      left join ". DB_TABLE_ORDERS_STATUS_INFO ." osi on (osi.order_status_id = os.id and osi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($row = $system->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_products_list($name, $insert, $parameters) {
    global $system;
    
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'product.inc.php');
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') .' --', ''),
    );
    
    $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
    while ($product = $system->database->fetch($products_query)) {
      $product = new ref_product($product['id']);
      $options[] = array($product->name[$system->language->selected['code']] .' ['. $product->quantity .'] '. $system->currency->format($product->price), $product->id);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_product_stock_options_list($product_id, $name, $insert, $parameters) {
    global $system;
    
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'product.inc.php');
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') .' --', ''),
    );
    
    if (!empty($product_id)) {
      $product = new ref_product($product_id);
      if (count($product->options_stock) > 0) {
        foreach (array_keys($product->options_stock) as $key) {
          $options[] = array($product->options_stock[$key]['name'][$system->language->selected['code']] .' ['. $product->options_stock[$key]['quantity'] .'] ', $product->id);
        }
      }
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_sold_out_status_list($name, $insert='', $parameters='') {
    global $system;
    
    $query = $system->database->query(
      "select sos.id, sosi.name from ". DB_TABLE_SOLD_OUT_STATUS ." sos
      left join ". DB_TABLE_SOLD_OUT_STATUS_INFO ." sosi on (sosi.sold_out_status_id = sos.id and sosi.language_code = '". $system->database->input($system->language->selected['code']) ."')
      order by sosi.name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($row = $system->database->fetch($query)) {
      $options[] = array($row['name'], $row['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_suppliers_list($name, $insert='', $parameters='') {
    global $system;
    
    $suppliers_query = $system->database->query(
      "select id, name from ". DB_TABLE_SUPPLIERS ."
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($supplier = $system->database->fetch($suppliers_query)) {
      $options[] = array($supplier['name'], $supplier['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_templates_list($name, $insert='', $parameters='') {
    global $system;
    
    $folders = glob(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'*');
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    foreach($folders as $folder) {
      $options[] = array(basename($folder));
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }

  function form_draw_timezones_list($name, $insert='', $parameters='') {
    global $system;
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    $zones = timezone_identifiers_list();
    
    foreach ($zones as $zone) {
      $zone = explode('/', $zone); // 0 => Continent, 1 => City
      
      if (in_array($zone[0], array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'))) {
        if (!empty($zone[1])) {
          $options[] = array($zone[0]. '/' . $zone[1]);
        }
      }
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_tax_classes_list($name, $insert='', $parameters='') {
    global $system;
    
    if (empty($insert)) $insert = $system->settings->get('default_tax_class_id');
    
    $tax_classes_query = $system->database->query(
      "select * from ". DB_TABLE_TAX_CLASSES ."
      order by name asc;"
    );
    
    $options = array(
      array('-- '. $system->language->translate('title_select', 'Select') . ' --', ''),
    );
    
    while ($tax_class = $system->database->fetch($tax_classes_query)) {
      $options[] = array($tax_class['name'], $tax_class['id']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_weight_classes_list($name, $insert='', $parameters='') {
    global $system;
    
    if (empty($insert)) $insert = $system->settings->get('store_weight_class');
    
    $options = array();
    
    foreach ($system->weight->classes as $class) {
      $options[] = array($class['unit']);
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
  function form_draw_zones_list($country_code, $name, $insert='', $parameters='', $return_type='code') {
    global $system;
    
    $zones_query = $system->database->query(
      "select * from ". DB_TABLE_ZONES ."
      where country_code = '". $system->database->input($country_code) ."'
      order by name asc;"
    );
    
    $options = array();
    
    if ($system->database->num_rows($zones_query) == 0) {
      return $system->functions->form_draw_hidden_field($name, '') . $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters . ' disabled="disabled"');
    }
    
    while ($zone = $system->database->fetch($zones_query)) {
      switch($return_type) {
        case 'id':
          $options[] = array($zone['name'], $zone['id']);
          break;
        case 'code':
          $options[] = array($zone['name'], $zone['code']);
          break;
        default:
          trigger_error('Unknown return type for list of zones', E_USER_ERROR);
      }
    }
    
    return $system->functions->form_draw_select_field($name, $options, $insert, false, false, $parameters);
  }
  
?>