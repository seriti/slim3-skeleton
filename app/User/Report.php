<?php
namespace App\User;

use Seriti\Tools\Form;
use Seriti\Tools\Report AS ReportTool;

class Report extends ReportTool
{
     

    //configure
    public function setup() 
    {
        //$this->report_header = '';
        $this->always_list_reports = true;

        $param = ['input'=>['select_dates','select_user']];
        $this->addReport('USER_ACTIVITY','User general activity',$param); 
        $this->addReport('USER_ACTION','User actions',$param); 
        
        $this->addInput('select_user','Select user');
        $this->addInput('select_dates','Select period dates');
        //not currently used, only HTML format
        $this->addInput('select_format','Select Report format');
    }

    protected function viewInput($id,$form = []) 
    {
        $html = '';
        
        if($id === 'select_user') {
            $param = [];
            $param['xtra'] = array('ALL'=>'ALL users');
            $param['class'] = 'form-control input-medium';
            $sql = 'SELECT `user_id`,`name` FROM `'.TABLE_USER.'` WHERE `zone` = "ADMIN" OR `zone` = "ALL" ORDER BY `name`';
            if(isset($form['user_id'])) $user_id = $form['user_id']; else $user_id = 'ALL';
            $html .= Form::sqlList($sql,$this->db,'user_id',$user_id,$param);
        }   

        if($id === 'select_dates') {
            $param = [];
            $param['class'] = 'form-control bootstrap_date input-small';

            $date = getdate();

            if(isset($form['from_date'])) {
                $from_date = $form['from_date'];
            } else {
                $from_date = date('Y-m-d',mktime(0,0,0,$date['mon']-1,$date['mday'],$date['year']));;
            }

            if(isset($form['to_date'])) {
                $to_date = $form['to_date'];
            } else {
                $to_date = date('Y-m-d');;
            }     
            
            $html .= '<table>
                        <tr>
                          <td align="right" valign="top" width="20%"><b>From date : </b></td>
                          <td>'.Form::textInput('from_date',$from_date,$param).'</td>
                        </tr>
                        <tr>
                          <td align="right" valign="top" width="20%"><b>To date : </b></td>
                          <td>'.Form::textInput('to_date',$to_date,$param).'</td>
                        </tr>
                     </table>';
        } 

        if($id === 'select_format') {
            if(isset($form['format'])) $format = $form['format']; else $format = 'HTML';
            $html.= Form::radiobutton('format','PDF',$format).'&nbsp;<img src="/images/pdf_icon.gif">&nbsp;PDF document<br/>';
            $html.= Form::radiobutton('format','CSV',$format).'&nbsp;<img src="/images/excel_icon.gif">&nbsp;CSV/Excel document<br/>';
            $html.= Form::radiobutton('format','HTML',$format).'&nbsp;Show on page<br/>';
        }

        return $html;       
    }

    protected function processReport($id,$form = []) 
    {
        $html = '';
        $error = '';
        
        if($id === 'USER_ACTIVITY') {
            $options = [];
            $options['format'] = $form['format'];
            $html .= Helpers::activityReport($this->db,$form['user_id'],$form['from_date'],$form['to_date'],$options,$error);
            if($error !== '') $this->addError($error);
        }

        if($id === 'USER_ACTION') {
            $options = [];
            $options['format'] = $form['format'];
            $html .= Helpers::actionsReport($this->db,$form['user_id'],$form['from_date'],$form['to_date'],$options,$error); 
            if($error !== '') $this->addError($error);
        }

        
        return $html;
    }
}

?>