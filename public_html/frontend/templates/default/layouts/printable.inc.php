<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/printable.min.css'); ?>" />
{{head_tags}}
</head>
<body>

{{content}}

{{foot_tags}}
</body>
</html>