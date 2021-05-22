<?php

namespace App\Controller\Products;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductServices;

final class FindSearchSuggestions{
    private $productServices;

    public function __construct(Database $db){
        $this->productServices = new ProductServices($db);
    }

    public function __invoke(ServerRequestInterface $request, $query){
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        
        return $this->productServices->findSuggestions($query, $user_id)
        ->then(function(array $suggestions) {
            return JsonResponse::ok([ "suggestions" => $suggestions ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
