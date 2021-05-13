<?php

namespace App\Controller\ProductImage;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ProductImageServices;

final class FindOne{
    private $categoryServices;

    public function __construct(Database $db){
        $this->categoryServices = new ProductImageServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $id){

        return $this->categoryServices->findOne($id)
            ->then(
                function (\App\Models\ServiceResponse $response) {
                    if($response->success){
                        return JsonResponse::ok(["image_product" => $response->data]);
                    };
                    return JsonResponse::badRequest($response->data);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
        );
    }
}
