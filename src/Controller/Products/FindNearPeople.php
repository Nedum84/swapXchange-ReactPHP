<?php

namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductServices;

final class FindNearPeople{
    private $productServices;

    public function __construct(Database $db){
        $this->productServices = new ProductServices($db);
    }

    public function __invoke(ServerRequestInterface $request, $product_lat, $product_long){

        return $this->productServices->findNearbyUsers($product_lat, $product_long)
            ->then(function(array $response) {
                return JsonResponse::ok(["users" => $response]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
