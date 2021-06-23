<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
// use App\Controller\Products\ProductController;
use App\Database;

final class ProductRoutes{


    private $routes;    
    private $dbCon;

    public function __construct(RouteCollector $r, Database $dbCon){
        $this->routes = $r;
        $this->dbCon = $dbCon;

        $this->_route();
    }

    private function _route() {
        //---> all/offset/limit  eg all/1/21
        $this->routes->get('/all[/{offset}[/{limit}]]', new \App\Controller\Products\FindAll($this->dbCon));
        //---> search suggestions
        $this->routes->get('/search/suggest/{query}', new \App\Controller\Products\FindSearchSuggestions($this->dbCon));
        //---> Search
        $this->routes->get('/search/{query}[/{filters}[/{offset}[/{limit}]]]', new \App\Controller\Products\FindBySearch($this->dbCon));
        $this->routes->get('/category/{category}[/{offset}[/{limit}]]', new \App\Controller\Products\FindByCategory($this->dbCon));
        $this->routes->get('/subcategory/{subcategory}[/{offset}[/{limit}[/{filters}]]]', new \App\Controller\Products\FindBySubCategory($this->dbCon));
        $this->routes->get('/me[/{offset}[/{limit}]]', new \App\Controller\Products\FindMyProducts($this->dbCon));
        $this->routes->get('/user/{user_id}[/{filter}[/{offset}[/{limit}]]]', new \App\Controller\Products\FindUserProducts($this->dbCon));
        $this->routes->get('/exchange/{product_id}[/{offset}[/{limit}]]', new \App\Controller\Products\FindExchangeOptions($this->dbCon));
        $this->routes->post('', new \App\Controller\Products\CreateProduct($this->dbCon));
        $this->routes->patch('/{product_id}', new \App\Controller\Products\UpdateProduct($this->dbCon));
        $this->routes->get('/{product_id}', new \App\Controller\Products\FindOne($this->dbCon));
        $this->routes->get('/nearbyuserss/{product_lat}/{product_long}', new \App\Controller\Products\FindNearPeople($this->dbCon));
    }
}

?>