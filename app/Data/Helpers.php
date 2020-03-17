<?php
namespace App\Data;

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

use Psr\Container\ContainerInterface;


//static functions for client module
class Helpers {
    public static function checkTimeout($time_start,$time_max,$time_tolerance=5) 
    {
        if ($time_start == 0 or $time_max == 0) return false;
          
        $time_passed = time()-$time_start;
        $time_trigger = $time_max-$time_tolerance;
              
        if($time_passed > $time_trigger) return true; return false;
    }

    
    public static function getAllTables($db) 
    {
        $tables = [];

        $sql = 'SHOW tables';
        $list = $db->readSqlList($sql);
        if($list != 0) {
            foreach($list as $table_name) {
                //remove spaces and period for valid array key
                $table_id = str_replace('.','_',$table_name);
                $tables[$table_id] = $table_name;
            }    
        }
        
        return $tables;
    } 

    public static function getAllTableCols($db,$table,$param = [])
    {
        $table_cols = [];
        if(!isset($param['type'])) $param['type'] = false;
        if(!isset($param['key'])) $param['key'] = false;

        $sql = 'SHOW COLUMNS FROM '.$table;
        $cols = $db->readSqlArray($sql); 
        foreach($cols as $col_id => $col) {
            $col_id = str_replace('.','_',$col_id);

            $desc = $col_id;
            if($param['type']) $desc.': '.$col['Type'];
            if($param['key']) $desc.': '.$col['Key'];
            
            $table_cols[$col_id] = $desc;
        }

        return $table_cols; 
    }    
}
