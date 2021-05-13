<?php

namespace App\Controller\ProductImage;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductImageServices;

final class FindAll{
    private $productImageServices;

    public function __construct(Database $db){
        $this->productImageServices = new ProductImageServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $product_id = null){

        return $this->productImageServices->findAllByProductId($product_id)
        ->then(function(\App\Models\ServiceResponse $response) {
            if($response->success){
                return JsonResponse::ok(["image_product" => $response->data]);
            };
            return JsonResponse::badRequest($response->data);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
