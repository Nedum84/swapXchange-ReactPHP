<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class TokenRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->post('/refresh', new \App\Controller\Token\RefreshToken($this->dbCon));
    }
}

?>