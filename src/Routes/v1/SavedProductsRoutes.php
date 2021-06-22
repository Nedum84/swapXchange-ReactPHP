<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class SavedProductsRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/all[/{offset}[/{limit}]]', new \App\Controller\SavedProducts\FindAll($this->dbCon));
        $this->routes->get('/{product_id}', new \App\Controller\SavedProducts\CheckSavedProducts($this->dbCon));
        $this->routes->post('', new \App\Controller\SavedProducts\CreateSavedProducts($this->dbCon));
        $this->routes->delete('/{product_id}', new \App\Controller\SavedProducts\RemoveSavedProducts($this->dbCon));
    }
}

?>