<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;

$html = $data['login'];
//NB: $html['email'] will be set with correct email from emailed login reset link
//So don't use $form['email'] in case user session has expired

$btn_text = 'Reset your password';
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

        echo '<h1>'.$html['email'].'<img src="'.BASE_URL.'images/tick.png"></h1>'; 

        echo '<h1>Reset your password.</h1>';
        
        echo '<div class="input-group">
                <input type="password" name="password" class="form-control pwd" value="" placeholder="Enter your NEW password here">
                <span class="input-group-btn">
                  <button class="btn btn-default reveal" type="button"><i class="glyphicon glyphicon-eye-open"></i></button>
                </span>          
              </div>';

        echo '<br/>';

        echo '<div class="input-group">
                <input type="password" name="password_repeat" class="form-control pwd" value="" placeholder="Repeat your NEW password here">
                <span class="input-group-btn">
                  <button class="btn btn-default reveal" type="button"><i class="glyphicon glyphicon-eye-open"></i></button>
                </span>          
              </div>';

        echo '<br/>';

        echo '<input id="btn_login" type="submit" value="'.$btn_text.'" class="btn btn-primary" onclick="link_download(\'btn_login\')">';
        ?>
                     

      </div>     
    </div>
  </div>
</div>

<script type="text/javascript">
$(".reveal").on('click',function() {
    var $pwd = $(".pwd");
    if ($pwd.attr('type') === 'password') {
        $pwd.attr('type', 'text');
    } else {
        $pwd.attr('type', 'password');
    }
});

</script>