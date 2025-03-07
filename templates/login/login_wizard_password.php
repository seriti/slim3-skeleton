<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;

$html = $data['login'];

$btn_text = 'Login';
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

        echo '<h1>'.$form['email'].'<img src="'.BASE_URL.'images/tick.png"></h1>'; 
        //echo '<input type="checkbox" onclick="checkbox_password_mask(\'pwd_input\',this)" checked>mask password<br/>';
        //echo '<input id="pwd_input" type="password" name="password" class="form-control pwd" placeholder="Please enter your password"><br/>';

        echo '<div class="input-group">
                <input type="password" id="pwd_input" name="password" class="form-control pwd" value="" placeholder="Enter your password here">
                <span class="input-group-btn">
                  <button class="btn btn-default reveal" type="button"><i class="glyphicon glyphicon-eye-open"></i></button>
                </span>          
              </div>';

        echo '<br/>';

        echo '<div class="row">'.
               '<div class="col-sm-7"><input type="checkbox" name="remember_me" value="1" CHECKED>&nbsp;Remember me '.
               Form::arrayList($html['days'],'remember_days',$form['remember_days'],true,$param).
               '</div>';
        echo   '<div class="col-sm-5">'.
               '<input id="btn_login" type="submit" value="'.$btn_text.'" class="btn btn-primary" onclick="link_download(\'btn_login\')">'.
               '</div>';
        echo '</div>';

        echo '<br/>';

        //NB: Ypu must specify a page=x in link args or wizard resets
        echo '<p><a href="javascript:toggle_display(\'login_options\')">Forgot your password?</a></p>';
        echo '<div id="login_options" style="display:none;">'.
             'we can <a href="'.BASE_URL.'login?page=2&mode=send_login">email you a login link</a>,<br/>'.
             'or <a href="'.BASE_URL.'login?page=2&mode=send_reset">reset your password</a>.'.
             '</div>';

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