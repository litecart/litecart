<?php
  $template_config = array(
    array(
      'key' => 'product_modal_window',
      'default_value' => '1',
      'title' => language::translate('template:title_product_modal_window', 'Product Modal Window'),
      'description' => language::translate('template:description_product_modal_window', 'Enables or disables the product modal window.'),
      'function' => 'toggle("e/d")',
    ),
    array(
      'key' => 'sidebar_parallax_effect',
      'default_value' => '1',
      'title' => language::translate('template:title_sidebar_parallax_effect', 'Sidebar Parallax Effect'),
      'description' => language::translate('template:description_sidebar_parallax_effect', 'Enables or disables the sidebar parallax effect.'),
      'function' => 'toggle("e/d")',
    ),
    array(
      'key' => 'compact_category_tree',
      'default_value' => '0',
      'title' => language::translate('template:title_compact_category_tree', 'Compact Category Tree'),
      'description' => language::translate('template:description_compact_category_tree', 'Hide the other categories while browsing a category branch.'),
      'function' => 'toggle("e/d")',
    ),
    array(
      'key' => 'cookie_acceptance',
      'default_value' => '1',
      'title' => language::translate('template:title_cookie_acceptance', 'Cookie Acceptance'),
      'description' => language::translate('template:description_cookie_acceptance', 'Enables or disables the cookie acceptance notice.'),
      'function' => 'toggle("e/d")',
    ),
  );
