<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class SubCategoryRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        $this->routes->get('/all', new \App\Controller\SubCategory\FindAll($this->dbCon));
        $this->routes->post('', new \App\Controller\SubCategory\CreateSubCategory($this->dbCon));
        $this->routes->patch('/{sub_cat_id}', new \App\Controller\SubCategory\UpdateSubCategory($this->dbCon));
        $this->routes->get('/{sub_cat_id}', new \App\Controller\SubCategory\FindOne($this->dbCon));
        $this->routes->get('/category/{cat_id}', new \App\Controller\SubCategory\FindByCategoryId($this->dbCon));
    }
}

?>