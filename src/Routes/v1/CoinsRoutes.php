<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class CoinsRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->post('', new \App\Controller\Coins\CreateCoins($this->dbCon));
        $this->routes->post('/{user_id}', new \App\Controller\Coins\CreateCoinsForUser($this->dbCon));
        $this->routes->get('/me', new \App\Controller\Coins\GetBalance($this->dbCon));
        $this->routes->get('/{user_id}', new \App\Controller\Coins\FindAllByUserId($this->dbCon));
    }
}

?>