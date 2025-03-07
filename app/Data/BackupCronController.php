<?php
namespace App\Data;

use Psr\Container\ContainerInterface;
use App\Data\Backup;
use Seriti\Tools\TABLE_BACKUP;

//must be called from same server 
if($_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR']) die('INVALID BACKUP CRONJOB ACCESS!');

//call this class from cronjob for regular automated backups 
class BackupCronController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        //TABLE_BACKUP is NOT updated by backupAnyDatabase()
        $backup = new Backup($this->container->mysql,$this->container,TABLE_BACKUP);
        
        $html = 'Nothing processed'; 

        $mode = 'none';
        if(isset($_GET['mode'])) $mode = $_GET['mode'];  

        $type = 'ALL';
        if(isset($_GET['type'])) $type = $_GET['type'];    

        if($mode !== 'none') {
            $param = [];
            $backup->setup($param);
            
            $param = [];
            $param['db_default'] = true;
            if($mode === 'db_day') $param['name_suffix'] = 'DAY';
            if($mode === 'db_month') $param['name_suffix'] = 'MONTH';
            if($mode === 'db_date') $param['name_suffix'] = 'DATE';

            //ALL tables except those excluded
            if($type === 'ALL') $param['type'] = 'MYSQLDUMP';
            //ONLY tables specifically included
            if($type === 'TABLES') {
                $param['type'] = 'MYSQLDUMP_TABLES';
                $param['name_tables'] = '_salt_tables_';
            }    

            $html = $backup->backupAnyDatabase($param);
        }   

        return $html;
    }
}