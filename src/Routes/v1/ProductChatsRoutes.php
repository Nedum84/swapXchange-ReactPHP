<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class ProductChatsRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/all', new \App\Controller\ProductChats\FindAll($this->dbCon));
        $this->routes->post('', new \App\Controller\ProductChats\CreateProductChats($this->dbCon));
        $this->routes->patch('/{id}', new \App\Controller\ProductChats\UpdateProductChats($this->dbCon));
        $this->routes->get('/user/{user_id}', new \App\Controller\ProductChats\FindLatestForTwoUsers($this->dbCon));
        $this->routes->get('/{id}', new \App\Controller\ProductChats\FindOne($this->dbCon));
    }
}

?>