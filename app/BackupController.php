<?php
namespace App;

use Psr\Container\ContainerInterface;
use App\Backup;
use Seriti\Tools\TABLE_BACKUP;

class BackupController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        
        if($this->container->user->getAccessLevel() !== 'GOD') {
            $template['html'] = '<h1>Insufficient access rights!</h1>';
        } else {  
            $backup = new Backup($this->container->mysql,$this->container,TABLE_BACKUP);
            

            $param = [];
            $backup->setup($param);
            $html = $backup->process();
                        
            $template['html'] = $html;
            $template['title'] = 'Admin manage backups';
        }    

        return $this->container->view->render($response,'admin.php',$template);
    }
}