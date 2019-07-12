<?php
namespace App;

use Seriti\Tools\Form;

echo $this->fetch('include/header.php',[]); 
?>
    <body>
        
        <div class="container">
          <div class="row">
            <div class="col-xs-12 col-sm-offset-3 col-sm-6 col-lg-offset-4 col-lg-4">
              
              <div id="login_div">
                <?php echo SITE_LOGIN_LOGO ?>
                
                <form method="post" id="login_form" action="?mode=login">
                <?php
                //unique security identifier
                echo '<input type="hidden" name="'.$html['human_input_name'].'" value="'.$html['human_input_value'].'">';

                echo $html['messages'];

                if($html['reset_send']) {
                  echo 'Your email: '.$html['email'].'<br/>';
                  echo 'NEW Password<br/><input type="password" name="password" class="form-control"<br/>';
                  echo 'Password repeat<br/><input type="password" name="password_repeat" class="form-control"><br/>';
                  echo '<input id="btn_reset" type="submit" value="Reset password" class="btn btn-primary" onclick="link_download(\'btn_reset\')"><br/>';

                } else {
                  echo '<input type="text" name="email" value="'.$html['email'].'" class="form-control" placeholder="Your email"><br/>'; 
                  echo '<input type="checkbox" onclick="checkbox_password_mask(\'pwd_input\',this)" checked>mask password<br/>';
                  echo '<input id="pwd_input" type="password" name="password" class="form-control" placeholder="Your password"><br/>';

                  $param = [];
                  echo ' <div class="row">'.
                        '<div class="col-sm-7"><input type="checkbox" name="remember_me" value="YES">&nbsp;Remember me<br/>'.
                        Form::arrayList($html['days'],'days_expire',$html['days_expire'],true,$param).'</div>';
                  echo '<div class="col-sm-5"><input id="btn_login" type="submit" value="login" class="btn btn-primary" onclick="link_download(\'btn_login\')"></div>';
                  echo '</div>';
                }
                ?>
                <script>
                  if(check_cookies_enabled()==0) document.write('<div class="error">Cookies not enabled! please enable and refresh this page.</div>');
                </script>

                </form>
                <hr/>

                <div id="login_reset">
                    <form method="post" id="reset_form" action="?mode=reset_send">
                    <input id="btn_reset2" value="Send me a new login" class="btn btn-primary form-control" onclick="processUserReset('LOGIN')">
                    <br/><br/>
                    <input id="btn_reset1" value="Reset my password" class="btn btn-primary form-control" onclick="processUserReset('PASSWORD')">
                    </form>
                </div>

              </div>     
            </div>
          </div>
        </div>
       
    </body>
</html>

<script type="text/javascript">

function processUserReset(Reset_type) {
    link_download('login_div');

    var login_form = document.getElementById('login_form');
    var reset_form = document.getElementById('reset_form');

    if(!login_form.email || !login_form.email.value) {
      alert('You have not entered an email address!');
      return false;
    } else {
      var email = login_form.email.value;
      if(!check_valid_email(email)) {
        alert('Invalid email address entered: '+email);
        return false;
      } 
    }   

    //prevents duplicate input fields if user dbl clicks or uncaught error...etc
    if( typeof reset_form.email == 'undefined') {
      var input = document.createElement("input");
      input.setAttribute("type", "hidden");
      input.setAttribute("name", "email");
      input.setAttribute("value", email);

      var input2 = document.createElement("input");
      input2.setAttribute("type", "hidden");
      input2.setAttribute("name", "reset_type");
      input2.setAttribute("value", Reset_type);

      var input3 = document.createElement("input");
      input3.setAttribute("type", "hidden");
      input3.setAttribute("name", "days_expire");
      input3.setAttribute("value", login_form.days_expire.value);

      //append to form element that you want .
      reset_form.appendChild(input);
      reset_form.appendChild(input2);
      reset_form.appendChild(input3);
    } else {
      reset_form.email.value = email;
      reset_form.reset_type.value = Reset_type;
      reset_form.days_expire.value = login_form.days_expire.value;
    }

    //console.log(reset_form);
    //return false;

    reset_form.submit();
}  
</script>