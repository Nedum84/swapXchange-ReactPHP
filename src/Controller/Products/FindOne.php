<?php

namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductServices;

final class FindOne{
    private $productServices;

    public function __construct(Database $db){
        $this->productServices = new ProductServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $product_id){
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->productServices->findOne($product_id, $user_id)
            ->then(function(array $product) {
                if(count($product)==0){
                    return JsonResponse::notFound();
                };
                return JsonResponse::ok(["product" => $product]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
