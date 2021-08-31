<?php

namespace App\Controller\Faqs;

use App\JsonResponse;
use App\Database;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\FaqsServices;

final class FindOne{
    private $faqsServices;

    public function __construct(Database $db){
        $this->faqsServices = new FaqsServices($db);
    }

    public function __invoke(ServerRequestInterface $request, int $faq_id){

        return $this->faqsServices->findOne($faq_id)
            ->then(function(array $response) {
                if(\count($response)==0)
                    return JsonResponse::badRequest("Nothing found");
                return JsonResponse::ok(["faq" => $response]);
            },function ($er){
                return JsonResponse::badRequest($er);
        });
    }
}
