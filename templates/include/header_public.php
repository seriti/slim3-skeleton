<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title><?php echo SITE_NAME; ?></title>
        <link href='//fonts.googleapis.com/css?family=Lato:300' rel='stylesheet' type='text/css'>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="<?php echo BASE_URL; ?>include/bootstrap/js/bootstrap-datepicker.min.js"></script>
        
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>include/bootstrap/css/<?php echo WWW_SITE_THEME_CSS; ?>">
        
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>include/bootstrap/css/bootstrap-datepicker.css"  type="text/css">

        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        
        <script type="text/javascript" src="<?php echo BASE_URL; ?>include/seriti/javascript.js"></script>

        <link href="<?php echo BASE_URL; ?>include/seriti/style.css" rel="stylesheet" type="text/css">

        <?php
        if(defined('WWW_SITE_CSS')) echo '<style type="text/css">'.WWW_SITE_CSS.'</style>';

        if(defined('WWW_SITE_ICON')) echo WWW_SITE_ICON;
        ?>

        <style type="text/css"> body { padding-top: 70px; } </style>

        <script>
          $(document).ready(function() {
            //alert('wtf');
            $('.bootstrap_date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
          });
        </script>
    </head>
    <body>
    <div id="main_div">
        <div class="container">    