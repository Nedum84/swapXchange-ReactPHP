<?php

namespace App\Controller\ReportedProducts;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ReportedProductsServices;

final class FindOne{
    private $reportedServices;

    public function __construct(Database $db){
        $this->reportedServices = new ReportedProductsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $id){

        return $this->reportedServices->findOne($id)
            ->then(function(array $response) {
                if(\count($response)==0)
                    return JsonResponse::badRequest("Nothing found");
                return JsonResponse::ok(["reported_product" => $response]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
