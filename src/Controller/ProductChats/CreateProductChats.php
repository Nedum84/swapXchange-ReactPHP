<?php

namespace App\Controller\ProductChats;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductChatsServices;



final class CreateProductChats{
    private $productChatsServices;

    public function __construct(Database $db){
        $this->productChatsServices = new ProductChatsServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = $request->getBody();

        return new \React\Promise\Promise(function ($resolve) use ($body, $request) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve, &$requestBody, $request) {
                $body               = json_decode($requestBody, true);
                //User details...
                $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;
                
                $pChat = new \App\Models\ProductChatsModel();
                $pChat->product_id        = $body['product_id']; 
                $pChat->offer_product_id  = $body['offer_product_id']??'0'; 
                $pChat->sender_id        = $body['sender_id']??$user_id; 
                $pChat->receiver_id        = $body['receiver_id']??'0'; 
                $pChat->sender_closed_deal = $body['sender_closed_deal']??'0'; 
                $pChat->receiver_closed_deal = $body['receiver_closed_deal']??'0'; 
                $pChat->chat_status     = $body['chat_status']??null; 

                $resolve(
                    $this->productChatsServices->create($pChat)
                       ->then(
                           function ($response) {
                               if(gettype($response)!=="array"){
                                   return JsonResponse::badRequest($response);
                               };
                               return JsonResponse::created(["product_chat" => $response]);
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
