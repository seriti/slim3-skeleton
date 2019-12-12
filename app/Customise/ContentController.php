<?php
namespace App\Customise;

use Psr\Container\ContainerInterface;
use App\Customise\Content;

class ContentController
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args)
    {
        $table = TABLE_PREFIX.'content';
        
        $table = new Content($this->container->mysql,$this->container,$table);

        $table->setup();
        $html = $table->processTable();
        
        $template['html'] = $html;
        $template['title'] = MODULE_LOGO.'Custom content';
        
        return $this->container->view->render($response,'admin.php',$template);
    }
}