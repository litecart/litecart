<?php
  $template_config = array(
    array(
      'key' => 'fixed_header',
      'default_value' => '0',
      'title' => language::translate('title_fixed_header', 'Fixed Header'),
      'description' => language::translate('description_fixed_header', 'Fixate the header position making it stick on top while scroll.'),
      'function' => 'toggle("y/n")',
    ),
  );
?>