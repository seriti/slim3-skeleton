<?php
namespace App\Data;

use Psr\Container\ContainerInterface;

use Seriti\Tools\Plupload;
use Seriti\Tools\BASE_UPLOAD;
use Seriti\Tools\UPLOAD_TEMP;
use Seriti\Tools\BASE_URL;
use Seriti\Tools\BASE_INCLUDE;

//this is only required for server php receiving code, js is irrellevant
class UploadTempController extends Plupload
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $param = [];
        $param['upload_dir'] = BASE_UPLOAD.UPLOAD_TEMP;
        $param['include_url_js'] = BASE_URL.BASE_INCLUDE.'plupload2/js/plupload.full.min.js';
        $param['max_file_size'] = 10000000;

        $upload = new Plupload();
        return $upload->process();
    }
}