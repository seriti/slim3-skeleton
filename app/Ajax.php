<?php
namespace App;

use Psr\Container\ContainerInterface;
use Seriti\Tools\Secure;
use Seriti\Tools\Doc;
use Seriti\Tools\TABLE_CACHE;


class Ajax
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $mode = '';
        $output = '';

        if(isset($_GET['mode'])) $mode = Secure::clean('basic',$_GET['mode']);

        if($mode === 'csv') $output = $this->getCsv();

        return $output;
    }

    protected function getCsv()
    {
        $cache = $this->container['cache'];
        $cache->setCache('CSV');
        $csv_id = Secure::clean('basic',$_GET['id']);
        $data = $cache->retrieve($csv_id);

        $format = 'csv';
        $doc_name = $csv_id.'_'.date('Y-m-d').'.'.$format;
        Doc::outputDoc($data,$doc_name,'DOWNLOAD',$format);        
    }
    
}