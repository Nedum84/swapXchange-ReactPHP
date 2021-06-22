<?php

namespace App\Controller\SavedProducts;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\SavedServices;

final class FindAll{
    private $savedServices;

    public function __construct(Database $db){
        $this->savedServices = new SavedServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $offset=0, int $limit=10){
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;
        
        return $this->savedServices->findAll($user_id, $offset, $limit)
        ->then(function(array $products) {
            return JsonResponse::ok([ "products" => $products ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
