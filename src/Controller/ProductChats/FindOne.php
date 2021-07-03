<?php

namespace App\Controller\ProductChats;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductChatsServices;

final class FindOne{
    private $productChatsServices;

    public function __construct(Database $db){
        $this->productChatsServices = new ProductChatsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, $id){

        return $this->productChatsServices->findById($id)
            ->then(function(array $response) {
                return JsonResponse::ok(["product_chat" => $response]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
