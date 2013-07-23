<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <?php echo Sys::get('loader')->view('../nav/mod_meta.tpl','nav/mod_meta.php')->render(); ?>
    <?php echo Sys::get('loader')->view('../nav/mod_css.tpl','nav/mod_css.php')->render(); ?>
    <?php echo Sys::get('loader')->view('../nav/mod_js.tpl','nav/mod_js.php')->render(); ?>

    <title><?php echo $Title; ?></title>
</head>

<body style="background: #e6e6e6">
<div style="background: #808080; color: white; font-weight: bold; font-size: 12px; padding: 5px">Mobile version</div>
<?php echo $Container; ?>
</body>
</html>