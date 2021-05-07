<?php
namespace App\User;

use Exception;
use Seriti\Tools\Calc;
use Seriti\Tools\Csv;
use Seriti\Tools\Doc;
use Seriti\Tools\Html;
use Seriti\Tools\Pdf;
use Seriti\Tools\Date;
use Seriti\Tools\SITE_TITLE;
use Seriti\Tools\BASE_UPLOAD;
use Seriti\Tools\UPLOAD_DOCS;
use Seriti\Tools\UPLOAD_TEMP;
use Seriti\Tools\SITE_NAME;

use Seriti\Tools\TABLE_AUDIT;
use Seriti\Tools\TABLE_USER;
use Seriti\Tools\TABLE_ROUTE;
use Seriti\Tools\TABLE_TOKEN;
use Seriti\Tools\TABLE_SYSTEM;

use Psr\Container\ContainerInterface;


//static functions for system users
class Helpers {
    
    public static function activityReport($db,$user_id,$date_from,$date_to,$param = [],&$error) 
    {
        $error = '';
        $html = '';
        $users = [];

        if(!isset($param['format'])) $param['format'] = 'HTML'; //or HTML
        if($param['format'] !== 'HTML') $error .= 'Only HTML/on page format supported';

        $title = 'Activity from '.$date_from.' to '.$date_to.' for ';

        if(!isset($param['zone'])) $param['zone'] = 'ADMIN'; 
        //session timeout in minutes
        if(!isset($param['session_timeout'])) $param['session_timeout'] = 20; 

        $sql = 'SELECT user_id,name,email FROM '.TABLE_USER.' WHERE (zone = "'.$param['zone'].'" OR zone = "ALL") ';
        if($user_id !== 'ALL') $sql .= 'AND user_id = "'.$db->escapeSql($user_id).'" ';
        $users = $db->readSqlArray($sql); 
        if($users == 0) $error .= 'No valid users found!'; 

        if($error !== '') return false;

        $data_base = [];
        $r = 0;
        $data_base[0][$r] = 'Date';
        $data_base[1][$r] = 'Start time';
        $data_base[2][$r] = 'Finish time';
        $data_base[3][$r] = 'Hours active';
        $data_base[4][$r] = 'Action count';
        
        foreach($users as $user_id => $user) {
            //look at ALL actions as indications of activity including logins
            $sql = 'SELECT YEAR(date) AS year, MONTH(date) AS month , DAY(date) AS day, MIN(date) AS start, Max(date) AS end, '. 
                          'TIMESTAMPDIFF(MINUTE,MIN(date),MAX(date)) AS minutes, COUNT(*) AS num  '.
                   'FROM '.TABLE_AUDIT.' '.
                   'WHERE user_id = "'.$user_id.'" AND DATE(date) >= "'.$db->escapeSql($date_from).'" AND DATE(date) <= "'.$db->escapeSql($date_to).'" '.
                   'GROUP BY YEAR(date),MONTH(date),DAY(date)';
            $first_col_key = false;
            $user_days = $db->readSqlArray($sql,$first_col_key);

            $data = $data_base;
            $hour_total = 0;
            $day_total = 0;
            
            foreach($user_days as $day) {
                $r++;
                $date = date('Y-m-d',mktime(0,0,0,$day['month'],$day['day'],$day['year']));
                //$hour_estimate = round(($day['minutes']+$param['session_timeout'])/60,1);
                $hour_estimate = round($day['minutes']/60,1);
                $hour_total += $hour_estimate;
                $day_total++;

                $data[0][$r] = $date;
                $data[1][$r] = substr($day['start'],11,5);
                $data[2][$r] = substr($day['end'],11,5);
                $data[3][$r] = $hour_estimate;
                $data[4][$r] = $day['num'];
            }


            $html .= '<h1>'.$title.$user['name'].', active days = '.$day_total.', hours = '.$hour_total.', average = '.round(($hour_total/$day_total),1).'</h1>'.
                     Html::arrayDumpHtml2($data);

        }

        

        return $html;
    }


