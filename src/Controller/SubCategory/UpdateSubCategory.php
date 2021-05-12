<?php
namespace App\Controller\SubCategory;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\SubCategoryServices;


final class UpdateSubCategory{
    private $subCategoryServices;

    public function __construct(Database $db){
        $this->subCategoryServices = new SubCategoryServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $sub_category_id){
        $body = json_decode((string) $request->getBody(), true);
        $category_id        = $body['category_id'] ?? ''; 
        $sub_category_name  = $body['sub_category_name'] ?? ''; 
        $sub_category_icon  = $body['sub_category_icon'] ?? ''; 
        $idx                 = $body['idx']; 
        //User details...
        $authPayload = \App\Utils\GetAuthPayload::getPayload($request);
        $user_id = $authPayload->user_id;

        return $this->subCategoryServices->update(
                        $category_id, 
                        $sub_category_id, 
                        $sub_category_name, 
                        $sub_category_icon, 
                        $idx, 
                        $user_id
                    ) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::ok(["subcategory" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
