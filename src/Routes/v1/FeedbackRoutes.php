<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class FeedbackRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/all/{status}', new \App\Controller\Feedback\FindAll($this->dbCon));
        $this->routes->post('', new \App\Controller\Feedback\CreateFeedback($this->dbCon));
        $this->routes->patch('/{id}', new \App\Controller\Feedback\UpdateFeedback($this->dbCon));
        $this->routes->get('/{id}', new \App\Controller\Feedback\FindOne($this->dbCon));
    }
}

?>