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
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;
        
        $body = json_decode((string) $request->getBody(), true);
        $pChat = new \App\Models\ProductChatsModel();
        $pChat->product_id        = $body['product_id']; 
        $pChat->offer_product_id  = $body['offer_product_id']??''; 
        $pChat->sender_id        = $body['sender_id']??$user_id; 
        $pChat->receiver_id        = $body['receiver_id']??0; 

        return $this->productChatsServices->create($pChat) 
            ->then(
                function ($response) {
                    echo gettype($response);
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::created(["product_chat" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
