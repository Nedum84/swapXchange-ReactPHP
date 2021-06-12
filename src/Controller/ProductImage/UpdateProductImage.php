<?php
namespace App\Controller\ProductImage;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductImageServices;


final class UpdateProductImage{
    private $productImageServices;

    public function __construct(Database $db){
        $this->productImageServices = new ProductImageServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $id){
        // $body = json_decode((string) $request->getBody(), true);
        $body = $request->getBody();

        return new \React\Promise\Promise(function ($resolve) use ($body, $id) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve,&$requestBody, $id) {
                $body               = json_decode($requestBody, true);
                $product_id           = $body['product_id']; 
                $image_path           = $body['image_path']; 
                $idx                  = $body['idx']??"0"; 

                $resolve(
                    $this->productImageServices->update($id, $product_id, $image_path, $idx) 
                    ->then(
                        function (\App\Models\ServiceResponse $response) {
                            if($response->success){
                                return JsonResponse::ok(["image_product" => $response->data]);
                            };
                            return JsonResponse::badRequest($response->data);
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
