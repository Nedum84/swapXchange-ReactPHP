<?php

namespace App\Routes\v1;


use FastRoute\RouteCollector;
use App\Database;

final class RoutesIndex{


    private $routes;    
    private $dbCon;
    private $filesystem;
    private $projectRoot = __FILE__;

    public function __construct(RouteCollector $r, Database $dbCon, \React\Filesystem\Filesystem $filesystem, string $projectRoot=__DIR__){
        $this->routes = $r;
        $this->dbCon = $dbCon;
        $this->filesystem = $filesystem;
        $this->projectRoot = $projectRoot;

        $this->_route();
    }

    private function _route() {
        //Products
        $this->routes->addGroup('/products', function (RouteCollector $r){
            new \App\Routes\v1\ProductRoutes($r, $this->dbCon);
        });
        //Users
        $this->routes->addGroup('/users', function (RouteCollector $r) {
            new \App\Routes\v1\UserRoutes($r, $this->dbCon);
        });
        //Users
        $this->routes->addGroup('/token', function (RouteCollector $r) {
            new \App\Routes\v1\TokenRoutes($r, $this->dbCon);
        });
        //Category
        $this->routes->addGroup('/category', function (RouteCollector $r) {
            new \App\Routes\v1\CategoryRoutes($r, $this->dbCon);
        });
        //Sub Category
        $this->routes->addGroup('/subcategory', function (RouteCollector $r) {
            new \App\Routes\v1\SubCategoryRoutes($r, $this->dbCon);
        });
        //Product Chats
        $this->routes->addGroup('/productchats', function (RouteCollector $r) {
            new \App\Routes\v1\ProductChatsRoutes($r, $this->dbCon);
        });
        //Product Chats
        $this->routes->addGroup('/image', function (RouteCollector $r) {
            new \App\Routes\v1\ImageUploadRoutes($r, $this->projectRoot, $this->filesystem);
        });
        //Product Images
        $this->routes->addGroup('/productimage', function (RouteCollector $r) {
            new \App\Routes\v1\ProductImageRoutes($r, $this->dbCon, $this->projectRoot, $this->filesystem);
        });
        //Agora Call SDK
        $this->routes->addGroup('/agora', function (RouteCollector $r) {
            new \App\Routes\v1\AgoraTokenRoutes($r, $this->dbCon);
        });
        //Coins and Credit
        $this->routes->addGroup('/coins', function (RouteCollector $r) {
            new \App\Routes\v1\CoinsRoutes($r, $this->dbCon);
        });
    }
}

?>