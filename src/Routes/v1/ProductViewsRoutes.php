<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class ProductViewsRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/{product_id}', new \App\Controller\ProductViews\FindAll($this->dbCon));
        $this->routes->post('', new \App\Controller\ProductViews\CreateProductViews($this->dbCon));
    }
}

?>