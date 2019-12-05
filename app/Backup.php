<?php
namespace App;

use Seriti\Tools\Backup AS BackupTool;

class Backup extends BackupTool 
{
    //configure
    public function setup($param = []) 
    {
        $param['source']='AMAZON';
        $param['bucket']='psspf-backup';
        //not used for AMAZON source but required and not normally a constant UPLOAD_BACKUP
        $param['path_backup'] = '';
        //other baths set top contstant defaults BASE_UPLOAD.UPLOAD_DOCS & UPLOAD_TEMP
        parent::setup($param);        

        $access = [];
        $access['restore'] = false;
        $this->modifyAccess($access);

        $this->setupIgnore('SUB_DIRECTORY','vendor');
        $this->setupIgnore('SUB_DIRECTORY','storage');
        $this->setupIgnore('SUB_DIRECTORY','logs');

        $this->setupIgnore('TABLE','files_old');

        $this->setupIgnore('TABLE','salt_member');
        $this->setupIgnore('TABLE','salt_member_new');
        $this->setupIgnore('TABLE','salt_member_old');

        $this->setupIgnore('TABLE','salt_client');
        $this->setupIgnore('TABLE','salt_client_new');
        $this->setupIgnore('TABLE','salt_client_old');

        $this->setupIgnore('TABLE','salt_people');
        $this->setupIgnore('TABLE','salt_people_new');
        $this->setupIgnore('TABLE','salt_people_old');

        $this->setupIgnore('TABLE','salt_section13a');
        $this->setupIgnore('TABLE','salt_section13a_new');
        $this->setupIgnore('TABLE','salt_section13a_old');

    }
}
