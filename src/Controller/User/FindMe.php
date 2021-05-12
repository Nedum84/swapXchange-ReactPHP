<?php

namespace App\Controller\User;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\UserServices;

final class FindMe{
    private $userServices;

    public function __construct(Database $db){
        $this->userServices = new UserServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;
        
        try {
            return $this->userServices->findOne($user_id)
                ->then(function(array $user) {
                    return JsonResponse::ok([ "user" => $user]);
                });
          } catch (\Throwable $er) {
            return JsonResponse::badRequest($er);
          }
    }
}
