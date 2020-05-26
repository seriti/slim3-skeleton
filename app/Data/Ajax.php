<?php
namespace App\Data;

use Psr\Container\ContainerInterface;
use Seriti\Tools\Secure;
use Seriti\Tools\Csv;
use Seriti\Tools\Doc;
use Seriti\Tools\TABLE_CACHE;


class Ajax
{
    protected $container;
    protected $db;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->mysql;
    }


    public function __invoke($request, $response, $args)
    {
        $mode = '';
        $output = '';

        if(isset($_GET['mode'])) $mode = Secure::clean('basic',$_GET['mode']);

        if($mode === 'csv') $output = $this->getCsv();
        if($mode === 'sql') $output = $this->getSqlCsv();

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

    protected function getSqlCsv()
    {
        $cache = $this->container['cache'];
        $cache->setCache('SQL');
        $sql_id = Secure::clean('basic',$_GET['id']);
        $sql = $cache->retrieve($sql_id);

        $data_set = $this->db->readSql($sql);
        if($data_set->num_rows > 0) {
            $data = Csv::mysqlDumpCsv($data_set);

            $format = 'csv';
            $doc_name = $sql_id.'_'.date('Y-m-d').'.'.$format;
            Doc::outputDoc($data,$doc_name,'DOWNLOAD',$format);
        }        
    }
    
}