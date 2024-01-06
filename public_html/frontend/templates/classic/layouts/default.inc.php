<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/app.min.css'); ?>" />
{{head_tags}}
{{style}}
</head>
<body>


  <?php if ($important_notice) { ?>
  <div id="important-notice">
    <div class="container">
      <?php echo $important_notice; ?>
    </div>
  </div>
  <?php } ?>

  <?php //include 'app://frontend/partials/site_header.inc.php'; ?>

  <?php include 'app://frontend/partials/site_navigation.inc.php'; ?>

  {{content}}

  <?php include 'app://frontend/partials/box_cookie_notice.inc.php'; ?>

  <?php include 'app://frontend/partials/site_footer.inc.php'; ?>

  <a id="scroll-up" class="hidden-print" href="#">
    <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
  </a>

{{foot_tags}}
<script src="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/js/app.min.js'); ?>"></script>

</body>
</html>