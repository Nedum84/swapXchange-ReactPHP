<?php

namespace App\Controller\ProductViews;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductViewsServices;

final class FindAll{
    private $productViewsServices;

    public function __construct(Database $db){
        $this->productViewsServices = new ProductViewsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $product_id){
        
        return $this->productViewsServices->findAll($product_id)
        ->then(function(array $response) {
            return JsonResponse::ok([ "views" => $response ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
