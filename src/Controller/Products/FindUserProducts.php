<?php

namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductServices;

final class FindUserProducts{
    private $productServices;

    public function __construct(Database $db){
        $this->productServices = new ProductServices($db);
    }

    public function __invoke(ServerRequestInterface $request, $user_id, $filter="all", int $offset=0, int $limit=10){
        
        return $this->productServices->findUserProducts($user_id, (string)$filter, (int)$offset, (int)$limit)
        ->then(function(array $products) {
            return JsonResponse::ok([ "products" => $products ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
