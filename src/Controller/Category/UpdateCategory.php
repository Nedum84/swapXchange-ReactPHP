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
        // $body = json_decode((string) $request->getBody(), true);
        $body = $request->getBody();

        return new \React\Promise\Promise(function ($resolve) use ($body, $request, $category_id) {
            $requestBody='';
            $body->on('data', function ($chunk) use (&$requestBody) {
                $requestBody .= $chunk;
            });
            $body->on('close', function () use ($resolve, &$requestBody, $request, $category_id) {
                $body               = json_decode($requestBody, true);
                $category_name        = $body['category_name'] ?? ''; 
                $category_icon        = $body['category_icon'] ?? ''; 
                $idx                  = $body['idx']; 

                $resolve(
                    $this->categoryServices->update($category_id, $category_name, $category_icon, $idx, $user_id) 
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
                   )
                );
            });
        });
    }
}