    //excludes any SYSTEM related actions like USER LOGIN or TOKEN updates
    public static function actionsReport($db,$user_id,$date_from,$date_to,$param = [],&$error) 
    {
        $error = '';
        $html = '';

        if(!isset($param['format'])) $param['format'] = 'HTML'; //or HTML
        if($param['format'] !== 'HTML') $error .= 'Only HTML/on page format supported';

        $title = 'Actions from '.$date_from.' to '.$date_to.' for ';

        if(!isset($param['zone'])) $param['zone'] = 'ADMIN'; 
        if(!isset($param['exclude_tables'])) $param['exclude_tables'] = [TABLE_USER,TABLE_TOKEN,TABLE_SYSTEM];

        if($error !== '') return false;

        $sql_exclude = 'AND action NOT LIKE "LOGIN%" ';
        if(is_array($param['exclude_tables'])) {
            $sql_exclude .= 'AND action NOT LIKE "%'.implode('" AND action NOT LIKE "%',$param['exclude_tables']).'" ';    
        }

        
        if($user_id === 'ALL') {
            $title .= 'All users';
            $sql_user = '';
        } else {
            $sql = 'SELECT name FROM '.TABLE_USER.' WHERE user_id = "'.$db->escapeSql($user_id).'" ';
            $title .= 'user: '.$db->readSqlValue($sql);
            $sql_user = 'A.user_id = "'.$db->escapeSql($user_id).'" AND ';
        }    

        $sql = 'SELECT A.action,count(*) AS `count`  '.
               'FROM '.TABLE_AUDIT.' AS A JOIN '.TABLE_USER.' AS U ON(A.user_id = U.user_id AND (U.zone = "'.$param['zone'].'" OR U.zone = "ALL") ) '.
               'WHERE '.$sql_user.' DATE(A.date) >= "'.$db->escapeSql($date_from).'" AND DATE(A.date) <= "'.$db->escapeSql($date_to).'" '.$sql_exclude.
               'GROUP BY A.action ';
        $first_col_key = false;
        $actions = $db->readSqlArray($sql,$first_col_key);
                
        $html .= '<h1>'.$title.'</h1>'.Html::arrayDumpHtml($actions);

        return $html;
    }

    //copy user access setting from one user to any other user, unless from user has GOD access
    //NB: similar function in Seriti/User class
    public function copyUserAccess($db,$from_user_id,$user_id,&$error) 
    {

        $error = '';
        $error_tmp = '';

        $sql = 'SELECT * FROM '.TABLE_USER.' WHERE user_id = "'.$db->escapeSql($from_user_id).'" ';
        $from_user = $db->readSqlArray($sql);
        if($from_user['access'] === 'GOD') {
            $error = 'Cannot copy access details for GOD access level.';
        } else {
            $copy = [];
            $copy['zone'] = $from_user['zone'];
            $copy['access'] = $from_user['access'];
            $copy['route_access'] = $from_user['route_access'];

            $where = ['user_id'=>$user_id];
            $db->updateRecord(TABLE_USER,$copy,$where,$error_tmp);
            if($error_tmp !== '') {
                $error .= 'Could not update user access zone and access';
            } else {
                if($from_user['route_access']) {
                    $sql = 'DELETE FROM '.TABLE_ROUTE.' '.
                           'WHERE user_id = "'.$db->escapeSql($user_id).'" ';
                    $db->executeSql($sql,$error_tmp);
                    if($error_tmp !== '') {
                        $error .= 'Could not remove old route access setting';
                    } else {
                        $sql = 'SELECT * FROM '.TABLE_ROUTE.' '.
                               'WHERE user_id = "'.$db->escapeSql($from_user_id).'" ';
                        $routes = $db->readSqlArray($sql); 
                        if($routes != 0) {
                            foreach($routes as $route) {
                                $route['user_id'] = $user_id;
                                $db->insertRecord(TABLE_ROUTE,$route,$error_tmp);
                                if($error_tmp !== '') $error .= 'Could not insert user access route';
                            }
                        }
                    }    
                }
            }
        }

        if($error === '') return true; else return false; 
    }
}
