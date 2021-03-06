<?php

namespace App\Controller\Category;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\CategoryServices;



final class CreateCategory{
    private $categoryServices;

    public function __construct(Database $db){
        $this->categoryServices = new CategoryServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $category_name        = $body['category_name'] ?? ''; 
        $category_icon        = $body['category_icon'] ?? ''; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->categoryServices->create($category_name, $category_icon, $user_id) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::created(["category" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );

    }
}
