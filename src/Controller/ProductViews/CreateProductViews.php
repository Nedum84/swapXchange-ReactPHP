<?php

namespace App\Controller\ProductViews;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductViewsServices;



final class CreateProductViews{
    private $productViewsServices;

    public function __construct(Database $db){
        $this->productViewsServices = new ProductViewsServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = $request->getBody();

        return new \React\Promise\Promise(function ($resolve) use ($body, $request) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve, &$requestBody, $request) {
                $body             = json_decode($requestBody, true);
                $product_id       = $body['product_id'] ?? ''; 
                //User details...
                $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;
                $resolve(
                    $this->productViewsServices->create($user_id, $product_id) 
                       ->then(
                           function ($response) {
                            return JsonResponse::ok($response);
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
