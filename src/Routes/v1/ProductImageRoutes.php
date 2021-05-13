<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class ProductImageRoutes{


    private $routes;    
    private $dbCon;
    private $projectRoot;
    private $filesystem;

    public function __construct(RouteCollector $r, Database $dbCon, string $projectRoot, \React\Filesystem\Filesystem $filesystem){
        $this->routes = $r;
        $this->dbCon = $dbCon;
        $this->projectRoot = $projectRoot;
        $this->filesystem = $filesystem;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/all/{product_id}', new \App\Controller\ProductImage\FindAll($this->dbCon));
        $this->routes->post('', new \App\Controller\ProductImage\CreateProductImage($this->dbCon));
        $this->routes->patch('/{id:\d+}', new \App\Controller\ProductImage\UpdateProductImage($this->dbCon));
        $this->routes->get('/{id:\d+}', new \App\Controller\ProductImage\FindOne($this->dbCon));
        $this->routes->delete('/{id:\d+}', new \App\Controller\ProductImage\DeleteProductImage($this->dbCon, $this->projectRoot, $this->filesystem));
    }
}

?>