<?php
namespace App\User;

use Seriti\Tools\Table;
use Seriti\Tools\Crypt;
use Seriti\Tools\Form;
use Seriti\Tools\Secure;
use Seriti\Tools\Audit;
use Seriti\Tools\TABLE_AUDIT;

class User extends Table 
{
    

    protected function beforeUpdate($id,$edit_type,&$form,&$error_str) 
    {
        //verify email unique
        $sql = 'SELECT COUNT(*) FROM '.$this->table.' WHERE email = "'.$this->db->escapeSql($form['email']).'" ';
        if($edit_type === 'UPDATE') $sql .= 'AND user_id <> "'.$this->db->escapeSql($id).'" ';  
        $count = $this->db->readSqlValue($sql);
        if($count != 0) $error_str .= 'Your entered email address is already being used in system!<br/>'.
                                      'Email addresses must be unique in system! Please use an alternate email address.'; 

        if($edit_type === 'INSERT' or isset($_POST['change_password'])) {
            //need to create salt and hash password
            $salt = Crypt::makeSalt();
            $pwd_hash = Crypt::passwordHash($form['password'],$salt); 

            $form[$this->user_cols['pwd_salt']] = $salt;
            $form[$this->user_cols['password']] = $pwd_hash;
        } 
    }  
  
    protected function beforeDelete($id,&$error) 
    {
        $login_user = $this->getContainer('user');
        if($login_user->getAccessLevel() === 'GOD') {
            $sql = 'DELETE FROM '.TABLE_AUDIT.' '.
                   'WHERE '.$this->audit_cols['user_id'].' = "'.$this->db->escapeSql($id).'" ';
            $this->db->executeSql($sql,$error);

            $sql = 'DELETE FROM '.TABLE_TOKEN.' '.
                   'WHERE '.$this->token_cols['user_id'].' = "'.$this->db->escapeSql($id).'" ';
            $this->db->executeSql($sql,$error);    
        }
        
    }
  
    protected function viewTableActions() 
    {
        $html = '';
        $list = array();
            
        $status_set = 'NEW';
        $date_set = date('Y-m-d');
        
        if(!$this->access['read_only']) {
            $list['SELECT'] = 'Action for selected '.$this->row_name_plural;
            $list['SEND_RESET'] = 'RESET user password and email them login details.';
            $list['SEND_LOGIN'] = 'Send user login link valid for 30days.';
            $list['SEND_EMAIL'] = 'Send users a plain text email.';
            $list['COPY_ACCESS'] = 'Copy another user access settings.';
        }  
        
        if(count($list) != 0){
            $html .= '<div id="action_div"><table><tr>';
            $html .= '<td><input type="checkbox" id="checkbox_all">(Select all users on page)</td>';
            $param['class'] = $this->classes['action'];
            $param['onchange'] = 'javascript:change_table_action()';
            $action_id = '';
            $email_body = '';
            $from_user_id = '';

            $html .= '<td>'.Form::arrayList($list,'table_action',$action_id,true,$param).'</td>';
            //javascript to show collection list depending on selection      
            $html .= '<script type="text/javascript">'.
                        '$("#checkbox_all").click(function () {$(".checkbox_action").prop(\'checked\', $(this).prop(\'checked\'));});'.
                        'function change_table_action() {'.
                        'var table_action = document.getElementById(\'table_action\');'.
                        'var email_text = document.getElementById(\'email_text\');'.
                        'var from_user = document.getElementById(\'from_user\');'.
                        'var action = table_action.options[table_action.selectedIndex].value; '.
                        'if(action==\'SEND_EMAIL\') {'.
                            'email_text.style.display = \'inline\';} else {email_text.style.display = \'none\';} '.
                        'if(action==\'COPY_ACCESS\') {'.
                            'from_user.style.display = \'inline\';} else {from_user.style.display = \'none\';} '.
                        '};'.
                      '</script>';

            $param = array();
            $param['class'] = $this->classes['action'];

            $html .= '<td><span id="email_text" style="display:none;"> Message &raquo;'.
                     Form::textAreaInput('email_body',$email_body,'','',$param).
                     '</span></td>';

            $html .= '<td><span id="from_user" style="display:none"> From user ID &raquo;'.
                     Form::textInput('from_user_id',$from_user_id,$param).
                     '</span></td>';

            $html .= '<td><input type="submit" name="action_submit" value="Apply action to selected '.$this->row_name_plural.'" class="btn btn-primary"></td>'.
                     '</tr></table></div>';
        }  
        
        return $html; 
    }
  
