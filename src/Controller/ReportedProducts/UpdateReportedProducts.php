<?php
namespace App\Controller\ReportedProducts;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\ReportedProductsServices;


final class UpdateReportedProducts{
    private $reportedServices;

    public function __construct(Database $db){
        $this->reportedServices = new ReportedProductsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, string $id){
        $body = json_decode((string) $request->getBody(), true);
        $status        = $body['status'] ?? ''; 
        //User details...
        $user_id = \App\Utils\GetAuthPayload::getPayload($request)->user_id;

        return $this->reportedServices->update($id, $user_id, $status) 
            ->then(
                function ($response) {
                    if(gettype($response)!=="array"){
                        return JsonResponse::badRequest($response);
                    };
                    return JsonResponse::ok(["reported_product" => $response]);
                },
                function ($error) {
                    return JsonResponse::badRequest($error->getMessage()??$error);
                }
            );
    }
}
