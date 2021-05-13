<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class ImageUploadRoutes{
    private $routes;    
    private $projectRoot;
    private $filesystem;

    public function __construct(RouteCollector $r, string $projectRoot, \React\Filesystem\Filesystem $filesystem){
        $this->routes = $r;
        $this->projectRoot = $projectRoot;
        $this->filesystem = $filesystem;

        $this->_route();
    }

    private function _route() {
        $this->routes->post('', new \App\Controller\ImageUpload\UploadImage($this->projectRoot, $this->filesystem));
        $this->routes->delete('/uploads/{image_path}', new \App\Controller\ImageUpload\DeleteImage($this->projectRoot, $this->filesystem));
    }
}

?>