    protected function updateTable() 
    {
        $audit_count = 0;
        $html = '';
        $error_str = '';
        
        $login_user = $this->getContainer('user');
        //$login_user_data = $login_user->getData();
            
        $action = Secure::clean('basic',$_POST['table_action']);
        if($action === 'SELECT') {
            $error_str .= 'You have not selected any action to perform on '.$this->row_name_plural.'!';  
        } 

        if($action === 'SEND_RESET') {
            $audit_str = 'Reset user password: ';
            $audit_action = 'USER_PWD_RESET';
        }

        if($action === 'SEND_LOGIN') {
            $audit_str = 'Email login link to user: ';
            $audit_action = 'USER_LOGIN_SEND';
        }

        if($action=='SEND_EMAIL') {
            $email_body = Secure::clean('text',$_POST['email_body']);  
            if($email_body == '') $error_str .= 'You have not entered any message text for user!';
            $audit_str = 'User send email['.$email_body.']: ';
            $audit_action = 'USER_EMAIL';
        }

        if($action === 'COPY_ACCESS') {
            $from_user_id = Secure::clean('integer',$_POST['from_user_id']);  
            if($from_user_id == '') $error_str .= 'You have not entered a valid user ID to copy access from!';
            $audit_str = 'Copy from user['.$from_user_id.'] access: ';
            $audit_action = 'USER_COPY_ACCESS';
        }
           
        if($error_str !== '') {
           $this->addError($error_str);
        } else {
            foreach($_POST as $key => $value) {
                if(substr($key,0,8) === 'checked_') {
                    $error_str = '';
                    $message_str = '';
                    $user_id = Secure::clean('alpha',substr($key,8));
                    $user = $this->get($user_id);
                    $audit_str .= 'User ID['.$user_id.'] ';
                  
                    if($action === 'SEND_RESET') {
                        if($login_user->resetSendPassword($user_id)) {
                            $audit_str .= 'SUCCESS!';
                            $audit_count++;
                            $this->addMessage('Successfully reset password for user ID['.$user_id.'] email['.$user['email'].'].');                
                        } else {
                            $audit_str .= 'ERROR sending password reset ';
                            $this->addError('ERROR resetting password for user ID['.$user_id.']');                
                        }
                    }

                    if($action === 'SEND_LOGIN') {
                        $expire_days = 30;
                        if($login_user->resetSendLoginLink($user_id,$expire_days)) {
                            $audit_str .= 'SUCCESS!<br/>';
                            $audit_count++;
                            $this->addMessage('Successfully sent login token for user ID['.$user_id.'] email['.$user['email'].'].');                
                        } else {
                            $audit_str .= 'ERROR sending login reset ';
                            $this->addError('ERROR resetting login token for user ID['.$user_id.']');                
                        }
                    }
                  
                    if($action === 'SEND_EMAIL') {
                        $send_mode = 'TEXT';
                        $mail_from = '';//will use default config from address 
                        $mail_to = $user['email'];
                        $subject = SITE_NAME.' User communication';
                        $body = SITE_NAME.' Message for users:  '."\r\n\r\n".
                               'Hi '.$user['name']."\r\n\r\n".$email_body;
                                                
                        $mailer = $this->getContainer('mail');
                        if(!$mailer->sendEmail($mail_from,$mail_to,$subject,$body,$error)) {
                            $this->addError('FAILURE emailing user['.$user_id.'] message to address['.$mail_to.'] '); 
                        } else {
                            $this->addMessage('SUCCESS sending user['.$user_id.'] email to address ['.$mail_to.']'); 
                            $audit_count++;
                        }
                    }

                    if($action === 'COPY_ACCESS') {
                        if($login_user->copyUserAccess($from_user_id,$user_id)) {
                            $audit_str .= 'SUCCESS!<br/>';
                            $audit_count++;
                            $this->addMessage('Successfully copied user access for user ID['.$user_id.'] from user ID['.$from_user_id.'].');                
                        } else {
                            $audit_str .= 'ERROR copying user access';
                            $this->addError('ERROR copying user access for user ID['.$user_id.'] from user ID['.$from_user_id.'].');                
                        }
                    }  
                }   
            }  
        }  
        
        //audit any updates except for deletes as these are already audited 
        if($audit_count != 0) {
            Audit::action($this->db,$this->user_id,$audit_action,$audit_str);
        }  
            
        $this->mode = 'list';
        $html .= $this->viewTable();
            
        return $html;
    }

