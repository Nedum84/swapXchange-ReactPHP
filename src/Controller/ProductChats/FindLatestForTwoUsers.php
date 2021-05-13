<?php

namespace App\Controller\ProductChats;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductChatsServices;

final class FindLatestForTwoUsers{
    private $productChatsServices;

    public function __construct(Database $db){
        $this->productChatsServices = new ProductChatsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $second_user_id){
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->productChatsServices->findLatestForTwoUsers($user_id, $second_user_id)
            ->then(function(array $response) {
                if(\count($response)==0)
                    return JsonResponse::badRequest("No product chat found!");
                return JsonResponse::ok(["product_chat" => $response]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
