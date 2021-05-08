<?php

namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductServices;

final class FindAll{
    private $productServices;

    public function __construct(Database $db){
        $this->productServices = new ProductServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $offset=1, int $limit=10){
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;
        
        try {
            return $this->productServices->findAll($user_id, $offset, $limit)
                ->then(function(array $products) {
                    return JsonResponse::ok([ "products" => $products ]);
                });
          } catch (\Throwable $er) {
            return JsonResponse::badRequest($er);
          }
    }
}