    //configure
    public function setup($param = []) 
    {
        $param=['row_name'=>'User','col_label'=>'name'];
        parent::setup($param);        

        $config = $this->getContainer('config');
        $login_user = $this->getContainer('user');

        //check if route access whitelist is required
        $route_access = $config->get('user','route_access');

        $this->addForeignKey(array('table'=>TABLE_AUDIT,'col_id'=>'user_id','message'=>'Audit trail'));

        $this->addTableCol(array('id'=>'user_id','type'=>'INTEGER','title'=>'User ID','key'=>true,'key_auto'=>true,'list'=>true));
        $this->addTableCol(array('id'=>'name','type'=>'STRING','title'=>'Name'));
        $this->addTableCol(array('id'=>'email','type'=>'EMAIL','title'=>'Email adress','hint'=>'(This must be unique to system and to all '.SITE_NAME.' users.)'));
        $this->addTableCol(array('id'=>'zone','type'=>'STRING','title'=>'Access zone','new'=>'ALL',
                                'hint'=>'(ALL user can access any zone in application!<br/>
                                         PUBLIC for public access only.)'));
        $this->addTableCol(array('id'=>'access','type'=>'STRING','title'=>'Access level','new'=>'ADMIN',
                                'hint'=>'(GOD can do anything, but most importantly create users and manage them!<br/>
                                         ADMIN allows users to add, and delete most data.<br/>
                                         USER allows users to add and edit but not delete data.<br/>
                                         VIEW allows users to see anything but not to modify or add any data!)'));
        if($route_access) {
            $this->addTableCol(array('id'=>'route_access','type'=>'BOOLEAN','title'=>'Access limit','hint'=>'(Check if you want to limit user to specific pages)'));
        }

        $this->addTableCol(array('id'=>'password','type'=>'PASSWORD','title'=>'Password','max'=>250,'list'=>false,
                                 'hint'=>'(NB: For NEW users please note password as it will be one-way encrypted when stored!<br/>To change existing user password click "Create New" checkbox, then enter new password and Submit)'));

        if($login_user->getAccessLevel() === 'GOD' and $login_user->getEmail() === 'mark@seriti.com') {
            //$this->addTableCol(array('id'=>'pwd_date','type'=>'DATE','title'=>'Password expiry date'));
            $this->addTableCol(array('id'=>'login_fail','type'=>'INTEGER','title'=>'Login FAIL count','list'=>false));
            $this->addTableCol(array('id'=>'email_token','type'=>'STRING','title'=>'Email token','list'=>false,'required'=>false));
            $this->addTableCol(array('id'=>'email_token_expire','type'=>'DATETIME','title'=>'Email token expiry date','list'=>false,'required'=>false));
            //$this->addTableCol(array('id'=>'pwd_salt','type'=>'STRING','title'=>'Password salt','list'=>false));
            $this->addTableCol(array('id'=>'status','type'=>'STRING','title'=>'Status','list'=>true));
        } else { 
            $this->addSql('WHERE','T.status <> "HIDE"');
        }    

        $this->addAction(array('type'=>'check_box','text'=>'')); 
        $this->addAction(array('type'=>'edit','text'=>'edit','icon_text'=>'edit'));
        $this->addAction(array('type'=>'delete','text'=>'delete','pos'=>'R','icon_text'=>'delete'));

        if($route_access) {
            $this->addAction(array('type'=>'popup','text'=>'Allowed pages','url'=>'user_route','mode'=>'view','width'=>600,'height'=>600,'verify'=>true));
            $this->addSearch(array('user_id','name','email','access','route_access'),array('rows'=>2));    
        } else {
            $this->addSearch(array('user_id','name','email','access'),array('rows'=>2));    
        }
        
        

        $this->addSelect('zone',['list'=>$config->get('user','zone'),'list_assoc'=>false]);
        $this->addSelect('access',['list'=>$config->get('user','access'),'list_assoc'=>false]);
        $this->addSelect('status',['list'=>$config->get('user','status'),'list_assoc'=>false]);

    }

    protected function verifyRowAction($action,$data) 
    {
        $valid = false;
        if($action['url'] === 'user_route') $valid = $data['route_access'];

        return $valid;
    }
}
