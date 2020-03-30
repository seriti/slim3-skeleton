<?php
namespace App\Data;

use Seriti\Tools\Backup AS BackupTool;

class Backup extends BackupTool 
{
    //configure
    public function setup($param = []) 
    {

        //$param['source']='LOCAL'; this will backup to local path
        //$param['path_backup']=''; 
        $param['source']='AMAZON';
        $param['bucket']=AWS_S3_BUCKET;
        //not used for AMAZON source but required and not normally a constant UPLOAD_BACKUP
        $param['path_backup'] = '';
        //other baths set top contstant defaults BASE_UPLOAD.UPLOAD_DOCS & UPLOAD_TEMP
        parent::setup($param);        

        //be very careful about enabling restore option
        $access = [];
        $access['restore'] = false;
        $this->modifyAccess($access);

        ///specify source code folders to ignore
        $this->setupIgnore('SUB_DIRECTORY','vendor');
        $this->setupIgnore('SUB_DIRECTORY','storage');
        $this->setupIgnore('SUB_DIRECTORY','logs');
    }
}
