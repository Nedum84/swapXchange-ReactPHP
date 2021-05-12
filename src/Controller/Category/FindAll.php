<?php

namespace App\Controller\Category;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\CategoryServices;

final class FindAll{
    private $categoryServices;

    public function __construct(Database $db){
        $this->categoryServices = new CategoryServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;
        

        return $this->categoryServices->findAll($user_id)
        ->then(function(array $category) {
            return JsonResponse::ok([ "category" => $category ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
