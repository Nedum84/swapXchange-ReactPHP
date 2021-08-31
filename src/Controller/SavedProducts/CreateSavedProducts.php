<?php

namespace App\Controller\SavedProducts;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\SavedServices;



final class CreateSavedProducts{
    private $savedServices;

    public function __construct(Database $db){
        $this->savedServices = new SavedServices($db);
    }


    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $product_id       = $body['product_id'] ?? ''; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;
            return $this->savedServices->create($user_id, $product_id) 
               ->then(
                   function ($response) {
                    return JsonResponse::ok($response);
                   },
                   function ($error) {
                       return JsonResponse::badRequest($error->getMessage()??$error);
                   }
                );

    }
}
