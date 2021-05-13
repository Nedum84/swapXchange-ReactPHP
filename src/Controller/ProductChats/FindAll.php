<?php

namespace App\Controller\ProductChats;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductChatsServices;

final class FindAll{
    private $productChatsServices;

    public function __construct(Database $db){
        $this->productChatsServices = new ProductChatsServices($db);
    }

    public function __invoke(ServerRequestInterface $request){

        return $this->productChatsServices->findAll()
        ->then(function(array $product_chat) {
            return JsonResponse::ok([ "product_chat" => $product_chat ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
