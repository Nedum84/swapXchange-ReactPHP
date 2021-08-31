<?php

namespace App\Controller\ReportedProducts;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ReportedProductsServices;

final class FindAll{
    private $reportedServices;

    public function __construct(Database $db){
        $this->reportedServices = new ReportedProductsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $status){

        return $this->reportedServices->findAll($status)
        ->then(function(array $response) {
            return JsonResponse::ok([ "reported_product" => $response ]);
        },function ($er){
            return JsonResponse::badRequest($er);
        });
    }
}
