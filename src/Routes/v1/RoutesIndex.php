<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class RoutesIndex{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        //Products
        $this->routes->addGroup('/products', function (RouteCollector $r){
            new \App\Routes\v1\ProductRoutes($r, $this->dbCon);
        });
        //Users
        $this->routes->addGroup('/users', function (RouteCollector $r) {
            new \App\Routes\v1\UserRoutes($r, $this->dbCon);
        });
        //Users
        $this->routes->addGroup('/token', function (RouteCollector $r) {
            new \App\Routes\v1\TokenRoutes($r, $this->dbCon);
        });
        //Category
        $this->routes->addGroup('/category', function (RouteCollector $r) {
            new \App\Routes\v1\CategoryRoutes($r, $this->dbCon);
        });
        //Sub Category
        $this->routes->addGroup('/subcategory', function (RouteCollector $r) {
            new \App\Routes\v1\SubCategoryRoutes($r, $this->dbCon);
        });
    }
}

?>