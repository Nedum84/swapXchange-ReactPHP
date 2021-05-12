<?php
namespace App\Controller\Category;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\CategoryServices;


final class UpdateCategory{
    private $categoryServices;

    public function __construct(Database $db){
        $this->categoryServices = new CategoryServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $category_id){
        $body = json_decode((string) $request->getBody(), true);
        $category_name        = $body['category_name'] ?? ''; 
        $category_icon        = $body['category_icon'] ?? ''; 
        $idx                  = $body['idx']; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->categoryServices->update($category_id, $category_name, $category_icon, $idx, $user_id) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::ok(["category" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
