<?php

namespace App\Controller\Coins;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\CoinsServices;



final class CreateCoinsForUser{
    private $coinsServices;

    public function __construct(Database $db){
        $this->coinsServices = new CoinsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $user_id){
        $body = $request->getBody();

        return new \React\Promise\Promise(function ($resolve) use ($body, $user_id) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve, &$requestBody, $user_id) {
                $body               = json_decode($requestBody, true);
                $amount             = $body['amount']??'0'; 
                $reference          =  'admin-'.$body['reference']; 
                $method_of_subscription  = $body['method_of_subscription'] ?? '';
                
                $resolve(
                    $this->coinsServices->create($user_id, $amount, $reference , $method_of_subscription) 
                       ->then(
                           function ($response) {
                               if(gettype($response)!=="array"){
                                   return JsonResponse::badRequest($response);
                               };
                               return JsonResponse::created($response);
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
