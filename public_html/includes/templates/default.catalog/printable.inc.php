<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{snippet:language}" lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={snippet:charset}" />
<link href="<!--snippet:template_path-->styles/printable.css" rel="stylesheet" type="text/css" media="print, screen" />
<!--snippet:head_tags-->
<!--snippet:javascript-->
</head>
<body>
<?php if (isset($_GET['media']) && $_GET['media'] == 'print') { ?>
<script type="text/javascript">
  window.onload = function() {
    window.print();
  }
</script>
<?php } ?>
<!--snippet:content-->
</body>
</html>