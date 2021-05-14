<?php

namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductServices;

final class FindMyProducts{
    private $productServices;

    public function __construct(Database $db){
        $this->productServices = new ProductServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $offset=0, int $limit=10){
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;
        
        return $this->productServices->findMyProducts($user_id, (int)$offset, (int)$limit)
        ->then(function(array $products) {
            return JsonResponse::ok([ "products" => $products ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
