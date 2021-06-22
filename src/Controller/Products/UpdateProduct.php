<?php
namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductServices;


use App\Models\ProductModel;

final class UpdateProduct{
    private $productServices;

    public function __construct(Database $db){
        $this->productServices = new ProductServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $product_id){
        // $body = json_decode((string) $request->getBody(), true);
        $body = $request->getBody();
        assert($body instanceof \Psr\Http\Message\StreamInterface);
        assert($body instanceof \React\Stream\ReadableStreamInterface);

        
        return new \React\Promise\Promise(function ($resolve) use ($body, $request, $product_id) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve, &$requestBody, $request, $product_id) {
                $body               = json_decode($requestBody, true);
                $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;
                $product = new ProductModel();

                $product->product_id        = $product_id ?? $body['product_id'] ?? ''; 
                $product->product_name      = $body['product_name'] ?? ''; 
                $product->category          = $body['category'] ?? ''; 
                $product->sub_category      = $body['sub_category'] ?? ''; 
                $product->price             = $body['price'] ?? ''; 
                $product->product_description = $body['product_description'] ?? ''; 
                $product->product_suggestion= $body['product_suggestion'] ?? ''; 
                $product->product_condition = $body['product_condition'] ?? ''; 
                $product->product_status    = $body['product_status'] ?? ''; 
                $product->timestamp         = $body['timestamp'] ?? ''; 
                $product->user_id           = $user_id ?? $body['user_id'] ?? ''; 
                $product->user_address      = $body['user_address'] ?? ''; 
                $product->user_address_city = $body['user_address_city'] ?? ''; 
                $product->user_address_lat  = $body['user_address_lat'] ?? ''; 
                $product->user_address_long = $body['user_address_long'] ?? '';
                
                $resolve(
                    $this->productServices->update($product, $product_id) 
                        ->then(
                            function (array $product) {
                                if(count($product)==0){
                                    return JsonResponse::notFound();
                                };
                                return JsonResponse::ok(["product" => $product]);
                            },
                            function ($error) {
                                return JsonResponse::badRequest($error->getMessage()??$error);
                            }
                    )
                );
            });
        });
    }
}
