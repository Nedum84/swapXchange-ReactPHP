<?php

namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductServices;


use App\Models\ProductModel;

final class CreateProduct{
    private $productServices;

    public function __construct(Database $db){
        $this->productServices = new ProductServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        $body = json_decode((string) $request->getBody(), true);
        $product = new ProductModel();
        $product->order_id          = $body['order_id'] ?? ''; 
        $product->product_name      = $body['product_name'] ?? ''; 
        $product->category          = $body['category'] ?? ''; 
        $product->sub_category      = $body['sub_category'] ?? ''; 
        $product->price             = $body['price'] ?? ''; 
        $product->product_description = $body['product_description'] ?? ''; 
        $product->product_suggestion = $body['product_suggestion'] ?? ''; 
        $product->product_condition    = $body['product_condition'] ?? ''; 
        $product->product_status    = $body['product_status'] ?? ''; 
        $product->user_id           = $user_id??$body['user_id'] ?? ''; 
        $product->user_address      = $body['user_address'] ?? ''; 
        $product->user_address_city = $body['user_address_city'] ?? ''; 
        $product->user_address_lat  = $body['user_address_lat'] ?? ''; 
        $product->user_address_long = $body['user_address_long'] ?? '';

        return $this->productServices->create($product)
            ->then(function (array $product) {
                    return JsonResponse::created(["product" => $product]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
