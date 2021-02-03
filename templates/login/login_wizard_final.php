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
        echo '<h1>'.$form['email'].'<img src="'.BASE_URL.'images/tick.png"></h1>'; 
        
        echo $html['messages'];
        
        ?>
      </div>     
    </div>
  </div>
</div>
