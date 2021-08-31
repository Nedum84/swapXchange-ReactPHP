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
        $body = json_decode((string) $request->getBody(), true);
        $amount             = $body['amount']??'0'; 
        $reference          =  $body['reference'].'_admin'; 
        $method_of_subscription  = $body['method_of_subscription'] ?? '';

        $user = \App\Utils\GetAuthPayload::getPayload($request);
        $userLevel = intval($user->user_level);
        if($userLevel==1){
            return JsonResponse::unauthorized("You are not authorized to access this route");
        }
        
            return $this->coinsServices->create($user_id, $amount, $reference , $method_of_subscription) 
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
                );
    }
}
