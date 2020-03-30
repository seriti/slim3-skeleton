<?php
namespace App\User;

use Psr\Container\ContainerInterface;

use App\User\Report;

class ReportController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $report = new Report($this->container->mysql,$this->container);
        
        $report->setup();
        $html = $report->process();

        $template['html'] = $html;
        $template['title'] = 'All Admin user reports';
        $template['javascript'] = $report->getJavascript();

        return $this->container->view->render($response,'admin.php',$template);
    }
}