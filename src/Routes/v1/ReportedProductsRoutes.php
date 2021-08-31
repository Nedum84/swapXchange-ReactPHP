<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class ReportedProductsRoutes{

    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/all/{status}', new \App\Controller\ReportedProducts\FindAll($this->dbCon));
        $this->routes->get('/{product_id}/{status}', new \App\Controller\ReportedProducts\FindByProductId($this->dbCon));
        $this->routes->post('', new \App\Controller\ReportedProducts\CreateReportedProducts($this->dbCon));
        $this->routes->patch('/{id}', new \App\Controller\ReportedProducts\UpdateReportedProducts($this->dbCon));
        $this->routes->get('/{id}', new \App\Controller\ReportedProducts\FindOne($this->dbCon));
    }
}

?>