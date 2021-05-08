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
        try {
            return $this->productServices->findByProductId($product_id)
                ->then(function(array $product) {
                    return JsonResponse::ok(["product" => $product]);
                });
          } catch (\Throwable $er) {
            return JsonResponse::badRequest($er);
          }
    }
}
