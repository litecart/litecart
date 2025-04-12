<?php

	class functions {

		public static function __callstatic($function, $arguments) {

			// Handle deprecated or renamed functions by rerouting them
			foreach ([
				'#^form_draw_currency_field$#' => 'form_select_currency',
				'#^form_draw_customer_field$#' => 'form_select_customer',
				'#^form_draw_product_field$#' => 'form_select_product',
				'#^form_draw_users_list$#' => 'form_select_administrator',
				'#^form_draw_select_field$#' => 'form_select',
				'#^form_draw_toggle_buttons$#' => 'form_toggle',
				'#^form_draw_weight_classes_list$#' => 'form_select_weight_unit',
				'#^form_draw_length_classes_list$#' => 'form_select_length_unit',
				'#^form_draw_volume_classes_list$#' => 'form_select_volume_unit',
				'#^form_draw_order_status_list$#' => 'form_select_order_status',
				'#^form_draw_checkbox$#' => 'form_checkbox',
				'#^form_draw_radio_button$#' => 'form_radio_button',
				'#^form_draw_textarea$#' => 'form_textarea',
				'#^form_draw_button$#' => 'form_button',
				'#^form_draw_select_multiple_field$#' => 'form_select_multiple',
				'#^form_draw_(.*?)ies_list$#' => 'form_select_$1y',
				'#^form_draw_(.*?)ses_list$#' => 'form_select_$1s',
				'#^form_draw_(.*?)s_list$#' => 'form_select_$1',
				'#^form_draw_form_(begin|end)$#' => 'form_$1',
				'#^form_draw_(.*?)_field$#' => 'form_input_$1',
			] as $search => $replace) {
				if (preg_match($search, $function)) {
					$new_function = preg_replace($search, $replace, $function);
					trigger_error('Function '. $function.'() has been renamed to '. $new_function .'()', E_USER_DEPRECATED);
					$function = $new_function;
					break;
				}
			}

			if (!function_exists($function)) {
				$file = 'func_' . strtok($function, '_') .'.inc.php';
				include_once 'app://includes/functions/' . $file;
			}

			return call_user_func_array($function, $arguments);
		}
	}
