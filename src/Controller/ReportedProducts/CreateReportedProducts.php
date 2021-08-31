<?php

namespace App\Controller\ReportedProducts;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ReportedProductsServices;



final class CreateReportedProducts{
    private $reportedServices;

    public function __construct(Database $db){
        $this->reportedServices = new ReportedProductsServices($db);
    }

    public function __invoke(ServerRequestInterface $request){
        $body = json_decode((string) $request->getBody(), true);
        $product_id        = $body['product_id'] ?? ''; 
        $reported_message        = $body['reported_message'] ?? ''; 
        $uploaded_by        = $body['uploaded_by'] ?? ''; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->reportedServices->create($user_id, $product_id, $reported_message, $uploaded_by) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::created(["reported_product" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );

    }
}
