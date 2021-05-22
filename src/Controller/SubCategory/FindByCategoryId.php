<?php

namespace App\Controller\SubCategory;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\SubCategoryServices;

final class FindByCategoryId{
    private $subCategoryServices;

    public function __construct(Database $db){
        $this->subCategoryServices = new SubCategoryServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $category_id){
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;
    
        return $this->subCategoryServices->findByCategoryId($user_id, $category_id)
            ->then(function(array $subcategory) {
                if(\count($subcategory)==0)
                    return JsonResponse::badRequest("No Sub category found");
                return JsonResponse::ok(["subcategory" => $subcategory]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
