<?php

namespace App\Controller\SubCategory;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\SubCategoryServices;



final class CreateSubCategory{
    private $subCategoryServices;

    public function __construct(Database $db){
        $this->subCategoryServices = new SubCategoryServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $category_id        = $body['category_id'] ?? ''; 
        $sub_category_name        = $body['sub_category_name'] ?? ''; 
        $sub_category_icon        = $body['sub_category_icon'] ?? ''; 

        //User details...
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;

            return $this->subCategoryServices->create($category_id, $sub_category_name, $sub_category_icon, $user_id) 
               ->then(
                   function ($response) {
                       if(gettype($response)!=="array"){
                           return JsonResponse::badRequest($response);
                       };
                       return JsonResponse::created(["subcategory" => $response]);
                   },
                   function ($error) {
                       return JsonResponse::badRequest($error->getMessage()??$error);
                   }
                );

    }
}
