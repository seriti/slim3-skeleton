<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;

$html = $data['login'];
?>
         
<div class="container">
  <div class="row">
    <div class="col-xs-12 col-sm-offset-3 col-sm-6 col-lg-offset-4 col-lg-4">
      
      <div id="login_div">
        <?php echo SITE_LOGIN_LOGO ?>
        
        <?php
        //unique security identifier
        echo '<input type="hidden" name="'.$html['human_input_name'].'" value="'.$html['human_input_value'].'">';

        echo $html['messages'];

        echo '<input type="text" name="email" value="'.$form['email'].'" class="form-control" placeholder="Your email address"><br/>'; 
        echo '<input id="btn_login" type="submit" value="Proceed" class="btn btn-primary" onclick="link_download(\'btn_login\')">';
        ?>
        <script>
          if(check_cookies_enabled()==0) document.write('<div class="error">Cookies not enabled! please enable and refresh this page.</div>');
        </script>
             

      </div>     
    </div>
  </div>
</div>