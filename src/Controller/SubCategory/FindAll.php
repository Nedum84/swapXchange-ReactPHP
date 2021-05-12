<?php

namespace App\Controller\SubCategory;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\SubCategoryServices;

final class FindAll{
    private $subCategoryServices;

    public function __construct(Database $db){
        $this->subCategoryServices = new SubCategoryServices($db);
    }

    public function __invoke(ServerRequestInterface $request ){
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;
    

        return $this->subCategoryServices->findAll($user_id)
        ->then(function(array $category) {
            return JsonResponse::ok([ "subcategory" => $category ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
