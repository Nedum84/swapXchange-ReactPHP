<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class CategoryRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/all', new \App\Controller\Category\FindAll($this->dbCon));
        $this->routes->post('', new \App\Controller\Category\CreateCategory($this->dbCon));
        $this->routes->patch('/{category_id}', new \App\Controller\Category\UpdateCategory($this->dbCon));
        $this->routes->get('/{category_id}', new \App\Controller\Category\FindOne($this->dbCon));
    }
}

?>