<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class ImageUploadRoutes{
    private $routes;    
    private $dir;
    private $filesystem;

    public function __construct(RouteCollector $r, string $dir, \React\Filesystem\Filesystem $filesystem){
        $this->routes = $r;
        $this->dir = $dir;
        $this->filesystem = $filesystem;

        $this->_route();
    }

    private function _route() {
        $this->routes->post('', new \App\Controller\ImageUpload\UploadImage($this->dir, $this->filesystem));
        $this->routes->delete('/{image_url}', new \App\Controller\ImageUpload\UploadImage($this->dir, $this->filesystem));
    }
}

?>