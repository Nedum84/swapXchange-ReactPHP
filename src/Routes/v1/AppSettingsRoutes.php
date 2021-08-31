<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class AppSettingsRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->patch('', new \App\Controller\AppSettings\UpdateAppSettings($this->dbCon));
        $this->routes->get('/{key}', new \App\Controller\AppSettings\FindOne($this->dbCon));
    }
}

?>