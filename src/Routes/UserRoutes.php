<?php

namespace App\Routes;


use FastRoute\RouteCollector;
use App\Database;

final class UserRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        // $this->routes->get('/all', new \App\Controller\User\FindAll($this->dbCon));
        $this->routes->post('', new \App\Controller\User\CreateUser($this->dbCon));
        $this->routes->put('/{user_id}', new \App\Controller\User\UpdateUser($this->dbCon));
        $this->routes->put('/adress/{user_id}', new \App\Controller\User\UpdateUserAddress($this->dbCon));
        $this->routes->get('/{user_id}', new \App\Controller\User\FindOne($this->dbCon));
    }
}

?>