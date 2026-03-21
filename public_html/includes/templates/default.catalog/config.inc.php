<?php

  return $template_config = [
    [
      'key' => 'sidebar_parallax_effect',
      'default_value' => '1',
      'title' => language::translate('template:title_sidebar_parallax_effect', 'Sidebar Parallax Effect'),
      'description' => language::translate('template:description_sidebar_parallax_effect', 'Enables or disables the sidebar parallax effect.'),
      'function' => 'toggle("e/d")',
    ],
    [
      'key' => 'compact_category_tree',
      'default_value' => '0',
      'title' => language::translate('template:title_compact_category_tree', 'Compact Category Tree'),
      'description' => language::translate('template:description_compact_category_tree', 'Hide the other main categories while browsing a category branch.'),
      'function' => 'toggle("e/d")',
    ],
    [
      'key' => 'footer_categories',
      'default_value' => '1',
      'title' => language::translate('template:title_footer_categories', 'Footer Categories'),
      'description' => language::translate('template:description_footer_categories', 'Display the categories section in the footer.'),
      'function' => 'toggle("y/n")',
    ],
    [
      'key' => 'footer_categories_limit',
      'default_value' => '9',
      'title' => language::translate('template:title_footer_categories_limit', 'Footer Categories Limit'),
      'description' => language::translate('template:description_footer_categories_limit', 'Maximum number of categories to display in the footer.'),
      'function' => 'number()',
    ],
    [
      'key' => 'footer_manufacturers',
      'default_value' => '1',
      'title' => language::translate('template:title_footer_manufacturers', 'Footer Manufacturers'),
      'description' => language::translate('template:description_footer_manufacturers', 'Display the manufacturers section in the footer.'),
      'function' => 'toggle("y/n")',
    ],
    [
      'key' => 'footer_manufacturers_limit',
      'default_value' => '9',
      'title' => language::translate('template:title_footer_manufacturers_limit', 'Footer Manufacturers Limit'),
      'description' => language::translate('template:description_footer_manufacturers_limit', 'Maximum number of manufacturers to display in the footer.'),
      'function' => 'number()',
    ],
    [
      'key' => 'footer_contact',
      'default_value' => '1',
      'title' => language::translate('template:title_footer_contact', 'Footer Contact'),
      'description' => language::translate('template:description_footer_contact', 'Display the contact section in the footer.'),
      'function' => 'toggle("y/n")',
    ],
  ];
