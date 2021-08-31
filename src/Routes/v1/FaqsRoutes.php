<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class FaqsRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/all', new \App\Controller\Faqs\FindAll($this->dbCon));
        $this->routes->post('', new \App\Controller\Faqs\CreateFaqs($this->dbCon));
        $this->routes->patch('/{faq_id}', new \App\Controller\Faqs\UpdateFaqs($this->dbCon));
        $this->routes->get('/{faq_id}', new \App\Controller\Faqs\FindOne($this->dbCon));
    }
}

?>