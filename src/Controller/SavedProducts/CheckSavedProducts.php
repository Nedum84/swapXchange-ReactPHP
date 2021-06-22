<?php
namespace App\Controller\SavedProducts;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\SavedServices;


final class CheckSavedProducts{
    private $savedServices;

    public function __construct(Database $db){
        $this->savedServices = new SavedServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $product_id){
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->savedServices->findOne($user_id, $product_id)
            ->then(function( $response) {
                if(\count($response)==0){
                    return JsonResponse::ok(["is_saved"=>false]);
                }
                return JsonResponse::ok(["is_saved"=>true]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
