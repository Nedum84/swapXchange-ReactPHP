<?php

namespace App\Controller\Coins;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\CoinsServices;

final class GetBalance{
    private $coinsServices;

    public function __construct(Database $db){
        $this->coinsServices = new CoinsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $category_id){
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->coinsServices->getBalance($user_id)
            ->then(function(array $balance) {
                if(\count($balance)==0)
                    return JsonResponse::badRequest("User Found");
                return JsonResponse::ok(["balance" => $balance]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
