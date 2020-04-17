<?php 
namespace App;

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title>Seriti Framework</title>
        <link href='//fonts.googleapis.com/css?family=Lato:300' rel='stylesheet' type='text/css'>
        <style>
            body {
                margin: 50px 0 0 0;
                padding: 0;
                width: 100%;
                font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                text-align: center;
                color: #aaa;
                font-size: 18px;
            }

            h1 {
                color: #719e40;
                letter-spacing: -3px;
                font-family: 'Lato', sans-serif;
                font-size: 100px;
                font-weight: 200;
                margin-bottom: 0;
            }
        </style>
    </head>
    <body>
        <h1><?php echo SITE_NAME ?></h1>
        <h2>An error has been encountered</h2>
        <p>The error has been logged and will be attended to.</p>

        <?php 
        if(defined('MAIL_SUPPORT')) {
            echo '<p>If you urgently require support then please contact us at <a href="mailto:'.MAIL_SUPPORT.'">'.MAIL_SUPPORT.'</a></p>';
        }

        if(isset($html)) echo $html;
        ?>

        <p><a href="dashboard">Return to the dashboard</a></p>
    </body>
</html>
