<?php

namespace App\Controller\Coins;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\CoinsServices;

final class FindAllByUserId{
    private $coinsServices;

    public function __construct(Database $db){
        $this->coinsServices = new CoinsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $user_id){

        return $this->coinsServices->findAllByUserId($user_id)
        ->then(function(array $response) {
            return JsonResponse::ok($response);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
