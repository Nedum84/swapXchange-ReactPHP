<?php
namespace App\Controller\SavedProducts;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\SavedServices;

final class RemoveSavedProducts{
    private $savedServices;

    public function __construct(Database $db){
        $this->savedServices = new SavedServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $product_id){
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->savedServices->remove($user_id, $product_id)
            ->then(function( $response) {
                return JsonResponse::ok($response);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
