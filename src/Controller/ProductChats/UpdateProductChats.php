<?php
namespace App\Controller\ProductChats;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductChatsServices;


final class UpdateProductChats{
    private $productChatsServices;

    public function __construct(Database $db){
        $this->productChatsServices = new ProductChatsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $product_chat_id){
        // $body = json_decode((string) $request->getBody(), true);
        $body = $request->getBody();

        return new \React\Promise\Promise(function ($resolve) use ($body) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve, &$requestBody) {
                $body               = json_decode($requestBody, true);
                $pChat = new \App\Models\ProductChatsModel();
                $pChat->id          = $product_chat_id??$body['id']; 
                $pChat->product_id        = $body['product_id']; 
                $pChat->offer_product_id  = $body['offer_product_id']; 
                $pChat->sender_id        = $body['sender_id']; 
                $pChat->receiver_id        = $body['receiver_id']; 
                $pChat->chat_status        = $body['chat_status']; 

                $resolve(
                    $this->productChatsServices->update($pChat) 
                       ->then(
                           function ($response) {
                               if(gettype($response)!=="array"){
                                   return JsonResponse::badRequest($response);
                               }elseif(empty($response)){
                                   return JsonResponse::badRequest("No product chat found");
                               };
                               return JsonResponse::ok(["product_chat" => $response]);
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
