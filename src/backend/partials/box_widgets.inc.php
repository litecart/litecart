<?php

  $box_widgets = new ent_view('app://backend/template/partials/box_widgets.inc.php', [
    'widgets' => [],
  ]);

  $widgets = f::admin_get_widgets();

  foreach ($widgets as $widget) {
    if (!empty(administrator::$data['permissions']['widgets']) && !in_array($widget['id'], administrator::$data['permissions']['widgets'])) continue;

    ob_start();
    include stream_app::resolve($widget['directory'] . $widget['file']);
    $output = ob_get_clean();

    $box_widgets->snippets['widgets'][] = [
      'id' => $widget['id'],
      'content' => $output,
    ];
  }

  echo $box_widgets;
